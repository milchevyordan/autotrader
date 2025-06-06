<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";

import Header from "@/components/Header.vue";
import AdditionalServices from "@/components/html/AdditionalServices.vue";
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
import { Item, ServiceLevelForm, RadioToggleInput, Company } from "@/types";
import { findEnumKeyByValue } from "@/utils";
import { validate } from "@/validations";

defineProps<{
    dataTable: DataTable<Item>;
    crmCompanies?: Multiselect<Company>;
}>();

const form = useForm<ServiceLevelForm>({
    id: null!,
    type: ServiceLevelType.System,
    name: null!,
    type_of_sale: null!,
    transport_included: false,
    damage: null!,
    payment_condition: null!,
    payment_condition_free_text: null!,
    discount: null!,
    discount_in_output: false,
    rdw_company_number: null!,
    login_autotelex: null!,
    api_japie: null!,
    bidder_name_autobid: null!,
    bidder_number_autobid: null!,
    api_vwe: null!,
    items: [],
    items_in_output: [],
    additional_services: [],
    companies: [],
});

const addDeliveryServiceItem = (item: Item) => {
    form.items.push(item.id);
};

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

const removeDeliveryServiceItem = (item: Item) => {
    const itemIndex = form.items.indexOf(item.id);

    form.items.splice(itemIndex, 1);
};

const save = () => {
    validate(form, serviceLevelFormRules);

    form.post(route("service-levels.store"), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {},
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
                    :form="form"
                    :crm-companies="crmCompanies"
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
                                            :name="`in_output[${item.id}]`"
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
            </div>
        </div>

        <ResetSaveButtons
            :processing="form.processing"
            @reset="form.reset()"
            @save="save"
        />
    </AppLayout>
</template>
