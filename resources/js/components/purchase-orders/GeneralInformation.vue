<script setup lang="ts">
import { InertiaForm, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";

import Accordion from "@/components/Accordion.vue";
import Input from "@/components/html/Input.vue";
import PaymentCondition from "@/components/html/PaymentCondition.vue";
import RadioButtonToggle from "@/components/html/RadioButtonToggle.vue";
import Textarea from "@/components/html/Textarea.vue";
import OwnerSelect from "@/components/OwnerSelect.vue";
import Select from "@/components/Select.vue";
import { Multiselect } from "@/data-table/types";
import { Country } from "@/enums/Country";
import { Currency } from "@/enums/Currency";
import { NationalEuOrWorldType } from "@/enums/NationalEuOrWorldType";
import { Papers } from "@/enums/Papers";
import {PurchaseOrderStatus} from "@/enums/PurchaseOrderStatus";
import { SupplierOrIntermediary } from "@/enums/SupplierOrIntermediary";
import {
    Company,
    CompanyDefaults,
    OwnerProps,
    PurchaseOrderForm,
    SelectInput,
    User,
} from "@/types";
import {
    dateToLocaleString,
    findKeyByValue,
    formatPriceOnBlur,
    formatPriceOnFocus,
} from "@/utils.js";
import { vatPercentage } from "@/vat-percentage";

const props = defineProps<{
    form: InertiaForm<PurchaseOrderForm>;
    formDisabled?: boolean;
    ownerProps: OwnerProps;
    suppliers: Multiselect<User>;
    companies: Multiselect<Company>;
    intermediaries?: Multiselect<User>;
    purchasers: Multiselect<User>;
    companyDefaults?: CompanyDefaults;
    purchaseOrder?: PurchaseOrderForm;
}>();

const emit = defineEmits(["form-updated"]);

const reset = ref<{
    supplier: boolean;
    intermediary: boolean;
}>({
    supplier: false,
    intermediary: false,
});

const handleValueUpdated = async (input: SelectInput): Promise<void> => {
    switch (input.name) {
        case "type":
            if (input.value == NationalEuOrWorldType.EU) {
                props.form.total_bpm = "";
            }

            break;

        case "supplier_company_id":
            props.form.vat_percentage = getVATPercentageForCountry(input.value);
            reset.value.supplier = true;

            break;

        case "intermediary_company_id":
            await new Promise((resolve, reject) => {
                router.reload({
                    data: {
                        intermediary_company_id: input.value,
                        company_id: input.value,
                    },
                    only: ["intermediaries", "companyDefaults"],
                    onSuccess: resolve,
                    onError: reject,
                });
            });

            if (
                props.intermediaries &&
                Object.keys(props.intermediaries).length == 1
            ) {
                props.form.intermediary_id = Object.values(
                    props.intermediaries
                )[0];
            }

            if (
                props.form.document_from_type ==
                SupplierOrIntermediary.Intermediary
            ) {
                props.form.currency_po =
                    props.companyDefaults?.default_currency;
                props.form.type = props.companyDefaults?.purchase_type;
            }
            reset.value.intermediary = true;
            break;

        case "document_from_type":
            switch (input.value) {
                case SupplierOrIntermediary.Supplier:
                    await new Promise((resolve, reject) => {
                        router.reload({
                            data: {
                                company_id:
                                    props.form.supplier_company_id ??
                                    props.purchaseOrder?.supplier.company_id,
                            },
                            only: ["companyDefaults"],
                            onSuccess: resolve,
                            onError: reject,
                        });
                    });
                    break;

                case SupplierOrIntermediary.Intermediary:
                    await new Promise((resolve, reject) => {
                        router.reload({
                            data: {
                                company_id:
                                    props.form.intermediary_company_id ??
                                    props.purchaseOrder?.intermediary
                                        ?.company_id,
                            },
                            only: ["companyDefaults"],
                            onSuccess: resolve,
                            onError: reject,
                        });
                    });
                    break;
            }

            props.form.currency_po = props.companyDefaults?.default_currency;
            props.form.type = props.companyDefaults?.purchase_type;
            reset.value.supplier = false;
            reset.value.intermediary = false;

            break;

        default:
            reset.value.supplier = false;
            reset.value.intermediary = false;
            break;
    }

    emit("form-updated", input);
};

function getVATPercentageForCountry(countryCode: any): number {
    switch (countryCode) {
        case Country.Belgium:
        case Country.Czech_Republic:
        case Country.Netherlands:
            return 21;
        case Country.Croatia:
        case Country.Norway:
        case Country.Sweden:
            return 25;
        case Country.Finland:
        case Country.Greece:
            return 24;
        case Country.Germany:
        case Country.Romania:
            return 19;
        case Country.Hungary:
            return 27;
        case Country.Ireland:
        case Country.Poland:
            return 23;
        case Country.Italy:
        case Country.Slovenia:
            return 22;
        case Country.Spain:
            return 21;
        default:
            return 20;
    }
}

const numberOfVehicles = computed(() => props.form.vehicleIds.length);
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
                            {{ __("Purchase order number") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ form.id }}
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
                                    props.companies,
                                    props.purchaseOrder?.supplier?.company_id
                                )
                            }}
                        </div>
                    </div>

                    <div
                        class="md:ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Contact person supplier") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ props.purchaseOrder?.supplier?.full_name }}
                        </div>
                    </div>

                    <div
                        class="md:ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Created") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{
                                dateToLocaleString(
                                    props.purchaseOrder?.created_at
                                )
                            }}
                        </div>
                    </div>

                    <div
                        class="ml-12 border-r-2 xl:border-0 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Amount of vehicles") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ numberOfVehicles }}
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
                        :key="form.supplier_company_id"
                        v-model="form.supplier_company_id"
                        :name="'supplier_company_id'"
                        :options="companies"
                        :disabled="formDisabled || !!numberOfVehicles"
                        :placeholder="__('Suppliers company')"
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleValueUpdated"
                        @remove="reset.supplier = true"
                    />

                    <label for="supplier_id">
                        {{ __("Contact person supplier") }}
                    </label>
                    <Select
                        :key="form.supplier_id"
                        v-model="form.supplier_id"
                        :name="'supplier_id'"
                        :options="suppliers"
                        :reset="reset.supplier"
                        :disabled="formDisabled"
                        :placeholder="__('Contact person supplier')"
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleValueUpdated"
                    />

                    <label for="intermediary_company_id">
                        {{ __("Intermediary") }}
                    </label>
                    <Select
                        v-model="form.intermediary_company_id"
                        :name="'intermediary_company_id'"
                        :options="companies"
                        :disabled="formDisabled"
                        :placeholder="__('Intermediary company')"
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleValueUpdated"
                        @remove="reset.intermediary = true"
                    />

                    <label for="intermediary_id">
                        {{ __("Intermediary contact person") }}
                    </label>
                    <Select
                        :key="form.intermediary_id"
                        v-model="form.intermediary_id"
                        :name="'intermediary_id'"
                        :options="intermediaries"
                        :disabled="formDisabled"
                        :reset="reset.intermediary"
                        :placeholder="__('Intermediary contact')"
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleValueUpdated"
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
                        :disabled="formDisabled"
                        :placeholder="__('Company Purchaser')"
                        class="w-full mb-3.5 sm:mb-0"
                    />

                    <label for="document_from_type">
                        {{ __("Invoice from") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        v-model="form.document_from_type"
                        :name="'document_from_type'"
                        :options="SupplierOrIntermediary"
                        :disabled="formDisabled"
                        :placeholder="__('Invoice from')"
                        class="w-full"
                        @select="handleValueUpdated"
                    />

                    <label for="type">
                        {{ __("Type of purchase") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        :key="form.type"
                        v-model="form.type"
                        :name="'type'"
                        :options="NationalEuOrWorldType"
                        :placeholder="__('Type of purchase')"
                        class="w-full"
                        :disabled="formDisabled"
                        @select="handleValueUpdated"
                    />

                    <label for="papers">
                        {{ __("Papers") }}
                    </label>
                    <Select
                        :key="form.papers"
                        v-model="form.papers"
                        :name="'papers'"
                        :options="Papers"
                        :placeholder="__('Papers')"
                        class="w-full"
                        :disabled="formDisabled"
                    />

                    <PaymentCondition
                        :form="form"
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

                    <label for="currency_po">
                        {{ __("Currency of the PO") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        :key="form.currency_po"
                        v-model="form.currency_po"
                        :name="'currency_po'"
                        :options="Currency"
                        :disabled="formDisabled"
                        :placeholder="__('Currency of the PO')"
                        class="w-full"
                    />

                    <label for="transport_included">
                        {{ __("Transport") }}
                    </label>
                    <RadioButtonToggle
                        v-model="form.transport_included"
                        name="transport_included"
                        :left-button-label="__('By Us')"
                        :right-button-label="__('By Supplier')"
                        :disabled="formDisabled"
                    />

                    <label for="vat_deposit">
                        {{ __("VAT deposit / Kaution") }}
                    </label>
                    <RadioButtonToggle
                        v-model="form.vat_deposit"
                        name="vat_deposit"
                        :disabled="formDisabled"
                    />

                    <label for="vat_deposit_amount">
                        {{ __("VAT deposit amount") }}
                    </label>
                    <Input
                        v-model="form.vat_deposit_amount"
                        :name="'vat_deposit_amount'"
                        type="text"
                        :disabled="formDisabled || !form.vat_deposit"
                        :placeholder="__('VAT deposit amount')"
                        class="mb-3.5 sm:mb-0"
                        @focus="formatPriceOnFocus(form, 'vat_deposit_amount')"
                        @blur="formatPriceOnBlur(form, 'vat_deposit_amount')"
                    />

                    <label for="vat_percentage">
                        {{ __("VAT percentage") }}
                    </label>
                    <Select
                        :key="form.vat_percentage"
                        v-model="form.vat_percentage"
                        :name="'vat_percentage'"
                        :options="vatPercentage"
                        :disabled="formDisabled || !form.vat_deposit"
                        :placeholder="__('VAT percentage')"
                        class="w-full"
                    />

                    <label for="down_payment">
                        {{ __("Down payment") }}
                    </label>
                    <RadioButtonToggle
                        v-model="form.down_payment"
                        name="down_payment"
                        :disabled="formDisabled"
                    />

                    <label for="down_payment_amount">
                        {{ __("Down payment amount") }}
                    </label>
                    <Input
                        v-model="form.down_payment_amount"
                        :name="'down_payment_amount'"
                        type="text"
                        :disabled="formDisabled || !form.down_payment"
                        :placeholder="__('Down payment amount')"
                        class="mb-3.5 sm:mb-0"
                        @focus="formatPriceOnFocus(form, 'down_payment_amount')"
                        @blur="formatPriceOnBlur(form, 'down_payment_amount')"
                    />

                    <label for="total_payment_amount">
                        {{ __("Total payment amount") }}
                    </label>
                    <Input
                        v-model="form.total_payment_amount"
                        :name="'total_payment_amount'"
                        type="text"
                        :placeholder="__('Total payment amount')"
                        class="mb-3.5 sm:mb-0"
                        :disabled="purchaseOrder?.status == PurchaseOrderStatus.Completed && !$can('super-edit')"
                        @focus="
                            formatPriceOnFocus(form, 'total_payment_amount')
                        "
                        @blur="formatPriceOnBlur(form, 'total_payment_amount')"
                    />

                    <label for="sales_restriction">
                        {{ __("Sales restriction") }}
                    </label>
                    <Input
                        v-model="form.sales_restriction"
                        :name="'sales_restriction'"
                        type="text"
                        :disabled="formDisabled"
                        :placeholder="__('Sales restriction')"
                        classes="mb-3.5 sm:mb-0"
                    />

                    <label for="contact_notes">
                        {{ __("Contact notes") }}
                    </label>
                    <Textarea
                        v-model="form.contact_notes"
                        :name="'contact_notes'"
                        :disabled="formDisabled"
                        :placeholder="__('Contact notes')"
                        classes="mb-3.5 sm:mb-0"
                    />
                </div>
            </div>
        </Accordion>
    </div>
</template>
