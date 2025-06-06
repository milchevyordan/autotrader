<script setup lang="ts">
import { InertiaForm, router } from "@inertiajs/vue3";
import { ref } from "vue";

import Accordion from "@/components/Accordion.vue";
import Input from "@/components/html/Input.vue";
import PaymentCondition from "@/components/html/PaymentCondition.vue";
import RadioButtonToggle from "@/components/html/RadioButtonToggle.vue";
import WeekRangePicker from "@/components/html/WeekRangePicker.vue";
import OwnerSelect from "@/components/OwnerSelect.vue";
import PreviewPdfModal from "@/components/PreviewPdfModal.vue";
import Select from "@/components/Select.vue";
import { Multiselect } from "@/data-table/types";
import { Currency } from "@/enums/Currency";
import { Damage } from "@/enums/Damage";
import { ImportOrOriginType } from "@/enums/ImportOrOriginType";
import { SalesOrderStatus } from "@/enums/SalesOrderStatus";
import {
    Company,
    OwnerProps,
    SalesOrderForm,
    SelectInput,
    ServiceLevel,
    ServiceLevelDefaults,
    User,
} from "@/types";
import {
    findKeyByValue,
    formatPriceOnBlur,
    formatPriceOnFocus,
} from "@/utils.js";
import { vatPercentage } from "@/vat-percentage";

const props = defineProps<{
    form: InertiaForm<SalesOrderForm>;
    formDisabled?: boolean;
    serviceLevelDefaults?: ServiceLevelDefaults;
    ownerProps: OwnerProps;
    salesOrder?: SalesOrderForm;
    companies: Multiselect<Company>;
    customers?: Multiselect<User>;
    serviceLevels: Multiselect<ServiceLevel>;
    previewPdfDisabled?: boolean;
    resetServiceLevels?: boolean;
    previewPdfUrl?: string;
}>();

const reset = ref<{
    customer: boolean;
    serviceLevel: boolean;
}>({
    customer: false,
    serviceLevel: false,
});

const handleValueUpdated = async (input: SelectInput): Promise<void> => {
    switch (input.name) {
        case "customer_company_id":
            await new Promise((resolve, reject) => {
                router.reload({
                    data: { customer_company_id: input.value },
                    only: ["customers", "serviceLevels", "resetServiceLevels"],
                    onSuccess: resolve,
                    onError: reject,
                });
            });

            reset.value.customer = true;
            if (props.resetServiceLevels) {
                reset.value.serviceLevel = true;
            }
            break;

        case "service_level_id":
            await new Promise((resolve, reject) => {
                router.reload({
                    data: { service_level_id: input.value },
                    only: ["levelServices", "items", "serviceLevelDefaults"],
                    onSuccess: resolve,
                    onError: reject,
                });
            });

            props.form.payment_condition =
                props.serviceLevelDefaults?.payment_condition;
            props.form.payment_condition_free_text =
                props.serviceLevelDefaults?.payment_condition_free_text;
            props.form.discount = props.serviceLevelDefaults?.discount;
            props.form.discount_in_output =
                props.serviceLevelDefaults?.discount_in_output;
            props.form.damage = props.serviceLevelDefaults?.damage;
            props.form.transport_included =
                props.serviceLevelDefaults?.transport_included;
            props.form.type_of_sale = props.serviceLevelDefaults?.type_of_sale;
            reset.value.customer = false;
            reset.value.serviceLevel = false;

            break;

        default:
            reset.value.customer = false;
            reset.value.serviceLevel = false;
            break;
    }
};

const showPreviewModal = ref<boolean>(false);

const openPreviewModal = async () => {
    await new Promise((resolve, reject) => {
        router.reload({
            only: ["previewPdfUrl"],
            onSuccess: resolve,
            onError: reject,
        });
    });

    showPreviewModal.value = true;
};

