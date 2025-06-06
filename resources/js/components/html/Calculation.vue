<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { computed, watch } from "vue";

import Input from "@/components/html/Input.vue";
import InputIcon from "@/components/html/InputIcon.vue";
import RadioButtonToggle from "@/components/html/RadioButtonToggle.vue";
import Select from "@/components/Select.vue";
import { Currency } from "@/enums/Currency";
import { companyCurrency } from "@/globals";
import { Bpm, PreOrderVehicleForm, VehicleForm } from "@/types";
import {
    strToNum,
    numberFormat,
    sumStrings,
    diffStrings,
    formatPriceOnFocus,
    formatPriceOnBlur,
    replaceEnumUnderscores,
    iconComponentMap,
    checkIsDateAfter,
    convertUnitsToCurrency,
    convertCurrencyToUnits,
} from "@/utils.js";
import { vatPercentage } from "@/vat-percentage";

const props = defineProps<{
    form: VehicleForm | PreOrderVehicleForm;
    bpmValues?: Bpm;
}>();

watch(
    () => props.form.vat_percentage,
    () => {
        calculate();
    }
);

const getBpmValues = async () => {
    try {
        await new Promise((resolve, reject) => {
            router.reload({
                data: {
                    year: props.form.first_registration_date,
                    fuel: props.form.fuel,
                    co2: props.form.co2_wltp,
                },
                only: ["bpmValues", "engine"],
                onSuccess: resolve,
                onError: reject,
            });
        });
    } catch (error) {
        return false;
    }
};

const calculateBpmPercentage = (): number => {
    const firstRegistrationDate = new Date(props.form.first_registration_date);
    const dateNow = new Date();
    const diffMonths =
        (dateNow.getFullYear() - firstRegistrationDate.getFullYear()) * 12 +
        (dateNow.getMonth() - firstRegistrationDate.getMonth());

    let percentage;

    switch (true) {
        case diffMonths == 0:
            percentage = 0;
            break;
        case diffMonths == 1:
            percentage = 12;
            break;
        case diffMonths > 1 && diffMonths <= 3:
            percentage = 12 + (diffMonths - 1) * 4;
            break;
        case diffMonths > 3 && diffMonths <= 5:
            percentage = 20 + (diffMonths - 3) * 3.5;
            break;
        case diffMonths > 5 && diffMonths <= 9:
            percentage = 27 + (diffMonths - 5) * 1.5;
            break;
        case diffMonths > 9 && diffMonths <= 18:
            percentage = 33 + (diffMonths - 9);
            break;
        case diffMonths > 18 && diffMonths <= 30:
            percentage = 42 + (diffMonths - 18) * 0.75;
            break;
        case diffMonths > 30 && diffMonths <= 42:
            percentage = 51 + (diffMonths - 30) * 0.5;
            break;
        case diffMonths > 42 && diffMonths <= 54:
            percentage = 57 + (diffMonths - 42) * 0.42;
            break;
        case diffMonths > 54 && diffMonths <= 66:
            percentage = 62 + (diffMonths - 54) * 0.42;
            break;
        case diffMonths > 66 && diffMonths <= 78:
            percentage = 67 + (diffMonths - 66) * 0.42;
            break;
        case diffMonths > 78 && diffMonths <= 90:
            percentage = 72 + (diffMonths - 78) * 0.25;
            break;
        case diffMonths > 90 && diffMonths <= 102:
            percentage = 75 + (diffMonths - 90) * 0.25;
            break;
        case diffMonths > 102 && diffMonths <= 114:
            percentage = 78 + (diffMonths - 102) * 0.25;
            break;
        default:
            percentage = 81 + (diffMonths - 114) * 0.19;
    }

    // Check if percentage is more than 100, if so set it to 100
    if (percentage > 100) {
        percentage = 100;
    }

    return percentage;
};

const isFirstRegistrationValid = computed(() => {
    return checkIsDateAfter(props.form.first_registration_date, 2018);
});

