<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";

import Header from "@/components/Header.vue";
import AdditionalServices from "@/components/html/AdditionalServices.vue";
import ChangeLogs from "@/components/html/ChangeLogs.vue";
import RadioButtonToggle from "@/components/html/RadioButtonToggle.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import AdditionalInformation from "@/components/service-levels/AdditionalInformation.vue";
import GeneralInformation from "@/components/service-levels/GeneralInformation.vue";
import Table from "@/data-table/Table.vue";
import { DataTable, Multiselect } from "@/data-table/types";
import { ItemType } from "@/enums/ItemType";
import { ServiceLevelType } from "@/enums/ServiceLevelType";
import IconMinus from "@/icons/Minus.vue";
import IconPlus from "@/icons/Plus.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { serviceLevelFormRules } from "@/rules/service-level-form-rules";
import {
    ServiceLevel,
    ServiceLevelForm,
    Item,
    RadioToggleInput,
    Company,
} from "@/types";
import { findEnumKeyByValue } from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    serviceLevel: ServiceLevel;
    dataTable: DataTable<Item>;
    crmCompanies?: Multiselect<Company>;
    selectedItemIds: number[];
}>();

const form = useForm<ServiceLevelForm>({
    _method: "put",
    id: props.serviceLevel.id,
    name: props.serviceLevel.name,
    type: props.serviceLevel.type,
    type_of_sale: props.serviceLevel.type_of_sale,
    transport_included: props.serviceLevel.transport_included,
    damage: props.serviceLevel.damage,
    payment_condition: props.serviceLevel.payment_condition,
    payment_condition_free_text: props.serviceLevel.payment_condition_free_text,
    discount: props.serviceLevel.discount,
    discount_in_output: props.serviceLevel.discount_in_output,
    rdw_company_number: props.serviceLevel.rdw_company_number,
    login_autotelex: props.serviceLevel.login_autotelex,
    api_japie: props.serviceLevel.api_japie,
    bidder_name_autobid: props.serviceLevel.bidder_name_autobid,
    bidder_number_autobid: props.serviceLevel.bidder_number_autobid,
    api_vwe: props.serviceLevel.api_vwe,
    items: props.selectedItemIds,
    additional_services: props.serviceLevel.additional_services,
    companies: props.serviceLevel.companies.map((company) => company.id),
    items_in_output: props.serviceLevel.items
        .filter((item: Item) => item?.pivot?.in_output)
        .map((item) => item.id),
});

const handleItemInOutputChange = (input: RadioToggleInput, id: number) => {
    const valueIndex = form.items_in_output.indexOf(id);
    if (input.value && valueIndex === -1) {
        form.items_in_output.push(id);
        return;
    }
    if (!input.value && valueIndex !== -1) {
        form.items_in_output.splice(valueIndex, 1);
    }
};

const addDeliveryServiceItem = (item: Item) => {
    form.items.push(item.id);
};

const removeDeliveryServiceItem = (item: Item) => {
    const itemIndex = form.items.indexOf(item.id);

    form.items.splice(itemIndex, 1);
};

const itemInOutput = (id: number): boolean => {
    return form.items_in_output.includes(id);
};

const save = async () => {
    validate(form, serviceLevelFormRules);

    return new Promise<void>((resolve, reject) => {
        form.post(route("service-levels.update", props.serviceLevel.id), {
            preserveScroll: true,
            onSuccess: () => {
                resolve();
            },
            onError: () => {
                reject(new Error("Error, during update"));
            },
        });
    });
};
</script>

<template>
    <Head :title="__('Service Level')" />

    <AppLayout>
        <Header :text="__('Service Level')" />

        <div class="flex justify-center">
            <div class="w-full max-w-[1721px]">
                <GeneralInformation
                    :service-level="serviceLevel"
                    :form="form"
                    :crm-companies="crmCompanies"
                    :form-disabled="true"
                />

                <AdditionalInformation
                    v-if="form.type == ServiceLevelType.Client"
                    :form="form"
                />

                <div
                    class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
                >
                    <div class="font-semibold text-xl sm:text-2xl mb-4">
                        {{ __("Items") }}
                    </div>

                    <Table
                        :data-table="dataTable"
                        :per-page-options="[5, 10, 15, 20, 50]"
                        :global-search="true"
                        :advanced-filters="true"
                        :selected-row-indexes="form.items"
                        :selected-row-column="'id'"
                    >
                        <template #cell(type)="{ value, item }">
                            <div class="flex gap-1.5">
                                {{ findEnumKeyByValue(ItemType, value) }}
                            </div>
                        </template>

                        <template #cell(action)="{ value, item }">
                            <div class="flex gap-1.5">
                                <div
                                    v-if="form.items.includes(item.id)"
                                    class="flex gap-1.5"
                                >
                                    <button
                                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                        @click="removeDeliveryServiceItem(item)"
                                    >
                                        <IconMinus
                                            classes="w-4 h-4 text-slate-600"
                                        />
                                    </button>

                                    <div
                                        class="document-container bg-white rounded"
                                    >
                                        <RadioButtonToggle
                                            :key="item.id"
                                            :name="`in_output[${item.id}]`"
                                            :model-value="itemInOutput(item.id)"
                                            :left-button-label="__('In Output')"
                                            :right-button-label="
                                                __('Not In Output')
                                            "
                                            @change="
                                                handleItemInOutputChange(
                                                    $event,
                                                    item.id
                                                )
                                            "
                                        />
                                    </div>
                                </div>
                                <div v-else>
                                    <button
                                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                        @click="addDeliveryServiceItem(item)"
                                    >
                                        <IconPlus
                                            classes="w-4 h-4 text-slate-600"
                                        />
                                    </button>
                                </div>
                            </div>
                        </template>
                    </Table>
                </div>

                <AdditionalServices :form="form" :hide-total="true" />

                <ChangeLogs :change-logs="serviceLevel.change_logs" />
            </div>
        </div>

        <ResetSaveButtons
            :processing="form.processing"
            @reset="form.reset()"
            @save="save"
        />
    </AppLayout>
</template>
