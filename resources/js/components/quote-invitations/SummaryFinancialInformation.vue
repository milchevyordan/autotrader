<script setup lang="ts">
import Accordion from "@/components/Accordion.vue";
import Input from "@/components/html/Input.vue";
import { Quote } from "@/types";
import { convertCurrencyToUnits, convertUnitsToCurrency } from "@/utils";

defineProps<{
    quote: Quote;
    allItemsAndAdditionals: boolean;
    vehiclesCount: number;
}>();
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

            <div class="justify-end my-4">
                <div class="flex sm:justify-end mb-3.5 sm:mb-0">
                    <span
                        class="bg-[#008FE3] text-white border-blue-200 border border-r-0 rounded-l-md border-l-0 rounded-r-md px-2"
                    >
                        {{ quote.is_brutto ? "Brutto" : "Netto" }}
                    </span>
                </div>
            </div>
            <div class="flex flex-col gap-4 w-2/3">
                <div>
                    <label
                        v-if="quote.service_level"
                        for="total_fee_intermediate_supplier"
                    >
                        {{ __("Service Level") }}
                    </label>
                    <Input
                        v-if="quote.service_level"
                        v-model="quote.service_level.name"
                        :name="'service_level_id'"
                        :disabled="true"
                        type="text"
                        :placeholder="__('Service Level')"
                    />
                </div>

                <div
                    v-if="quote.calculation_on_quote && allItemsAndAdditionals"
                >
                    <label for="total_sales_price">
                        {{ __("Total sales price exclude VAT") }},
                        {{ vehiclesCount }} {{ __("vehicles") }}
                    </label>
                    <Input
                        v-model="quote.total_sales_price_exclude_vat"
                        :name="'total_sales_price'"
                        :disabled="true"
                        type="text"
                        :placeholder="__('Total sales price exclude VAT')"
                        class="mb-3.5 sm:mb-0"
                    />
                </div>

                <div v-if="allItemsAndAdditionals">
                    <label for="total_sales_price_service_items">
                        {{ __("Total Sales price service items and extra`s") }}
                    </label>
                    <Input
                        v-model="quote.total_sales_price_service_items"
                        :name="'total_sales_price_service_items'"
                        :disabled="true"
                        type="text"
                        :placeholder="
                            __('Total Sales price service items and extra`s')
                        "
                        class="mb-3.5 sm:mb-0"
                    />
                </div>

                <div v-if="quote.discount && quote.discount_in_output">
                    <label for="total_sales_price_service_items">
                        {{ __("Discount") }} ({{ vehiclesCount }}x)
                    </label>
                    <Input
                        :model-value="
                            convertUnitsToCurrency(
                                Number(convertCurrencyToUnits(quote.discount)) *
                                    vehiclesCount
                            )
                        "
                        :name="'discount'"
                        :disabled="true"
                        type="text"
                        :placeholder="__('Discount')"
                        class="mb-3.5 sm:mb-0"
                    />
                </div>

                <div v-if="quote.calculation_on_quote || !quote.is_brutto">
                    <label for="total_quote_price_exclude_vat">
                        {{ __("Total quote price exclude VAT") }},
                        {{ vehiclesCount }} {{ __("vehicles") }}
                    </label>
                    <Input
                        v-model="quote.total_quote_price_exclude_vat"
                        :name="'total_quote_price_exclude_vat'"
                        :disabled="true"
                        type="text"
                        :placeholder="__('Total quote price exclude VAT')"
                        class="mb-3.5 sm:mb-0"
                    />
                </div>

                <div v-if="quote.is_brutto && quote.calculation_on_quote">
                    <label for="total_vat">{{ __("Total VAT") }}</label>
                    <Input
                        v-model="quote.total_vat"
                        :name="'total_vat'"
                        :disabled="true"
                        type="text"
                        :placeholder="__('VAT')"
                        class="mb-3.5 sm:mb-0"
                    />
                </div>

                <div v-if="quote.is_brutto && quote.calculation_on_quote">
                    <label for="total_bpm">
                        {{ __("Total BPM") }}
                    </label>
                    <Input
                        v-model="quote.total_bpm"
                        :name="'total_bpm'"
                        :disabled="true"
                        type="text"
                        :placeholder="__('Total BPM')"
                        class="mb-3.5 sm:mb-0"
                    />
                </div>

                <div v-if="quote.is_brutto && quote.calculation_on_quote">
                    <label for="total_registration_fees">
                        {{ __("Total registration fees") }}
                    </label>
                    <Input
                        v-model="quote.total_registration_fees"
                        :name="'total_registration_fees'"
                        :disabled="true"
                        type="text"
                        :placeholder="__('Total registration fees')"
                        class="mb-3.5 sm:mb-0"
                    />
                </div>

                <div v-if="quote.is_brutto">
                    <label for="total_quote_price">
                        {{ __("Total quote price") }}
                    </label>
                    <Input
                        v-model="quote.total_quote_price"
                        :name="'total_quote_price'"
                        :disabled="true"
                        type="text"
                        :placeholder="__('Total quote price')"
                        class="mb-3.5 sm:mb-0"
                    />
                </div>
            </div>
        </Accordion>
    </div>
</template>