const handleBpmCalculation = async () => {
    if (
        !(
            props.form.fuel &&
            isFirstRegistrationValid.value &&
            props.form.co2_wltp
        )
    ) {
        return;
    }

    await getBpmValues();

    const bpmPercent = calculateBpmPercentage();
    const additionalDieselTax = props.bpmValues?.additional_diesel_tax ?? 1;
    const vehicleCo2Tax = props.bpmValues?.min_co2_to_add_diesel_tax;
    const minCo2ForTax = props.bpmValues?.co2_min ?? 0;
    const muyltiplyAmount = props.bpmValues?.multiply_amount ?? 1;
    const sumAmount = props.bpmValues?.sum_amount ?? 0;
    let additionalTax = 0;

    props.form.bpm_percent = numberFormat(bpmPercent);
    if (vehicleCo2Tax) {
        const co2Diff = props.form.co2_wltp - vehicleCo2Tax;
        if (co2Diff > 0) {
            additionalTax += co2Diff * additionalDieselTax;
        }
    }

    props.form.gross_bpm = numberFormat(
        (props.form.co2_wltp - minCo2ForTax) * muyltiplyAmount + sumAmount
    );

    props.form.bpm = numberFormat(
        Number(strToNum(props.form.gross_bpm)) -
            Number(strToNum(props.form.gross_bpm)) * (bpmPercent / 100)
    );
};

watch(
    () => [
        props.form.fuel,
        props.form.co2_wltp,
        props.form.first_registration_date,
    ],
    () => {
        handleBpmCalculation();
    }
);

const toggleIntermediate = () => {
    if (props.form.intermediate) {
        return;
    }

    props.form.fee_intermediate = null!;
    calculate();
};

const calculate = () => {
    props.form.total_purchase_price = sumStrings(
        props.form.net_purchase_price,
        props.form.fee_intermediate
    );

    props.form.is_locked ? calculateLockedPrices() : calculateUnlockedPrice();

    props.form.sale_price_net_including_services_and_products = sumStrings(
        props.form.sales_price_net,
        props.form.sale_price_services_and_products,
        props.form.discount
    );
};

const calculateLockedPrices = () => {
    props.form.is_vat
        ? calculateVatLockedPrice()
        : calculateMarginLockedPrice();
};

const calculateVatLockedPrice = () => {
    props.form.sales_price_incl_vat_or_margin = diffStrings(
        props.form.sales_price_total,
        props.form.leges_vat,
        props.form.rest_bpm_indication
    );

    props.form.vat = numberFormat(
        ((Number(strToNum(props.form.sales_price_incl_vat_or_margin)) /
            (1 + props.form.vat_percentage / 100)) *
            props.form.vat_percentage) /
            100
    );

    props.form.sales_price_net = diffStrings(
        props.form.sales_price_incl_vat_or_margin,
        props.form.vat
    );

    props.form.total_costs_with_fee = diffStrings(
        props.form.sales_price_net,
        props.form.total_purchase_price
    );

    props.form.sales_margin = diffStrings(
        props.form.total_costs_with_fee,
        props.form.costs_of_damages,
        props.form.transport_inbound,
        props.form.transport_outbound,
        props.form.costs_of_taxation,
        props.form.recycling_fee
    );
};

const calculateMarginLockedPrice = () => {
    props.form.sales_price_incl_vat_or_margin = diffStrings(
        props.form.sales_price_total,
        props.form.rest_bpm_indication,
        props.form.leges_vat
    );

    const costsWithFee =
        Number(
            strToNum(
                diffStrings(
                    props.form.sales_price_incl_vat_or_margin,
                    props.form.net_purchase_price
                )
            )
        ) /
            (1 + props.form.vat_percentage / 100) -
        Number(strToNum(props.form.fee_intermediate));
    props.form.total_costs_with_fee = numberFormat(costsWithFee);

    props.form.vat = numberFormat(
        (Number(strToNum(props.form.fee_intermediate)) +
            Number(strToNum(props.form.total_costs_with_fee))) *
            (props.form.vat_percentage / 100)
    );

    props.form.sales_price_net = diffStrings(
        props.form.sales_price_incl_vat_or_margin,
        props.form.vat
    );

    props.form.sales_margin = diffStrings(
        props.form.total_costs_with_fee,
        props.form.costs_of_damages,
        props.form.transport_inbound,
        props.form.transport_outbound,
        props.form.costs_of_taxation,
        props.form.recycling_fee
    );
};

