<script setup lang="ts">
import { InertiaForm, router } from "@inertiajs/vue3";
import { ref } from "vue";

import Accordion from "@/components/Accordion.vue";
import Input from "@/components/html/Input.vue";
import RadioButtonToggle from "@/components/html/RadioButtonToggle.vue";
import Textarea from "@/components/html/Textarea.vue";
import OwnerSelect from "@/components/OwnerSelect.vue";
import Select from "@/components/Select.vue";
import { Multiselect } from "@/data-table/types";
import { Currency } from "@/enums/Currency";
import { ImportEuOrWorldType } from "@/enums/ImportEuOrWorldType";
import { SupplierOrIntermediary } from "@/enums/SupplierOrIntermediary";
import { Company, OwnerProps, PreOrderForm, SelectInput, User } from "@/types";
import {
    dateToLocaleString,
    findKeyByValue,
    formatPriceOnBlur,
    formatPriceOnFocus,
} from "@/utils.js";
import { vatPercentage } from "@/vat-percentage";

const props = defineProps<{
    form: InertiaForm<PreOrderForm>;
    defaultCurrencies: Record<number, Currency>;
    preOrder?: PreOrderForm;
    companies: Multiselect<Company>;
    suppliers?: Multiselect<User>;
    purchasers: Multiselect<User>;
    ownerProps: OwnerProps;
    intermediaries?: Multiselect<User>;
    disabled?: boolean;
}>();

const reset = ref<{
    supplier: boolean;
    intermediary: boolean;
}>({
    supplier: false,
    intermediary: false,
});

