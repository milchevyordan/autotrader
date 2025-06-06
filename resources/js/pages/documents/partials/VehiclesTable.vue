<script setup lang="ts">
import WeekRangePicker from "@/components/html/WeekRangePicker.vue";
import VehicleLinks from "@/components/html/VehicleLinks.vue";
import { Multiselect } from "@/data-table/types";
import { ExteriorColour } from "@/enums/ExteriorColour";
import { FuelType } from "@/enums/FuelType";
import { Vehicle, WeekPicker, WorkflowProcess } from "@/types";
import {
    dateTimeToLocaleString,
    dateToLocaleString,
    findEnumKeyByValue,
    getImage,
    replaceEnumUnderscores,
} from "@/utils";

defineProps<{
    workflowProcesses?: Multiselect<WorkflowProcess>;
    tableVehicles?: Vehicle[];
    flattenedVehicles?: Record<number, WeekPicker>;
}>();
</script>

<template>
    <div class="table-container max-w-full overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs uppercase text-black bg-[#F0F0F0]">
                <tr>
                    <th class="px-6 py-3 border-r">
                        {{ __("Action") }}
                    </th>
                    <th class="px-6 py-3 border-r">
                        {{ __("#") }}
                    </th>
                    <th class="px-6 py-3 border-r">
                        {{ __("Vin") }}
                    </th>
                    <th class="px-6 py-3 border-r">
                        {{ __("Image") }}
                    </th>
                    <th class="px-6 py-3 border-r">
                        {{ __("Creator") }}
                    </th>
                    <th class="px-6 py-3 border-r">
                        {{ __("Date") }}
                    </th>
                    <th class="px-6 py-3 border-r">
                        {{ __("Updated") }}
                    </th>
                    <th class="px-6 py-3 border-r">
                        {{ __("Make") }}
                    </th>
                    <th class="px-6 py-3 border-r">
                        {{ __("Model") }}
                    </th>
                    <th class="px-6 py-3 border-r">
                        {{ __("Fuel type") }}
                    </th>
                    <th class="px-6 py-3 border-r">
                        {{ __("HP") }}
                    </th>
                    <th class="px-6 py-3 border-r">
                        {{ __("Kilometers") }}
                    </th>
                    <th class="px-6 py-3 border-r">
                        {{ __("First Reg. Date") }}
                    </th>
                    <th class="px-6 py-3 border-r">
                        {{ __("Color") }}
                    </th>
                    <th class="px-6 py-3 border-r">
                        {{ __("Supplier") }}
                    </th>
                    <th class="px-6 py-3 border-r">
                        {{ __("Purch. Price") }}
                    </th>
                    <th class="px-6 py-3 border-r">
                        {{ __("Damages") }}
                    </th>
                    <th class="px-6 py-3 border-r">
                        {{ __("Transport") }}
                    </th>
                    <th class="px-6 py-3 border-r">
                        {{ __("Margin") }}
                    </th>
                    <th class="px-6 py-3 border-r">
                        {{ __("Sales netto") }}
                    </th>
                    <th class="px-6 py-3 border-r">
                        {{ __("Discount") }}
                    </th>
                    <th class="px-6 py-3 border-r">
                        {{ __("BPM") }}
                    </th>
                    <th class="px-6 py-3 border-r">
                        {{ __("Sales brutto") }}
                    </th>
                    <th class="px-6 py-3 border-r">
                        {{ __("Available") }}
                    </th>
                    <th class="px-6 py-3 border-r">
                        {{ __("Lead time") }}
                    </th>
                </tr>
            </thead>

            <tbody>
                <tr
                    v-for="(item, rowIndex) in tableVehicles"
                    v-if="tableVehicles?.length"
                    :key="item.id"
                    class="bg-white border-b border-[#E9E7E7]"
                >
                    <td class="whitespace-nowrap px-6 py-3.5">
                        <div class="flex-col min-w-[270px]">
                            <div class="flex gap-1.5">
                                <VehicleLinks
                                    :item="item"
                                    :workflow-processes="workflowProcesses"
                                />
                            </div>

                            <WeekRangePicker
                                v-if="
                                    flattenedVehicles &&
                                    flattenedVehicles[item.id]
                                "
                                :model-value="
                                    flattenedVehicles?.[item.id] ?? null
                                "
                                name="expected_date_of_availability_from_supplier"
                                :disabled="true"
                            />
                        </div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        {{ item.id }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        {{ item.vin }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        <img
                            :src="getImage(item.image_path)"
                            alt="vehicle image"
                            class="object-contain w-auto mb-3 lg:mb-0 min-w-24 rounded"
                        />
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        {{ item.creator.full_name }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        {{ dateTimeToLocaleString(item.created_at) }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        {{ dateTimeToLocaleString(item.updated_at) }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        {{ item.make.name }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        {{ item.vehicle_model?.name }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        {{
                            replaceEnumUnderscores(
                                findEnumKeyByValue(FuelType, item.fuel)
                            )
                        }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        {{ item.hp }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        {{ item.kilometers }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        {{ dateToLocaleString(item.first_registration_date) }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        {{
                            findEnumKeyByValue(
                                ExteriorColour,
                                item.specific_exterior_color
                            )
                        }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        {{ item.supplier_company?.name }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        {{ item.calculation.total_purchase_price }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        {{ item.calculation.costs_of_damages }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        {{ item.calculation.transport_inbound }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        {{ item.calculation.sales_margin }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        {{ item.calculation.sales_price_net }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        {{ item.calculation.discount }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        {{ item.calculation.rest_bpm_indication }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        {{ item.calculation.sales_price_total }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        <div class="flex gap-1.5 min-w-[270px]">
                            <WeekRangePicker
                                :model-value="
                                    item.expected_date_of_availability_from_supplier
                                "
                                name="expected_date_of_availability_from_supplier"
                                disabled
                            />
                        </div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        <div
                            v-if="
                                item.expected_leadtime_for_delivery_from ||
                                item.expected_leadtime_for_delivery_to
                            "
                            class="flex gap-1.5"
                        >
                            <span
                                v-if="item.expected_leadtime_for_delivery_from"
                            >
                                {{ __("From") }}
                                {{ item.expected_leadtime_for_delivery_from }}
                            </span>
                            <span v-if="item.expected_leadtime_for_delivery_to">
                                {{ __("to") }}
                                {{ item.expected_leadtime_for_delivery_to }}
                            </span>
                            {{ __("weeks") }}
                        </div>
                    </td>
                </tr>

                <tr v-else>
                    <td
                        class="bg-white text-center py-5 text-lg font-semibold border-b border-[#E9E7E7]"
                        colspan="25"
                    >
                        {{ __("No found data") }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
