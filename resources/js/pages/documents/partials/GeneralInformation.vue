<script setup lang="ts">
import { InertiaForm, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";

import Accordion from "@/components/Accordion.vue";
import DatePicker from "@/components/html/DatePicker.vue";
import Input from "@/components/html/Input.vue";
import PaymentCondition from "@/components/html/PaymentCondition.vue";
import Textarea from "@/components/html/Textarea.vue";
import OwnerSelect from "@/components/OwnerSelect.vue";
import Select from "@/components/Select.vue";
import { Multiselect } from "@/data-table/types";
import { DocumentableType } from "@/enums/DocumentableType";
import { DocumentStatus } from "@/enums/DocumentStatus";
import {
    Company,
    Document,
    DocumentForm,
    OwnerProps,
    SelectInput,
    User,
} from "@/types";
import { findKeyByValue } from "@/utils.js";

const props = defineProps<{
    form: InertiaForm<DocumentForm>;
    formDisabled?: boolean;
    ownerProps: OwnerProps;
    document?: Document;
    companies: Multiselect<Company>;
    customers: Multiselect<User>;
}>();

const emit = defineEmits(["form-updated"]);

const reset = ref<{
    customer: boolean;
}>({
    customer: false,
});

const disabledPaidAt = computed(
    () => props.document?.status != DocumentStatus.Sent_to_customer
);

const handleValueUpdated = async (input: SelectInput): Promise<void> => {
    switch (input.name) {
        case "documentable_type":
            await new Promise((resolve, reject) => {
                router.reload({
                    data: { documentable_type: input.value },
                    only: ["dataTable"],
                    onSuccess: resolve,
                    onError: reject,
                });
            });
            reset.value.customer = false;

            break;
        case "customer_company_id":
            await new Promise((resolve, reject) => {
                router.reload({
                    data: { customer_company_id: input.value },
                    only: ["customers"],
                    onSuccess: resolve,
                    onError: reject,
                });
            });

            reset.value.customer = true;

            break;

        default:
            reset.value.customer = false;
            break;
    }

    emit("form-updated", input);
};
</script>

<template>
    <div
        class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
    >
        <Accordion>
            <template #head>
                <div class="font-semibold text-xl sm:text-2xl mb-4">
                    {{ __("General Information") }}: {{ form.id }}
                </div>
            </template>

            <template #collapsedContent>
                <div
                    class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-y-3 xl:gap-y-0"
                >
                    <div class="border-r-2 border-[#E9E7E7] border-dashed">
                        <div class="font-medium text-[#676666]">
                            {{ __("Document system number") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ form.id }}
                        </div>
                    </div>

                    <div
                        class="ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Notes") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ form.notes }}
                        </div>
                    </div>

                    <div
                        v-if="props.document?.customer"
                        class="ml-12 md:ml-0 xl:ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Customer Company") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{
                                findKeyByValue(
                                    companies,
                                    props.document?.customer?.company_id
                                )
                            }}
                        </div>
                    </div>

                    <div
                        class="ml-12 md:ml-0 xl:ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Total Price Include Vat") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ form.total_price_include_vat }}
                        </div>
                    </div>
                </div>
            </template>

            <div class="grid lg:grid-cols-1 xl:grid-cols-2 gap-4">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:border-r border-[#E9E7E7] xl:pr-8 sm:gap-y-2 items-center"
                >
                    <label for="id">
                        {{ __("Invoice number") }}
                    </label>

                    <Input
                        :name="'number'"
                        type="text"
                        :model-value="props.document?.number"
                        :placeholder="__('Invoice number')"
                        disabled
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="date">
                        {{ __("Invoice Date") }}
                    </label>
                    <DatePicker
                        v-model="form.date"
                        :name="'date'"
                        :enable-time-picker="false"
                        :disabled="formDisabled"
                        :placeholder="__('Date')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="type">
                        {{ __("Type") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        :key="form.documentable_type"
                        v-model="form.documentable_type"
                        :name="'documentable_type'"
                        :options="DocumentableType"
                        :disabled="!!form.documentable_type"
                        :placeholder="__('Document entity type')"
                        class="w-full"
                        @select="handleValueUpdated"
                    />
                    <!-- Do not simplify !!form.documentable_type -->

                    <label for="customer_company_id">
                        {{ __("Customer Company") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        :key="form.customer_company_id"
                        v-model="form.customer_company_id"
                        :name="'customer_company_id'"
                        :options="companies"
                        :disabled="formDisabled"
                        :placeholder="__('Customer Company')"
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleValueUpdated"
                        @remove="reset.customer = true"
                    />

                    <label for="customer_id">
                        {{ __("Contact person customer") }}
                    </label>
                    <Select
                        :key="form.customer_id"
                        v-model="form.customer_id"
                        :name="'customer_id'"
                        :options="customers"
                        :reset="reset.customer"
                        :disabled="formDisabled"
                        :placeholder="__('Contact person customer')"
                        class="w-full mb-3.5 sm:mb-0"
                    />

                    <PaymentCondition
                        :form="form"
                        :required="true"
                        :form-disabled="formDisabled"
                    />
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:pl-4 sm:gap-y-2 items-center"
                >
                    <OwnerSelect
                        v-if="$can('create-ownership')"
                        v-model="form.owner_id"
                        :users="ownerProps.mainCompanyUsers"
                        :pending-ownerships="ownerProps.pendingOwnerships"
                        :disabled="formDisabled"
                    />

                    <label for="total_price_exclude_vat">
                        {{ __("Total Price Exclude Vat") }}
                    </label>
                    <Input
                        v-model="form.total_price_exclude_vat"
                        :name="'total_price_exclude_vat'"
                        type="text"
                        :disabled="true"
                        :placeholder="__('Total Price Exclude Vat')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="total_vat">
                        {{ __("Total Vat") }}
                    </label>
                    <Input
                        v-model="form.total_vat"
                        :name="'total_vat'"
                        type="text"
                        :disabled="true"
                        :placeholder="__('Total Vat')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="total_price_include_vat">
                        {{ __("Total Price Include Vat") }}
                    </label>
                    <Input
                        v-model="form.total_price_include_vat"
                        :name="'total_price_include_vat'"
                        type="text"
                        :disabled="true"
                        :placeholder="__('Total Price Include Vat')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="paid_at">
                        {{ __("Paid At") }}
                    </label>
                    <DatePicker
                        v-model="form.paid_at"
                        :name="'paid_at'"
                        :enable-time-picker="false"
                        :disabled="disabledPaidAt"
                        :placeholder="__('Paid At')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="notes">
                        {{ __("Notes") }}
                    </label>
                    <Textarea
                        v-model="form.notes"
                        :name="'notes'"
                        :disabled="formDisabled"
                        :placeholder="__('Notes')"
                        classes="mb-3.5 sm:mb-0"
                    />
                </div>
            </div>
        </Accordion>
    </div>
</template>
