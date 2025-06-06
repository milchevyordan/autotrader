<script setup lang="ts">
import { computed, watch } from "vue";

import Accordion from "@/components/Accordion.vue";
import Input from "@/components/html/Input.vue";
import RadioButtonToggle from "@/components/html/RadioButtonToggle.vue";
import { Multiselect } from "@/data-table/types";
import {
    OrderItem,
    AdditionalService,
    QuoteForm,
    ServiceLevel,
    Vehicle,
} from "@/types";
import {
    convertCurrencyToUnits,
    convertUnitsToCurrency,
    formatPriceOnBlur,
    formatPriceOnFocus,
} from "@/utils";

const props = defineProps<{
    form: QuoteForm;
    vehicles: Vehicle[];
    serviceLevels?: Multiselect<ServiceLevel>;
    paymentCondition?: number;
    vehiclesCount: number;
    disabled?: boolean;
}>();

watch(
    () => [
        props.vehiclesCount,
        props.form.discount,
        props.form.items,
        props.form.additional_services,
    ],
    () => {
        calculatePrices();
    }
);

const allItemsAndServicesSalesPrice = computed(
    () =>
        (props.form.items.reduce(
            (sum: number, item: OrderItem) =>
                sum + Number(convertCurrencyToUnits(item.sale_price)),
            0
        ) +
            props.form.additional_services.reduce(
                (sum: number, additionalService: AdditionalService) =>
                    sum +
                    Number(
                        convertCurrencyToUnits(additionalService.sale_price)
                    ),
                0
            )) *
        props.vehiclesCount
);