const closePreviewModal = () => {
    showPreviewModal.value = false;
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
                            {{ salesOrder?.number }}
                        </div>
                    </div>

                    <div
                        class="md:ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Customer Company") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{
                                findKeyByValue(
                                    companies,
                                    salesOrder?.customer?.company_id
                                )
                            }}
                        </div>
                    </div>

                    <div
                        class="ml-12 md:ml-0 xl:ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Contact person customer") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ salesOrder?.customer?.full_name }}
                        </div>
                    </div>

                    <div
                        class="md:ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Sales Person") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ salesOrder?.seller?.full_name }}
                        </div>
                    </div>

                    <div
                        class="ml-12 border-r-2 xl:border-0 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Amount of vehicles") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ form.vehicleIds.length }}
                        </div>
                    </div>
                </div>
            </template>

            <div
                class="my-4 flex flex-col sm:flex-row items-start sm:items-center sm:justify-end gap-3 sm:gap-5"
            >
                <button
                    v-if="
                        salesOrder?.id &&
                        salesOrder?.status < SalesOrderStatus.Sent_to_buyer
                    "
                    class="rounded px-4 py-1.5 active:scale-95 bg-[#00A793] hover:bg-emerald-500 h-8 text-white text-xs"
                    @click="openPreviewModal"
                >
                    {{ __("Preview Pdf") }}
                </button>
            </div>

            <div class="grid lg:grid-cols-1 xl:grid-cols-2 gap-4">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:border-r border-[#E9E7E7] xl:pr-8 sm:gap-y-2 items-center"
                >
                    <label for="number">
                        {{ __("Sales order number") }}
                    </label>

                    <Input
                        :name="'number'"
                        type="text"
                        :model-value="salesOrder?.number"
                        :placeholder="__('Sales order number')"
                        disabled
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="reference">
                        {{ __("Custom SO reference") }}
                    </label>
                    <Input
                        v-model="form.reference"
                        :name="'reference'"
                        type="text"
                        :placeholder="__('Custom SO reference')"
                        class="mb-3.5 sm:mb-0"
                        :disabled="formDisabled"
                    />

                    <OwnerSelect
                        v-if="$can('create-ownership')"
                        v-model="form.owner_id"
                        :users="ownerProps.mainCompanyUsers"
                        :pending-ownerships="ownerProps.pendingOwnerships"
                        :disabled="formDisabled"
                    />

                    <label for="customer_company_id">
                        {{ __("Customer Company") }}
                    </label>
                    <Select
                        v-model="form.customer_company_id"
                        name="customer_company_id"
                        :options="companies"
                        :placeholder="__('Customer Company')"
                        class="w-full mb-3.5 sm:mb-0"
                        :disabled="formDisabled"
                        @select="handleValueUpdated"
                        @remove="reset.customer = true"
                    />

                    <label for="customer_id">
                        {{ __("Contact person customer") }}
                    </label>
                    <Select
                        v-model="form.customer_id"
                        :name="'customer_id'"
                        :options="customers"
                        :reset="reset.customer"
                        :placeholder="__('Contact person customer')"
                        class="w-full mb-3.5 sm:mb-0"
                        :disabled="formDisabled"
                        @select="handleValueUpdated"
                    />

                    <label for="seller_id">
                        {{ __("Sales Person") }}
                    </label>
                    <Select
                        v-model="form.seller_id"
                        :name="'seller_id'"
                        :options="ownerProps.mainCompanyUsers"
                        :disabled="formDisabled"
                        :placeholder="__('Sales Person')"
                        class="w-full mb-3.5 sm:mb-0"
                    />

                    <label for="currency">
                        {{ __("Currency") }}
                    </label>
                    <Select
                        :key="form.currency"
                        :selected-option-value="form.currency"
                        :name="'currency'"
                        :options="Currency"
                        :disabled="true"
                        :placeholder="__('Currency')"
                        class="w-full"
                    />

                    <label for="service_level_id">
                        {{ __("Service Level") }}
                    </label>
                    <Select
                        v-model="form.service_level_id"
                        :name="'service_level_id'"
                        :options="serviceLevels"
                        :placeholder="__('Service Level')"
                        class="w-full"
                        :reset="reset.serviceLevel"
                        :disabled="formDisabled"
                        @select="handleValueUpdated"
                    />
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:pl-4 sm:gap-y-2 items-center"
                >
                    <label for="type_of_sale">
                        {{ __("Type of sale") }}
                    </label>
                    <Select
                        id="type_of_sale"
                        v-model="form.type_of_sale"
                        :name="'type_of_sale'"
                        :options="ImportOrOriginType"
                        :placeholder="__('Type of sale')"
                        class="w-full"
                        :disabled="formDisabled"
                    />

                    <label for="damage">
                        {{ __("Damage repair level") }}
                    </label>
                    <Select
                        id="damage"
                        :key="form.damage"
                        v-model="form.damage"
                        :name="'damage'"
                        :options="Damage"
                        :disabled="formDisabled"
                        :placeholder="__('Damage repair level')"
                        class="w-full"
                    />

                    <label for="transport_included">
                        {{ __("Transport included") }}
                    </label>
                    <RadioButtonToggle
                        v-model="form.transport_included"
                        name="transport_included"
                        :left-button-label="__('Included')"
                        :right-button-label="__('Pick up by buyer')"
                        :disabled="formDisabled"
                    />

                    <PaymentCondition
                        :form="form"
                        :form-disabled="formDisabled"
                    />

                    <label for="discount_in_output">
                        {{ __("Discount in output") }}
                    </label>
                    <RadioButtonToggle
                        v-model="form.discount_in_output"
                        name="discount_in_output"
                        :disabled="formDisabled"
                    />

                    <label for="discount">
                        {{ __("Discount per vehicle") }}
                    </label>
                    <Input
                        v-model="form.discount"
                        :name="'discount'"
                        type="text"
                        :placeholder="__('Discount per vehicle')"
                        class="mb-3.5 sm:mb-0"
                        :disabled="formDisabled"
                        @focus="formatPriceOnFocus(form, 'discount')"
                        @blur="formatPriceOnBlur(form, 'discount')"
                    />

                    <label for="delivery_week">
                        {{ __("Delivery week") }}
                    </label>
                    <WeekRangePicker
                        v-model="form.delivery_week"
                        name="delivery_week"
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

                    <label for="vat_percentage">
                        {{ __("VAT percentage") }}
                    </label>
                    <Select
                        v-model="form.vat_percentage"
                        :name="'vat_percentage'"
                        :options="vatPercentage"
                        :disabled="!form.vat_deposit || formDisabled"
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
                </div>
            </div>
        </Accordion>
    </div>

    <PreviewPdfModal
        :show-preview-modal="showPreviewModal"
        :preview-pdf-url="previewPdfUrl"
        @close="closePreviewModal"
    />
</template>
