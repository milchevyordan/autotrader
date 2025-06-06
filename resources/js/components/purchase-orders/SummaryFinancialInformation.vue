<script setup lang="ts">
import { InertiaForm } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";

import Accordion from "@/components/Accordion.vue";
import Input from "@/components/html/Input.vue";
import InputIcon from "@/components/html/InputIcon.vue";
import { Currency } from "@/enums/Currency";
import { NationalEuOrWorldType } from "@/enums/NationalEuOrWorldType";
import { companyCurrency } from "@/globals";
import { __ } from "@/translations";
import { PurchaseOrderForm, Vehicle } from "@/types";
import {
    convertCurrencyToUnits,
    convertUnitsToCurrency,
    findEnumKeyByValue,
    formatPriceOnBlur,
    formatPriceOnFocus,
    iconComponentMap,
    replaceEnumUnderscores,
    sumCurrencyValues,
} from "@/utils";

const props = defineProps<{
    form: InertiaForm<PurchaseOrderForm>;
    vehicles: Vehicle[];
    formDisabled?: boolean;
}>();

const totalPurchaseValue = ref<string>();

const calculatePrices = () => {
    totalPurchaseValue.value = convertUnitsToCurrency(
        Number(
            props.vehicles.reduce(
                (sum, vehicle) =>
                    sum +
                    <number>(
                        convertCurrencyToUnits(
                            vehicle.calculation.total_purchase_price as string
                        )
                    ),
                0
            )
        )
    );

    props.form.total_purchase_price = totalPurchaseValue.value;

    props.form.total_purchase_price_eur = convertUnitsToCurrency(
        (convertCurrencyToUnits(totalPurchaseValue.value) as number) *
            props.form.currency_rate || 0
    );

    props.form.total_fee_intermediate_supplier = sumCurrencyValues(props.vehicles, v => (v as Vehicle).calculation.fee_intermediate);

    props.form.total_purchase_price_exclude_vat = sumCurrencyValues(props.vehicles, v => (v as Vehicle).calculation.net_purchase_price);

    props.form.total_transport = props.form.transport_included ? "" : sumCurrencyValues(props.vehicles, v => (v as Vehicle).calculation.transport_inbound);

    props.form.total_vat = sumCurrencyValues(props.vehicles, v => (v as Vehicle).calculation.vat);

    if (props.form.type != NationalEuOrWorldType.EU) {
        props.form.total_bpm = sumCurrencyValues(props.vehicles, v => (v as Vehicle).calculation.bpm);
    } else {
        props.form.total_bpm = "";
    }

    props.form.total_purchase_price_include_vat = totalPurchaseValue.value;
};

watch(props.vehicles, () => {
    calculatePrices();
});

const purchaseOrderCurrency = computed(() => {
    return __(
        replaceEnumUnderscores(
            findEnumKeyByValue(Currency, props.form.currency_po)
        )
    );
});

defineExpose({ calculatePrices });
</script>