const calculateUnlockedPrice = () => {
    props.form.total_costs_with_fee = sumStrings(
        props.form.costs_of_damages,
        props.form.transport_inbound,
        props.form.transport_outbound,
        props.form.costs_of_taxation,
        props.form.recycling_fee,
        props.form.sales_margin
    );

    props.form.sales_price_net = sumStrings(
        props.form.total_costs_with_fee,
        props.form.total_purchase_price
    );

    if (props.form.is_vat) {
        props.form.vat = numberFormat(
            Number(strToNum(props.form.sales_price_net)) *
                (props.form.vat_percentage / 100)
        );
    } else {
        props.form.vat = numberFormat(
            (Number(strToNum(props.form.fee_intermediate)) +
                Number(strToNum(props.form.total_costs_with_fee))) *
                (props.form.vat_percentage / 100)
        );
    }

    props.form.sales_price_incl_vat_or_margin = sumStrings(
        props.form.sales_price_net,
        props.form.vat
    );

    props.form.sales_price_total = sumStrings(
        props.form.sales_price_incl_vat_or_margin,
        props.form.leges_vat ?? 91.2,
        props.form.rest_bpm_indication
    );
};

const handleBlur = (name: string) => {
    if (!formFields.hasOwnProperty(name)) {
        return;
    }
    const value =
        typeof props.form[formFields[name]] === "string"
            ? strToNum(props.form[formFields[name]])
            : props.form[formFields[name]];
    props.form[formFields[name]] = !isNaN(value) ? numberFormat(value) : 0;
};

const handleFocus = (name: string) => {
    if (!formFields.hasOwnProperty(name)) {
        return;
    }
    props.form[formFields[name]] = isNaN(props.form[formFields[name]])
        ? strToNum(props.form[formFields[name]])
        : props.form[formFields[name]];
};

const formFields: { [key: string]: string } = {
    net_purchase_price: "net_purchase_price",
    fee_intermediate: "fee_intermediate",
    costs_of_damages: "costs_of_damages",
    transport_inbound: "transport_inbound",
    transport_outbound: "transport_outbound",
    costs_of_taxation: "costs_of_taxation",
    recycling_fee: "recycling_fee",
    purchase_cost_items_services: "purchase_cost_items_services",
    sale_price_net_including_services_and_products:
        "sale_price_net_including_services_and_products",
    sale_price_services_and_products: "sale_price_services_and_products",
    discount: "discount",
    sales_margin: "sales_margin",
    rest_bpm_indication: "rest_bpm_indication",
    leges_vat: "leges_vat",
    sales_price_total: "sales_price_total",
    selling_price_supplier: "selling_price_supplier",
};

const calculateSellPriceCurrencyEuro = () => {
    props.form.sell_price_currency_euro = convertUnitsToCurrency(
        (convertCurrencyToUnits(props.form.selling_price_supplier) as number) /
            (props.form.currency_exchange_rate ?? 1)
    );
};
</script>