const calculatePrices = () => {
    props.form.total_vehicles_purchase_price = convertUnitsToCurrency(
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

    props.form.total_costs = convertUnitsToCurrency(
        props.vehicles.reduce(
            (sum, vehicle) =>
                sum +
                Number(
                    convertCurrencyToUnits(
                        vehicle.calculation.costs_of_damages as string
                    )
                ) +
                Number(
                    convertCurrencyToUnits(
                        vehicle.calculation.transport_inbound as string
                    )
                ),
            0
        ) +
            props.form.items.reduce(
                (sum: number, item: OrderItem) =>
                    sum +
                    Number(convertCurrencyToUnits(item.item?.purchase_price)),
                0
            ) *
                props.vehiclesCount +
            props.form.additional_services.reduce(
                (sum: number, additionalService: AdditionalService) =>
                    sum +
                    Number(
                        convertCurrencyToUnits(additionalService.purchase_price)
                    ),
                0
            ) *
                props.vehiclesCount
    );

    props.form.total_sales_price_service_items = convertUnitsToCurrency(
        allItemsAndServicesSalesPrice.value
    );

    props.form.total_fee_intermediate_supplier = convertUnitsToCurrency(
        Number(
            props.vehicles.reduce(
                (sum, vehicle) =>
                    sum +
                    Number(
                        convertCurrencyToUnits(
                            vehicle.calculation.fee_intermediate as string
                        )
                    ),
                0
            )
        )
    );

    props.form.total_sales_price_exclude_vat = convertUnitsToCurrency(
        Number(
            props.vehicles.reduce(
                (sum, vehicle) =>
                    sum +
                    <number>(
                        convertCurrencyToUnits(
                            vehicle.calculation.sales_price_net as string
                        )
                    ),
                0
            )
        )
    );

    props.form.total_sales_price_include_vat = convertUnitsToCurrency(
        Number(
            props.vehicles.reduce(
                (sum, vehicle) =>
                    sum +
                    <number>(
                        convertCurrencyToUnits(
                            vehicle.calculation
                                .sales_price_incl_vat_or_margin as string
                        )
                    ),
                0
            )
        )
    );

    props.form.total_sales_margin = convertUnitsToCurrency(
        Number(
            props.vehicles.reduce(
                (sum, vehicle) =>
                    sum +
                    <number>(
                        convertCurrencyToUnits(
                            vehicle.calculation.sales_margin as string
                        )
                    ),
                0
            )
        )
    );

    props.form.total_registration_fees = convertUnitsToCurrency(
        Number(
            props.vehicles.reduce(
                (sum, vehicle) =>
                    sum +
                    <number>(
                        convertCurrencyToUnits(
                            vehicle.calculation.leges_vat as string
                        )
                    ),
                0
            )
        )
    );

    calculateBruttoNettoPrices();
};

const calculateBruttoNettoPrices = () => {
    let totalSalesPriceExcludeVatUnits =
        Number(
            convertCurrencyToUnits(props.form.total_sales_price_exclude_vat)
        ) +
        allItemsAndServicesSalesPrice.value +
        Number(convertCurrencyToUnits(props.form.discount)) *
            props.vehiclesCount;

    props.form.total_quote_price_exclude_vat = convertUnitsToCurrency(
        totalSalesPriceExcludeVatUnits
    );

    if (props.form.is_brutto) {
        let totalVatUnits =
            totalSalesPriceExcludeVatUnits * (props.form.vat_percentage / 100);

        props.form.total_vat = convertUnitsToCurrency(totalVatUnits);

        let bpmUnits = Number(
            props.vehicles.reduce(
                (sum, vehicle) =>
                    sum +
                    <number>(
                        convertCurrencyToUnits(
                            vehicle.calculation.rest_bpm_indication as string
                        )
                    ),
                0
            )
        );
        props.form.total_bpm = convertUnitsToCurrency(bpmUnits);

        props.form.total_quote_price = convertUnitsToCurrency(
            totalSalesPriceExcludeVatUnits +
                totalVatUnits +
                bpmUnits +
                Number(
                    convertCurrencyToUnits(props.form.total_registration_fees)
                )
        );
    } else {
        props.form.total_bpm = null;
        props.form.total_vat = null;
        props.form.total_quote_price = null;
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
                    {{ __("Summary Financial Information") }}
                </div>
            </template>

            <div
                class="my-4 flex flex-col sm:flex-row items-start sm:items-center sm:justify-end gap-3 sm:gap-5"
            >
                <p class="text-sm sm:text-base order-1 sm:order-none">
                    * {{ __("With Items And Additional Services") }}
                </p>

                <div class="flex flex-wrap gap-3 order-3 sm:order-none">
                    <button
                        v-if="!disabled"
                        class="rounded px-4 py-1.5 active:scale-95 bg-[#00A793] hover:bg-emerald-500 h-8 text-white text-xs"
                        @click="calculatePrices"
                    >
                        {{ __("Calculate") }}
                    </button>

                    <RadioButtonToggle
                        :key="form.is_brutto"
                        v-model="form.is_brutto"
                        :name="'is_brutto'"
                        :left-button-label="'Brutto'"
                        :right-button-label="'Netto'"
                        class="text-xs"
                        :disabled="disabled"
                        @change="calculateBruttoNettoPrices"
                    />
                </div>
            </div>

            <div class="grid lg:grid-cols-1 xl:grid-cols-2 gap-4">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:border-r border-[#E9E7E7] xl:pr-8 sm:gap-y-2 items-center"
                >
                    <label for="total_vehicles_purchase_price">
                        {{ __("Total Vehicles Purchase price") }} *
                    </label>
                    <Input
                        v-model="form.total_vehicles_purchase_price"
                        :name="'total_vehicles_purchase_price'"
                        :disabled="disabled"
                        type="text"
                        :placeholder="__('Total purchase price')"
                        class="mb-3.5 sm:mb-0"
                        @focus="
                            formatPriceOnFocus(
                                form,
                                'total_vehicles_purchase_price'
                            )
                        "
                        @blur="
                            formatPriceOnBlur(
                                form,
                                'total_vehicles_purchase_price'
                            )
                        "
                    />

                    <label for="total_costs">
                        {{ __("Total Costs") }}
                    </label>
                    <Input
                        v-model="form.total_costs"
                        :name="'total_costs'"
                        :disabled="true"
                        type="text"
                        :placeholder="__('Total Costs')"
                        class="mb-3.5 sm:mb-0"
                        @focus="formatPriceOnFocus(form, 'total_costs')"
                        @blur="formatPriceOnBlur(form, 'total_costs')"
                    />

                    <label for="total_fee_intermediate_supplier">
                        {{ __("Total fee intermediate supplier") }}
                    </label>
                    <Input
                        v-model="form.total_fee_intermediate_supplier"
                        :name="'total_fee_intermediate_supplier'"
                        :disabled="disabled"
                        type="text"
                        :placeholder="__('Total fee intermediate supplier')"
                        class="mb-3.5 sm:mb-0"
                        @focus="
                            formatPriceOnFocus(
                                form,
                                'total_fee_intermediate_supplier'
                            )
                        "
                        @blur="
                            formatPriceOnBlur(
                                form,
                                'total_fee_intermediate_supplier'
                            )
                        "
                    />

                    <label for="total_sales_price_include_vat">
                        {{ __("Total sales price in/in") }}
                    </label>
                    <Input
                        v-model="form.total_sales_price_include_vat"
                        :name="'total_sales_price_include_vat'"
                        :disabled="disabled"
                        type="text"
                        :placeholder="__('Total sales price in/in')"
                        class="mb-3.5 sm:mb-0"
                        @focus="
                            formatPriceOnFocus(
                                form,
                                'total_sales_price_include_vat'
                            )
                        "
                        @blur="
                            formatPriceOnBlur(
                                form,
                                'total_sales_price_include_vat'
                            )
                        "
                    />

                    <label for="total_sales_margin">
                        {{ __("Total Sales margin") }}
                    </label>
                    <Input
                        v-model="form.total_sales_margin"
                        :name="'total_sales_margin'"
                        :disabled="disabled"
                        type="text"
                        :placeholder="__('Total Sales margin')"
                        class="mb-3.5 sm:mb-0"
                        @focus="formatPriceOnFocus(form, 'total_sales_margin')"
                        @blur="formatPriceOnBlur(form, 'total_sales_margin')"
                    />
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:pl-4 sm:gap-y-2 items-center"
                >
                    <label for="calculation_on_quote">
                        {{ __("Calculation On Quote") }}
                    </label>
                    <RadioButtonToggle
                        :key="form.calculation_on_quote"
                        v-model="form.calculation_on_quote"
                        :name="'calculation_on_quote'"
                        :disabled="disabled"
                    />

                    <label for="total_sales_price_exclude_vat">
                        {{ __("Total sales price exclude VAT") }}
                    </label>
                    <Input
                        v-model="form.total_sales_price_exclude_vat"
                        :name="'total_sales_price_exclude_vat'"
                        :disabled="disabled"
                        type="text"
                        :placeholder="__('Total sales price exclude VAT')"
                        class="mb-3.5 sm:mb-0"
                        @keyup="calculateBruttoNettoPrices"
                        @focus="
                            formatPriceOnFocus(
                                form,
                                'total_sales_price_exclude_vat'
                            )
                        "
                        @blur="
                            formatPriceOnBlur(
                                form,
                                'total_sales_price_exclude_vat'
                            )
                        "
                    />

                    <label for="total_sales_price_service_items">
                        {{ __("Total Sales price service items and extra`s") }}
                    </label>
                    <Input
                        v-model="form.total_sales_price_service_items"
                        :name="'total_sales_price_service_items'"
                        :disabled="disabled"
                        type="text"
                        :placeholder="
                            __('Total Sales price service items and extra`s')
                        "
                        class="mb-3.5 sm:mb-0"
                        @focus="
                            formatPriceOnFocus(
                                form,
                                'total_sales_price_service_items'
                            )
                        "
                        @blur="
                            formatPriceOnBlur(
                                form,
                                'total_sales_price_service_items'
                            )
                        "
                    />

                    <label for="total_quote_price_exclude_vat">
                        {{ __("Total quote price exclude VAT") }} *
                    </label>
                    <Input
                        v-model="form.total_quote_price_exclude_vat"
                        :name="'total_quote_price_exclude_vat'"
                        :disabled="disabled"
                        type="text"
                        :placeholder="__('Total quote price exclude VAT')"
                        class="mb-3.5 sm:mb-0"
                        @focus="
                            formatPriceOnFocus(
                                form,
                                'total_quote_price_exclude_vat'
                            )
                        "
                        @blur="
                            formatPriceOnBlur(
                                form,
                                'total_quote_price_exclude_vat'
                            )
                        "
                    />

                    <label for="total_vat">{{ __("Total VAT") }}</label>
                    <Input
                        v-model="form.total_vat"
                        :name="'total_vat'"
                        :disabled="disabled"
                        type="text"
                        :placeholder="__('Total VAT')"
                        class="mb-3.5 sm:mb-0"
                        @focus="formatPriceOnFocus(form, 'total_vat')"
                        @blur="formatPriceOnBlur(form, 'total_vat')"
                    />

                    <label for="total_bpm">{{ __("Total BPM") }}</label>
                    <Input
                        v-model="form.total_bpm"
                        :name="'total_bpm'"
                        :disabled="disabled"
                        type="text"
                        :placeholder="__('Total BPM')"
                        class="mb-3.5 sm:mb-0"
                        @focus="formatPriceOnFocus(form, 'total_bpm')"
                        @blur="formatPriceOnBlur(form, 'total_bpm')"
                    />

                    <label for="total_registration_fees">
                        {{ __("Total registration fees") }}
                    </label>
                    <Input
                        v-model="form.total_registration_fees"
                        :name="'total_registration_fees'"
                        :disabled="disabled"
                        type="text"
                        :placeholder="__('Total registration fees')"
                        class="mb-3.5 sm:mb-0"
                        @focus="
                            formatPriceOnFocus(form, 'total_registration_fees')
                        "
                        @blur="
                            formatPriceOnBlur(form, 'total_registration_fees')
                        "
                    />

                    <label for="total_quote_price">
                        {{ __("Total quote price") }} *
                    </label>
                    <Input
                        v-model="form.total_quote_price"
                        :name="'total_quote_price'"
                        :disabled="disabled"
                        type="text"
                        :placeholder="__('Total quote price')"
                        class="mb-3.5 sm:mb-0"
                        @focus="formatPriceOnFocus(form, 'total_quote_price')"
                        @blur="formatPriceOnBlur(form, 'total_quote_price')"
                    />
                </div>
            </div>
        </Accordion>
    </div>
</template>
