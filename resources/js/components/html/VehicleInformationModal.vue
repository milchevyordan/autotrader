<script setup lang="ts">
import InformationRow from "@/components/html/InformationRow.vue";
import Modal from "@/components/Modal.vue";
import { Currency } from "@/enums/Currency";
import { FuelType } from "@/enums/FuelType";
import { Vehicle } from "@/types";
import {
    booleanRepresentation,
    dateTimeToLocaleString,
    findEnumKeyByValue,
    replaceEnumUnderscores,
} from "@/utils";

const emit = defineEmits(["close-modal"]);

defineProps<{
    showVehicleInformationModal: boolean;
    vehicle?: Vehicle;
}>();
</script>

<template>
    <Modal
        max-width="lg"
        :show="showVehicleInformationModal"
        @close="emit('close-modal')"
    >
        <div v-if="vehicle">
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{ __("Vehicle #") + vehicle.id + __(" information") }}
            </div>

            <InformationRow :title="__('Created')">
                {{ dateTimeToLocaleString(vehicle.created_at) }}
            </InformationRow>

            <InformationRow :title="__('Fuel type')">
                {{
                    replaceEnumUnderscores(
                        findEnumKeyByValue(FuelType, vehicle.fuel)
                    )
                }}
            </InformationRow>

            <InformationRow v-if="vehicle.co2_wltp" title="CO2 WLTP">
                {{ vehicle.co2_wltp }}
            </InformationRow>

            <InformationRow v-if="vehicle.co2_nedc" title="CO2 NEDC">
                {{ vehicle.co2_nedc }}
            </InformationRow>

            <InformationRow
                v-if="
                    vehicle.calculation.selling_price_supplier ||
                    vehicle.calculation.original_currency
                "
                :title="__('Selling Price Supplier')"
            >
                {{ vehicle.calculation.selling_price_supplier }}
                {{
                    replaceEnumUnderscores(
                        findEnumKeyByValue(
                            Currency,
                            vehicle.calculation.original_currency
                        )
                    )
                }}
            </InformationRow>

            <InformationRow
                v-if="vehicle.calculation.vat_percentage"
                :title="__('VAT% Supplier')"
            >
                {{ vehicle.calculation.vat_percentage }}%
            </InformationRow>

            <InformationRow
                v-if="vehicle.calculation.net_purchase_price"
                :title="__('Purchase price')"
            >
                {{ !vehicle.calculation.is_vat ? __("Margin") : __("Net") }}
                {{ vehicle.calculation.net_purchase_price }}
            </InformationRow>

            <InformationRow :title="__('Intermediate')">
                {{ booleanRepresentation(vehicle.calculation.intermediate) }}
            </InformationRow>

            <InformationRow
                v-if="vehicle.calculation.intermediate"
                :title="__('Fee intermediate')"
            >
                {{ vehicle.calculation.fee_intermediate }}
            </InformationRow>

            <InformationRow
                v-if="vehicle.calculation.total_purchase_price"
                :title="__('Total purchase price')"
            >
                {{ !vehicle.calculation.is_vat ? __("Margin") : __("Net") }}
                {{ vehicle.calculation.total_purchase_price }}
            </InformationRow>

            <InformationRow
                v-if="vehicle.calculation.costs_of_damages"
                :title="__('Costs of damages')"
            >
                {{ vehicle.calculation.costs_of_damages }}
            </InformationRow>

            <InformationRow
                v-if="vehicle.calculation.transport_inbound"
                :title="__('Transport inbound')"
            >
                {{ vehicle.calculation.transport_inbound }}
            </InformationRow>

            <InformationRow
                v-if="vehicle.calculation.transport_outbound"
                :title="__('Transport outbound')"
            >
                {{ vehicle.calculation.transport_outbound }}
            </InformationRow>

            <InformationRow
                v-if="vehicle.calculation.costs_of_taxation"
                :title="__('Costs of taxation for BPM')"
            >
                {{ vehicle.calculation.costs_of_taxation }}
            </InformationRow>

            <InformationRow
                v-if="vehicle.calculation.recycling_fee"
                :title="__('Recycling fee')"
            >
                {{ vehicle.calculation.recycling_fee }}
            </InformationRow>

            <InformationRow
                v-if="vehicle.calculation.sales_margin"
                :title="__('Sales Margin')"
            >
                {{ vehicle.calculation.sales_margin }}
            </InformationRow>

            <InformationRow
                v-if="vehicle.calculation.total_costs_with_fee"
                :title="__('Total costs and fee')"
            >
                {{ vehicle.calculation.total_costs_with_fee }}
            </InformationRow>

            <InformationRow
                v-if="vehicle.calculation.sales_price_net"
                :title="__('Sales price net')"
            >
                ({{ !vehicle.calculation.is_vat ? __("margin") : __("ex/ex") }})
                {{ vehicle.calculation.sales_price_net }}
            </InformationRow>

            <InformationRow v-if="vehicle.calculation.vat" :title="__('VAT')">
                <span v-if="vehicle.calculation.vat_percentage">
                    ({{ vehicle.calculation.vat_percentage }}%)
                </span>

                <span v-if="!vehicle.calculation.is_vat">
                    {{ __("on Costs and Fee") }}</span
                >
                {{ vehicle.calculation.vat }}
            </InformationRow>

            <InformationRow
                v-if="vehicle.calculation.sales_price_incl_vat_or_margin"
                :title="__('Sales price')"
            >
                <span v-if="vehicle.calculation.is_vat">{{
                    __("including VAT")
                }}</span>
                <span v-else>{{ __("margin") }}</span>
                {{ vehicle.calculation.sales_price_incl_vat_or_margin }}
            </InformationRow>

            <InformationRow
                v-if="vehicle.calculation.rest_bpm_indication"
                :title="__('Rest BPM (indication)')"
            >
                {{ vehicle.calculation.rest_bpm_indication }}
            </InformationRow>

            <InformationRow
                v-if="vehicle.calculation.leges_vat"
                :title="__('Leges (VAT)')"
            >
                {{ vehicle.calculation.leges_vat }}
            </InformationRow>

            <InformationRow
                v-if="vehicle.calculation.sales_price_total"
                :title="__('Sales price total')"
            >
                ({{ !vehicle.calculation.is_vat ? __("margin") : __("in/in") }})
                {{ vehicle.calculation.sales_price_total }}
            </InformationRow>

            <InformationRow
                v-if="vehicle.calculation.gross_bpm"
                :title="__('Gross BPM (indication)')"
            >
                {{ vehicle.calculation.gross_bpm }}
            </InformationRow>

            <InformationRow
                v-if="vehicle.calculation.bpm_percent"
                :title="__('Depreciation Percentage BPM')"
            >
                {{ vehicle.calculation.bpm_percent }}
            </InformationRow>

            <InformationRow
                v-if="vehicle.calculation.bpm"
                :title="__('Rest BPM (indication)')"
            >
                {{ vehicle.calculation.bpm }}
            </InformationRow>

            <div class="col-span-2 flex justify-end gap-3 mt-2 pt-1 pb-3 px-4">
                <button
                    class="bg-[#F0F0F0] px-12 py-2 rounded hover:opacity-80 active:scale-95 transition"
                    @click="emit('close-modal')"
                >
                    {{ __("Close") }}
                </button>
            </div>
        </div>
    </Modal>
</template>