<template>
    <label for="selling_price_supplier">
        {{ __("Selling Price Supplier") }}
    </label>
    <div class="flex gap-2">
        <Input
            v-model="form.selling_price_supplier"
            :name="'selling_price_supplier'"
            type="text"
            class="mb-3.5 sm:mb-0"
            :placeholder="__('Selling Price Supplier')"
            @change="calculateSellPriceCurrencyEuro"
            @blur="handleBlur('selling_price_supplier')"
            @focus="handleFocus('selling_price_supplier')"
        />
        <Select
            :key="form.original_currency"
            v-model="form.original_currency"
            :name="'original_currency'"
            :options="Currency"
            :placeholder="__('Original currency')"
            class="w-full mb-3.5 sm:mb-0"
        />
    </div>

    <label for="currency_exchange_rate">
        {{ __("Currency exchange rate") }}
    </label>
    <div class="flex gap-2">
        <Input
            v-model="form.currency_exchange_rate"
            :name="'currency_exchange_rate'"
            type="number"
            :placeholder="__('Currency exchange rate')"
            class="mb-3.5 sm:mb-0 col-span-2"
            @change="calculateSellPriceCurrencyEuro"
        />
        <Input
            v-model="form.sell_price_currency_euro"
            :name="'sell_price_currency_euro'"
            type="text"
            :disabled="true"
            :placeholder="__('Sell price currency euro')"
            class="mb-3.5 sm:mb-0 col-span-2"
        />
    </div>

    <label for="is_vat">
        {{ __("Pricing Type") }}
    </label>
    <RadioButtonToggle
        v-model="form.is_vat"
        name="is_vat"
        :left-button-label="__('VAT')"
        :right-button-label="__('Margin')"
        :disabled="form.is_locked"
        @change="calculate"
    />

    <label for="net_purchase_price">
        {{ __("Purchase price") }}
        {{ form.is_vat ? __("Net") : __("Margin") }}
        {{ replaceEnumUnderscores(companyCurrency) }}
    </label>
    <InputIcon
        v-model="form.net_purchase_price"
        :name="'net_purchase_price'"
        type="text"
        class="mb-3.5 sm:mb-0"
        :placeholder="__('Purchase price')"
        @keyup="calculate"
        @blur="handleBlur('net_purchase_price')"
        @focus="handleFocus('net_purchase_price')"
    >
        <template #secondIcon>
            <component
                :is="iconComponentMap[companyCurrency]"
                v-if="companyCurrency"
                class="text-[#909090]"
            />
        </template>
    </InputIcon>

    <label for="intermediate">
        {{ __("Intermediate") }}
    </label>
    <RadioButtonToggle
        v-model="form.intermediate"
        name="intermediate"
        @change="toggleIntermediate"
    />

    <label for="fee_intermediate">
        {{ __("Fee intermediate") }}
    </label>
    <div :class="form.is_locked ? 'grid grid-cols-2' : 'w-full'">
        <Input
            v-model="form.fee_intermediate"
            :name="'fee_intermediate'"
            type="text"
            class="mb-3.5 sm:mb-0 col-span-2 sm:col-start-2"
            :disabled="!form.intermediate || form.is_locked"
            :placeholder="__('Fee intermediate')"
            @keyup="calculate"
            @blur="handleBlur('fee_intermediate')"
            @focus="handleFocus('fee_intermediate')"
        />
    </div>

    <label for="total_purchase_price" class="font-semibold">
        {{ __("Total purchase price") }}
        {{ form.is_vat ? __("Net") : __("Margin") }}
    </label>
    <div class="grid grid-cols-2">
        <Input
            v-model="form.total_purchase_price"
            :name="'total_purchase_price'"
            :disabled="true"
            type="text"
            class="mb-3.5 sm:mb-0 col-span-2 sm:col-start-2"
            :placeholder="__('Total purchase price')"
        />
    </div>

    <label for="costs_of_damages">
        {{ __("Costs of damages") }}
    </label>
    <div :class="form.is_locked ? 'grid grid-cols-2' : 'w-full'">
        <Input
            v-model="form.costs_of_damages"
            :name="'costs_of_damages'"
            type="text"
            class="mb-3.5 sm:mb-0 col-span-2 sm:col-start-2"
            :disabled="form.is_locked"
            :placeholder="__('Costs of damages')"
            @keyup="calculate"
            @blur="handleBlur('costs_of_damages')"
            @focus="handleFocus('costs_of_damages')"
        />
    </div>

    <label for="transport_inbound">
        {{ __("Transport inbound") }}
    </label>
    <div :class="form.is_locked ? 'grid grid-cols-2' : 'w-full'">
        <Input
            v-model="form.transport_inbound"
            :name="'transport_inbound'"
            type="text"
            class="mb-3.5 sm:mb-0 col-span-2 sm:col-start-2"
            :placeholder="__('Transport inbound')"
            :disabled="form.is_locked"
            @keyup="calculate"
            @blur="handleBlur('transport_inbound')"
            @focus="handleFocus('transport_inbound')"
        />
    </div>

    <label for="transport_outbound">
        {{ __("Transport outbound") }}
    </label>
    <div :class="form.is_locked ? 'grid grid-cols-2' : 'w-full'">
        <Input
            v-model="form.transport_outbound"
            :name="'transport_outbound'"
            type="text"
            class="mb-3.5 sm:mb-0 col-span-2 sm:col-start-2"
            :placeholder="__('Transport outbound')"
            :disabled="form.is_locked"
            @keyup="calculate"
            @blur="handleBlur('transport_outbound')"
            @focus="handleFocus('transport_outbound')"
        />
    </div>

    <label for="costs_of_taxation">
        {{ __("Costs of taxation for BPM") }}
    </label>
    <div :class="form.is_locked ? 'grid grid-cols-2' : 'w-full'">
        <Input
            v-model="form.costs_of_taxation"
            :name="'costs_of_taxation'"
            type="text"
            class="mb-3.5 sm:mb-0 col-span-2 sm:col-start-2"
            :placeholder="__('Costs of taxation for BPM')"
            :disabled="form.is_locked"
            @keyup="calculate"
            @blur="handleBlur('costs_of_taxation')"
            @focus="handleFocus('costs_of_taxation')"
        />
    </div>

    <label for="recycling_fee">{{ __("Recycling fee") }}</label>
    <div :class="form.is_locked ? 'grid grid-cols-2' : 'w-full'">
        <Input
            v-model="form.recycling_fee"
            :name="'recycling_fee'"
            type="text"
            class="mb-3.5 sm:mb-0 col-span-2 sm:col-start-2"
            :placeholder="__('Recycling fee')"
            :disabled="form.is_locked"
            @keyup="calculate"
            @blur="handleBlur('recycling_fee')"
            @focus="handleFocus('recycling_fee')"
        />
    </div>

    <label for="purchase_cost_items_services">
        {{ __("Purchase costs Items and Services") }}</label
    >
    <div :class="form.is_locked ? 'grid grid-cols-2' : 'w-full'">
        <Input
            v-model="form.purchase_cost_items_services"
            :name="'purchase_cost_items_services'"
            type="text"
            class="mb-3.5 sm:mb-0 col-span-2 sm:col-start-2"
            :placeholder="__('Purchase costs Items and Services')"
            :disabled="true"
            @keyup="calculate"
            @blur="handleBlur('purchase_cost_items_services')"
            @focus="handleFocus('purchase_cost_items_services')"
        />
    </div>

    <label for="sales_margin" class="font-semibold">
        {{ __("Sales Margin") }}
    </label>
    <div :class="form.is_locked ? 'grid grid-cols-2' : 'w-full'">
        <Input
            v-model="form.sales_margin"
            :name="'sales_margin'"
            type="text"
            class="mb-3.5 sm:mb-0 col-span-2 sm:col-start-2"
            :disabled="form.is_locked"
            :placeholder="__('Sales Margin')"
            @keyup="calculate"
            @blur="handleBlur('sales_margin')"
            @focus="handleFocus('sales_margin')"
        />
    </div>

    <label for="total_costs_with_fee">
        {{ __("Total costs and fee") }}
    </label>
    <div class="grid grid-cols-2">
        <Input
            v-model="form.total_costs_with_fee"
            :name="'total_costs_with_fee'"
            type="text"
            class="mb-3.5 sm:mb-0 col-span-2 sm:col-start-2"
            :disabled="true"
            :placeholder="__('Total costs and fee')"
        />
    </div>

    <label for="sales_price_net" class="font-semibold">
        {{ __("Sales price net") }}
        ({{ form.is_vat ? __("ex/ex") : __("margin") }})
    </label>
    <Input
        v-model="form.sales_price_net"
        :name="'sales_price_net'"
        type="text"
        class="mb-3.5 sm:mb-0"
        :placeholder="__('Sales price net')"
        @blur="formatPriceOnBlur(form, 'sales_price_net')"
        @focus="formatPriceOnFocus(form, 'sales_price_net')"
    />

    <label for="sale_price_services_and_products">
        {{ __("Services And Products") }}</label
    >
    <div :class="form.is_locked ? 'grid grid-cols-2' : 'w-full'">
        <Input
            v-model="form.sale_price_services_and_products"
            :name="'sale_price_services_and_products'"
            type="text"
            class="mb-3.5 sm:mb-0 col-span-2 sm:col-start-2"
            :placeholder="__('Services And Products')"
            :disabled="true"
            @keyup="calculate"
            @blur="handleBlur('sale_price_services_and_products')"
            @focus="handleFocus('sale_price_services_and_products')"
        />
    </div>

    <label for="discount"> {{ __("Discount") }}</label>
    <div :class="form.is_locked ? 'grid grid-cols-2' : 'w-full'">
        <Input
            v-model="form.discount"
            :name="'discount'"
            type="text"
            class="mb-3.5 sm:mb-0 col-span-2 sm:col-start-2"
            :placeholder="__('Discount')"
            :disabled="true"
            @keyup="calculate"
            @blur="handleBlur('discount')"
            @focus="handleFocus('discount')"
        />
    </div>

    <label for="sale_price_net_including_services_and_products">
        {{ __("Price With Services And Products") }}</label
    >
    <div :class="form.is_locked ? 'grid grid-cols-2' : 'w-full'">
        <Input
            v-model="form.sale_price_net_including_services_and_products"
            :name="'sale_price_net_including_services_and_products'"
            type="text"
            class="mb-3.5 sm:mb-0 col-span-2 sm:col-start-2"
            :placeholder="__('Price With Services And Products')"
            :disabled="true"
            @keyup="calculate"
            @blur="handleBlur('sale_price_net_including_services_and_products')"
            @focus="
                handleFocus('sale_price_net_including_services_and_products')
            "
        />
    </div>

    <label for="vat">
        {{ __("VAT") }}
        <span v-if="form.vat_percentage"> ({{ form.vat_percentage }}%) </span>

        <span v-if="!form.is_vat">
            {{ __("on Costs and Fee") }}
        </span>
    </label>
    <div class="grid grid-cols-2">
        <Input
            v-model="form.vat"
            :name="'vat'"
            type="text"
            class="mb-3.5 sm:mb-0 col-span-2 sm:col-start-2"
            :disabled="true"
            :placeholder="__('VAT')"
        />
    </div>

    <label for="sales_price_incl_vat_or_margin">
        {{ __("Sales price") }}
        {{ form.is_vat ? __("including VAT") : __("margin") }}
    </label>
    <Input
        v-model="form.sales_price_incl_vat_or_margin"
        :name="'sales_price_incl_vat_or_margin'"
        type="text"
        class="mb-3.5 sm:mb-0"
        :placeholder="__('Sales price including')"
    />

    <label for="rest_bpm_indication">
        {{ __("Rest BPM (indication)") }}
    </label>
    <Input
        v-model="form.rest_bpm_indication"
        :name="'rest_bpm_indication'"
        type="text"
        class="mb-3.5 sm:mb-0"
        :placeholder="__('Rest BPM (indication)')"
        @keyup="calculate"
        @blur="handleBlur('rest_bpm_indication')"
        @focus="handleFocus('rest_bpm_indication')"
    />

    <label for="leges_vat">
        {{ __("Leges (VAT)") }}
    </label>
    <div :class="form.is_locked ? 'grid grid-cols-2' : 'w-full'">
        <Input
            v-model="form.leges_vat"
            :name="'leges_vat'"
            type="text"
            class="mb-3.5 sm:mb-0 col-span-2 sm:col-start-2"
            :placeholder="__('Leges (VAT)')"
            :disabled="form.is_locked"
            @keyup="calculate"
            @blur="handleBlur('leges_vat')"
            @focus="handleFocus('leges_vat')"
        />
    </div>

    <label for="sales_price_total" class="font-semibold mb-8">
        {{ __("Sales price total") }}
        ({{ form.is_vat ? "in/in" : __("margin") }})
    </label>
    <Input
        v-model="form.sales_price_total"
        :name="'sales_price_total'"
        type="text"
        class="mb-8"
        :placeholder="__('Sales price total')"
        @blur="formatPriceOnBlur(form, 'sales_price_total')"
        @focus="formatPriceOnFocus(form, 'sales_price_total')"
        @keyup="calculate"
    />

    <label for="is_locked">
        {{ __("Lock sales price") }}
    </label>
    <RadioButtonToggle
        v-model="form.is_locked"
        name="is_locked"
        @change="calculate"
    />

    <label for="gross_bpm">
        {{ __("Gross BPM (indication)") }}
    </label>
    <Input
        v-model="form.gross_bpm"
        :name="'gross_bpm'"
        type="text"
        class="mb-3.5 sm:mb-0"
    />

    <label for="bpm_percent" class="font-semibold">
        {{ __("Depreciation Percentage BPM") }}
    </label>
    <div class="grid grid-cols-2">
        <Input
            v-model="form.bpm_percent"
            :name="'bpm_percent'"
            :disabled="true"
            type="text"
            class="mb-3.5 sm:mb-0 col-span-2 sm:col-start-2"
        />
    </div>

    <label for="bpm">
        {{ __("BPM") }}
    </label>
    <Input
        v-model="form.bpm"
        :name="'bpm'"
        type="text"
        class="mb-3.5 sm:mb-0"
    />
</template>