<template>
    <div
        class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
    >
        <Accordion>
            <template #head>
                <div class="font-semibold text-xl sm:text-2xl mb-4">
                    {{ __("Summary Financial Information") }}
                </div>
            </template>

            <div
                class="my-4 flex flex-col sm:flex-row items-start sm:items-center sm:justify-end gap-3 sm:gap-5"
            >
                <button
                    v-if="!formDisabled"
                    class="rounded px-4 py-1.5 active:scale-95 bg-[#00A793] hover:bg-emerald-500 h-8 text-white text-xs"
                    @click="calculatePrices"
                >
                    {{ __("Calculate") }}
                </button>
            </div>

            <div class="grid lg:grid-cols-1 xl:grid-cols-2 gap-4">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:border-r border-[#E9E7E7] xl:pr-8 sm:gap-y-2 items-center"
                >
                    <label for="total_purchase_price">
                        {{ __("Total purchase price") }}
                        {{ replaceEnumUnderscores(purchaseOrderCurrency) }}
                    </label>
                    <InputIcon
                        v-model="form.total_purchase_price"
                        :name="'total_purchase_price'"
                        :disabled="true"
                        type="text"
                        :placeholder="
                            __('Total purchase price') +
                            ' ' +
                            replaceEnumUnderscores(purchaseOrderCurrency)
                        "
                        class="mb-3.5 sm:mb-0"
                    >
                        <template #secondIcon>
                            <component
                                :is="iconComponentMap[purchaseOrderCurrency]"
                                v-if="purchaseOrderCurrency"
                                class="text-[#909090]"
                            />
                        </template>
                    </InputIcon>
                    <label for="total_purchase_price_eur">
                        {{ __("Total purchase price") }}
                        {{ replaceEnumUnderscores(companyCurrency) }}
                    </label>
                    <InputIcon
                        v-model="form.total_purchase_price_eur"
                        :name="'total_purchase_price_eur'"
                        :disabled="true"
                        type="text"
                        :placeholder="
                            __('Total purchase price') +
                            ' ' +
                            replaceEnumUnderscores(companyCurrency)
                        "
                        class="mb-3.5 sm:mb-0"
                    >
                        <template #secondIcon>
                            <component
                                :is="iconComponentMap[companyCurrency]"
                                v-if="companyCurrency"
                                class="text-[#909090]"
                            />
                        </template>
                    </InputIcon>

                    <label for="total_fee_intermediate_supplier">
                        {{ __("Total fee intermediate supplier") }}
                    </label>
                    <Input
                        v-model="form.total_fee_intermediate_supplier"
                        :name="'total_fee_intermediate_supplier'"
                        :disabled="true"
                        type="text"
                        :placeholder="__('Total fee intermediate supplier')"
                    />

                    <label for="total_purchase_price_exclude_vat">
                        {{ __("Total purchase price exclude VAT") }}
                    </label>
                    <Input
                        v-model="form.total_purchase_price_exclude_vat"
                        :name="'total_purchase_price_exclude_vat'"
                        :disabled="true"
                        type="text"
                        :placeholder="__('Total purchase price exclude VAT')"
                        class="mb-3.5 sm:mb-0"
                    />
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:pl-4 sm:gap-y-2 items-center"
                >
                    <label for="currency_rate">
                        {{ __("Currency rate") }}
                    </label>
                    <div class="flex justify-end">
                        <div class="w-2/5">
                            <Input
                                v-model="form.currency_rate"
                                :name="'currency_rate'"
                                type="number"
                                min="0"
                                :disabled="formDisabled"
                                placeholder="Currency Rate"
                                class="mb-3.5 sm:mb-0"
                                @input="calculatePrices"
                            />
                        </div>
                    </div>

                    <label for="total_transport">{{
                        __("Total Transport")
                    }}</label>
                    <Input
                        v-model="form.total_transport"
                        :name="'total_transport'"
                        :disabled="true"
                        type="text"
                        :placeholder="__('Total Transport')"
                        class="mb-3.5 sm:mb-0"
                        @focus="formatPriceOnFocus(form, 'total_transport')"
                        @blur="formatPriceOnBlur(form, 'total_transport')"
                    />

                    <label for="total_vat">{{
                        form.type == NationalEuOrWorldType.EU
                            ? __("VAT deposit")
                            : __("Total VAT")
                    }}</label>
                    <Input
                        v-model="form.total_vat"
                        :name="'total_vat'"
                        :disabled="true"
                        :placeholder="__('VAT')"
                        type="text"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="total_bpm">{{ __("Total BPM") }}</label>
                    <Input
                        v-model="form.total_bpm"
                        :name="'total_bpm'"
                        :disabled="form.type != NationalEuOrWorldType.National"
                        type="text"
                        :placeholder="__('Total BPM')"
                        class="mb-3.5 sm:mb-0"
                        @focus="formatPriceOnFocus(form, 'total_bpm')"
                        @blur="formatPriceOnBlur(form, 'total_bpm')"
                    />

                    <label for="total_purchase_price_include_vat">
                        {{ __("Total purchase price in/in") }}
                    </label>
                    <Input
                        v-model="form.total_purchase_price_include_vat"
                        :name="'total_purchase_price_include_vat'"
                        :disabled="true"
                        type="text"
                        :placeholder="__('Total purchase price in/in')"
                        class="mb-3.5 sm:mb-0"
                    />
                </div>
            </div>
        </Accordion>
    </div>
</template>
