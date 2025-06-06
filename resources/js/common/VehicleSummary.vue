<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";

import WeekRangePicker from "@/components/html/WeekRangePicker.vue";
import InformationRow from "@/components/html/InformationRow.vue";
import Section from "@/components/html/Section.vue";
import { ColorType } from "@/enums/ColorType";
import { Transmission } from "@/enums/Transmission";
import { Quote, Vehicle, WeekPicker } from "@/types";
import {
    convertCurrencyToUnits,
    convertUnitsToCurrency,
    findEnumKeyByValue,
    getImage,
    replaceEnumUnderscores,
    sumStrings,
} from "@/utils";

const props = defineProps<{
    vehicle: Vehicle;
    vehiclesPriceFixed: boolean;
    salesPriceServiceItemsPerVehicleUnits: number;
    quote: Quote;
    deliveryWeek?: WeekPicker;
    allItemsAndAdditionals: boolean;
}>();

const calculatedPrices = computed(() => {
    if (props.vehiclesPriceFixed) {
        return {
            priceNet:
                props.vehicle.calculation
                    .sale_price_net_including_services_and_products,
            vat: props.vehicle.calculation.vat,
            priceBrutto: props.vehicle.calculation.sales_price_total,
        };
    }

    let priceNetUnits =
        Number(
            convertCurrencyToUnits(
                props.vehicle.calculation.sales_price_net as string
            )
        ) +
        Number(convertCurrencyToUnits(props.quote.discount as string)) +
        props.salesPriceServiceItemsPerVehicleUnits;
    let priceNet = convertUnitsToCurrency(priceNetUnits);
    let vatUnits =
        priceNetUnits * ((props.vehicle.calculation.vat_percentage ?? 0) / 100);
    let vat = convertUnitsToCurrency(vatUnits);
    let priceBrutto = sumStrings(
        priceNet,
        vat,
        <string>props.vehicle.calculation.rest_bpm_indication,
        <string>props.vehicle.calculation.leges_vat
    );

    return {
        priceNet: priceNet,
        vat: vat,
        priceBrutto: priceBrutto,
    };
});
</script>

<template>
    <div
        class="information-container flex flex-wrap justify-around my-2 p-4 gap-4 rounded-md"
    >
        <Section classes="h-fit w-full max-w-sm">
            <div class="rounded-t-lg overflow-hidden pb-2 flex justify-center">
                <img
                    class="w-auto max-h-72"
                    :src="getImage(vehicle.image_path)"
                    alt="vehicle image"
                />
            </div>

            <InformationRow
                v-if="vehicle.creator"
                :title="__('Creator of resource')"
            >
                {{ vehicle.creator.full_name }}
            </InformationRow>

            <div class="p-4 border-b border-[#E9E7E7] font-bold">
                {{ __("Vehicle") }} #{{ vehicle.id }}
            </div>

            <InformationRow v-if="vehicle.vin" :title="__('VIN')">
                {{ vehicle.vin }}
            </InformationRow>

            <InformationRow v-if="vehicle.make" :title="__('Make')">
                {{ vehicle.make.name }}
            </InformationRow>

            <InformationRow v-if="vehicle.vehicle_model" :title="__('Model')">
                {{ vehicle.vehicle_model.name }}
            </InformationRow>

            <InformationRow v-if="vehicle.engine" :title="__('Engine')">
                {{ vehicle.engine.name }}
            </InformationRow>

            <InformationRow v-if="vehicle.kilometers" :title="__('Kilometers')">
                {{ vehicle.kilometers }}
            </InformationRow>

            <InformationRow
                v-if="vehicle.transmission"
                :title="__('Transmission')"
            >
                {{
                    replaceEnumUnderscores(
                        findEnumKeyByValue(Transmission, vehicle.transmission)
                    )
                }}
            </InformationRow>

            <InformationRow
                v-if="vehicle.kw || vehicle.hp"
                :title="__('Power kW / HP')"
            >
                {{ vehicle.kw }} / {{ vehicle.hp }}
            </InformationRow>

            <InformationRow v-if="vehicle.color_type" :title="__('Color')">
                {{
                    replaceEnumUnderscores(
                        findEnumKeyByValue(ColorType, vehicle.color_type)
                    )
                }}
            </InformationRow>

            <InformationRow v-if="deliveryWeek" :title="__('Delivery week')">
                <WeekRangePicker
                    :model-value="deliveryWeek"
                    disabled
                    :placeholder="__('Delivery Week')"
                    name="delivery_week"
                />
            </InformationRow>

            <InformationRow
                v-if="
                    quote.calculation_on_quote &&
                    allItemsAndAdditionals &&
                    vehicle.calculation.sales_price_net
                "
                :title="__('Sales price net')"
            >
                {{ vehicle.calculation.sales_price_net }}
            </InformationRow>

            <InformationRow
                v-if="
                    allItemsAndAdditionals &&
                    salesPriceServiceItemsPerVehicleUnits
                "
                :title="__('Services & products')"
            >
                {{
                    convertUnitsToCurrency(
                        salesPriceServiceItemsPerVehicleUnits
                    )
                }}
            </InformationRow>

            <InformationRow
                v-if="quote.discount && quote.discount_in_output"
                :title="__('Discount')"
            >
                {{ quote.discount }}
            </InformationRow>

            <InformationRow
                v-if="
                    (quote.calculation_on_quote || !quote.is_brutto) &&
                    calculatedPrices.priceNet
                "
                :title="__('Price net')"
            >
                {{ calculatedPrices.priceNet }}
            </InformationRow>

            <InformationRow
                v-if="
                    quote.is_brutto &&
                    quote.calculation_on_quote &&
                    calculatedPrices.vat
                "
                :title="`VAT (${vehicle.calculation.vat_percentage}%)`"
            >
                {{ calculatedPrices.vat }}
            </InformationRow>

            <InformationRow
                v-if="
                    quote.is_brutto &&
                    quote.calculation_on_quote &&
                    vehicle.calculation.rest_bpm_indication
                "
                :title="__('BPM')"
            >
                {{ vehicle.calculation.rest_bpm_indication }}
            </InformationRow>

            <InformationRow
                v-if="
                    quote.is_brutto &&
                    quote.calculation_on_quote &&
                    vehicle.calculation.leges_vat
                "
                :title="__('Reg fees (0%)')"
            >
                {{ vehicle.calculation.leges_vat }}
            </InformationRow>

            <InformationRow
                v-if="quote.is_brutto && calculatedPrices.priceBrutto"
                :title="__('Price brutto (in/in)')"
            >
                {{ calculatedPrices.priceBrutto }}
            </InformationRow>

            <div
                v-if="$can('edit-vehicle')"
                class="px-4 py-3 flex items-center justify-between"
            >
                <div class="text-[#6D6D73]">
                    {{ __("Vehicle") }}
                </div>

                <Link
                    :href="route('vehicles.edit', vehicle.id)"
                    class="border border-[#008FE3] text-[#008FE3] px-3 py-0.5 rounded-md hover:opacity-60 active:scale-95 transition"
                >
                    {{ __("Go to") }}
                </Link>
            </div>
        </Section>
    </div>
</template>