const handleSelectUpdated = async (input: SelectInput): Promise<void> => {
    switch (input.name) {
        case "supplier_company_id":
            await new Promise((resolve, reject) => {
                router.reload({
                    data: { supplier_company_id: input.value },
                    only: ["suppliers"],
                    onSuccess: resolve,
                    onError: reject,
                });
            });

            if (
                props.form.document_from_type == SupplierOrIntermediary.Supplier
            ) {
                props.form.currency_po =
                    props.defaultCurrencies[input.value as number];
            }
            reset.value.supplier = true;

            break;

        case "intermediary_company_id":
            await new Promise((resolve, reject) => {
                router.reload({
                    data: { intermediary_company_id: input.value },
                    only: ["intermediaries"],
                    onSuccess: resolve,
                    onError: reject,
                });
            });

            if (
                props.form.document_from_type ==
                SupplierOrIntermediary.Intermediary
            ) {
                props.form.currency_po =
                    props.defaultCurrencies[input.value as number];
            }
            reset.value.intermediary = true;
            break;

        case "document_from_type":
            switch (input.value) {
                case SupplierOrIntermediary.Supplier:
                    props.form.currency_po =
                        props.defaultCurrencies[
                            props.form.supplier_company_id ??
                                props.preOrder?.supplier.company_id
                        ];
                    break;

                case SupplierOrIntermediary.Intermediary:
                    props.form.currency_po =
                        props.defaultCurrencies[
                            props.form.intermediary_company_id ??
                                props.preOrder?.intermediary?.company_id
                        ];
                    break;
            }

            reset.value.supplier = false;
            reset.value.intermediary = false;

            break;

        default:
            reset.value.supplier = false;
            reset.value.intermediary = false;

            break;
    }
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
                            {{ __("Sales order number") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ form.number }}
                        </div>
                    </div>

                    <div
                        class="ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Custom reference") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ form.contact_notes }}
                        </div>
                    </div>

                    <div
                        class="ml-12 md:ml-0 xl:ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Supplier") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{
                                findKeyByValue(
                                    companies,
                                    preOrder?.supplier?.company_id
                                )
                            }}
                        </div>
                    </div>

                    <div
                        class="md:ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Purchasing entity") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{
                                findKeyByValue(
                                    companies,
                                    preOrder?.purchaser?.company_id
                                )
                            }}
                        </div>
                    </div>

                    <div
                        class="md:ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Created") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ dateToLocaleString(form.created_at) }}
                        </div>
                    </div>
                </div>
            </template>

            <div class="grid lg:grid-cols-1 xl:grid-cols-2 gap-4">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:border-r border-[#E9E7E7] xl:pr-8 sm:gap-y-2 items-center"
                >
                    <label for="supplier_company_id">
                        {{ __("Supplier") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        v-model="form.supplier_company_id"
                        :name="'supplier_company_id'"
                        :options="companies"
                        :disabled="disabled"
                        :placeholder="__('Suppliers company')"
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleSelectUpdated"
                        @remove="reset.supplier = true"
                    />

                    <label for="supplier_id">
                        {{ __("Contact person supplier") }}
                    </label>
                    <Select
                        v-model="form.supplier_id"
                        :name="'supplier_id'"
                        :options="suppliers"
                        :disabled="disabled"
                        :reset="reset.supplier"
                        :placeholder="__('Contact person supplier')"
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleSelectUpdated"
                    />

                    <label for="intermediary_company_id">
                        {{ __("Intermediary") }}
                    </label>
                    <Select
                        v-model="form.intermediary_company_id"
                        :name="'intermediary_company_id'"
                        :options="companies"
                        :disabled="disabled"
                        :placeholder="__('Intermediary company')"
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleSelectUpdated"
                        @remove="reset.intermediary = true"
                    />

                    <label for="intermediary_id">
                        {{ __("Intermediary contact person") }}
                    </label>
                    <Select
                        v-model="form.intermediary_id"
                        :name="'intermediary_id'"
                        :options="intermediaries"
                        :disabled="disabled"
                        :reset="reset.intermediary"
                        :placeholder="__('Intermediary contact')"
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleSelectUpdated"
                    />

                    <label
                        for="purchaser_id"
                        :title="
                            __(
                                'Users with role Company Purchaser in this company'
                            )
                        "
                    >
                        {{ __("Company Purchaser") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        v-model="form.purchaser_id"
                        :name="'purchaser_id'"
                        :options="purchasers"
                        :disabled="disabled"
                        :placeholder="__('Purchase Customer')"
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleSelectUpdated"
                    />

                    <label for="document_from_type">
                        {{ __("Invoice from") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        v-model="form.document_from_type"
                        :name="'document_from_type'"
                        :options="SupplierOrIntermediary"
                        :disabled="disabled"
                        :placeholder="__('Invoice from')"
                        class="w-full"
                    />

                    <label for="type">
                        {{ __("Type of purchase") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        v-model="form.type"
                        :name="'type'"
                        :options="ImportEuOrWorldType"
                        :disabled="disabled"
                        :placeholder="__('Type of purchase')"
                        class="w-full"
                    />
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:pl-4 sm:gap-y-2 items-center"
                >
                    <OwnerSelect
                        v-if="$can('create-ownership')"
                        v-model="form.owner_id"
                        :disabled="disabled"
                        :users="ownerProps.mainCompanyUsers"
                        :pending-ownerships="ownerProps.pendingOwnerships"
                    />

                    <label for="currency_po">
                        {{ __("Currency of the PO") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        :key="form.currency_po"
                        v-model="form.currency_po"
                        :name="'currency_po'"
                        :options="Currency"
                        :disabled="disabled"
                        :placeholder="__('Currency of the PO')"
                        class="w-full"
                    />

                    <label for="transport_included">
                        {{ __("Transport") }}
                    </label>
                    <RadioButtonToggle
                        v-model="form.transport_included"
                        :disabled="disabled"
                        name="transport_included"
                        :left-button-label="__('Included')"
                        :right-button-label="__('By Supplier')"
                    />

                    <label for="vat_deposit">
                        {{ __("VAT deposit / Kaution") }}
                    </label>
                    <RadioButtonToggle
                        v-model="form.vat_deposit"
                        :disabled="disabled"
                        name="vat_deposit"
                    />

                    <label for="vat_percentage">
                        {{ __("VAT percentage") }}
                    </label>
                    <Select
                        v-model="form.vat_percentage"
                        :name="'vat_percentage'"
                        :options="vatPercentage"
                        :disabled="!form.vat_deposit || disabled"
                        :placeholder="__('VAT percentage')"
                        class="w-full"
                    />

                    <label for="amount_of_vehicles">
                        {{ __("Amount of vehicles") }}
                    </label>
                    <Input
                        v-model="form.amount_of_vehicles"
                        :name="'amount_of_vehicles'"
                        type="number"
                        min="1"
                        :disabled="disabled"
                        :placeholder="__('Amount of vehicles')"
                        class="mb-3.5 sm:mb-0"
                        @input="$emit('calculate-prices')"
                    />

                    <label for="down_payment">
                        {{ __("Down payment") }}
                    </label>
                    <RadioButtonToggle
                        v-model="form.down_payment"
                        :disabled="disabled"
                        name="down_payment"
                    />

                    <label for="down_payment_amount">
                        {{ __("Down payment amount") }}
                    </label>
                    <Input
                        v-model="form.down_payment_amount"
                        :name="'down_payment_amount'"
                        type="text"
                        :disabled="!form.down_payment || disabled"
                        :placeholder="__('Down payment amount')"
                        class="mb-3.5 sm:mb-0"
                        @focus="formatPriceOnFocus(form, 'down_payment_amount')"
                        @blur="formatPriceOnBlur(form, 'down_payment_amount')"
                    />

                    <label for="contact_notes">
                        {{ __("Contact notes") }}
                    </label>
                    <Textarea
                        v-model="form.contact_notes"
                        :disabled="disabled"
                        :name="'contact_notes'"
                        :placeholder="__('Contact notes')"
                        classes="mb-3.5 sm:mb-0"
                    />
                </div>
            </div>
        </Accordion>
    </div>
</template>
