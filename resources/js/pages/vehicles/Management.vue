<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";

import Header from "@/components/Header.vue";
import TabBox from "@/components/html/TabBox.vue";
import Modal from "@/components/Modal.vue";
import { limitCharacters } from "@/data-table/js/utils";
import DataTableTable from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import { ExteriorColour } from "@/enums/ExteriorColour";
import { Transmission } from "@/enums/Transmission";
import { TransportOrderStatus } from "@/enums/TransportOrderStatus";
import { TransportType } from "@/enums/TransportType";
import { VehicleType } from "@/enums/VehicleType";
import IconDocument from "@/icons/Document.vue";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import {
    DocumentLine,
    ServiceVehicle,
    TransportOrder,
    Vehicle,
    WorkflowFinishedStep,
} from "@/types";
import {
    camelToKebab,
    dateTimeToLocaleString,
    dateToLocaleString,
    findCreatedAtStatusDate,
    findEnumKeyByValue,
    replaceEnumUnderscores,
} from "@/utils";

defineProps<{
    dataTable: DataTable<Vehicle>;
    tabs: string[];
    method: string;
}>();

const toggleTab = async (tab: string) => {
    await new Promise((resolve, reject) => {
        router.visit(route("vehicles.management", camelToKebab(tab)), {
            only: ["dataTable", "method"],
            preserveScroll: true,
            onSuccess: resolve,
            onError: reject,
        });
    });
};

const showTransportOrdersModal = ref(false);
const transportOrders = ref<TransportOrder[]>(null!);

const openTransportOrdersModal = (items: TransportOrder[]) => {
    transportOrders.value = items;
    showTransportOrdersModal.value = true;
};

const closeTransportOrdersModal = () => {
    showTransportOrdersModal.value = false;
    transportOrders.value = null!;
};

const showOptionsModal = ref(false);
const options = ref<string>(null!);

const openOptionsModal = (item: string) => {
    options.value = item;
    showOptionsModal.value = true;
};

const closeOptionsModal = () => {
    showOptionsModal.value = false;
    options.value = null!;
};

// const findStep = (
//     item: Vehicle | ServiceVehicle,
//     variableMapName: string,
//     field: "created_at" | "additional_value"
// ) => {
//     return (
//         item.workflow?.finished_steps_management?.find(
//             (step: WorkflowFinishedStep) =>
//                 step.step.variable_map_name == variableMapName
//         )?.[field] ?? null
//     );
// };
</script>

<template>
    <Head :title="__('Management')" />

    <AppLayout>
        <Header :text="__('Management')" />

        <div
            class="rounded-lg border border-[#E9E7E7] shadow-sm bg-white my-2 sm:py-2 px-4 mt-1"
        >
            <ul
                class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500"
            >
                <TabBox
                    v-for="(tab, index) in tabs"
                    :key="index"
                    :tab="tab"
                    :method="method"
                    @click="toggleTab"
                />
            </ul>
        </div>

        <div
            class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-1"
        >
            <DataTableTable
                :data-table="dataTable"
                :per-page-options="[5, 10, 15, 20, 50]"
                :global-search="true"
            >
                <template #cell(transmission)="{ value, item }">
                    {{
                        replaceEnumUnderscores(
                            findEnumKeyByValue(Transmission, value)
                        )
                    }}
                </template>

                <template #cell(type)="{ value, item }">
                    {{
                        replaceEnumUnderscores(
                            findEnumKeyByValue(VehicleType, value)
                        )
                    }}
                </template>

                <template #cell(specific_exterior_color)="{ value, item }">
                    {{ findEnumKeyByValue(ExteriorColour, value) }}
                </template>

                <template
                    #cell(salesOrder.customerCompany.name)="{ value, item }"
                >
                    {{ item.sales_order[0]?.customer_company?.name }}
                </template>

                <template #cell(number_of_vehicles)="{ value, item }">
                    1
                </template>

                <template #cell(workflow.examination)="{ value, item }">
                    <span
                        v-if="
                            item.workflow?.process?.name &&
                            item.workflow.process.name.includes('Import')
                        "
                    >
                        <!-- {{
                            dateToLocaleString(
                                findStep(
                                    item,
                                    "hasPassedSampleInspection",
                                    "additional_value"
                                )
                            )
                        }} -->
                    </span>
                    <span v-else>
                        {{ __("No examination") }}
                    </span>
                </template>

                <template #cell(documents.number)="{ value, item }">
                    <div class="flex gap-1.5">
                        <span
                            v-for="(document, index) in item.in_output"
                            :key="index"
                        >
                            {{ document.number }}
                        </span>
                    </div>
                </template>

                <template #cell(transportOrders)="{ value, item }">
                    <button
                        v-if="item.transport_orders.length"
                        class="text-sm font-medium me-2 px-2.5 py-0.5 rounded cursor-pointer bg-gray-100 text-gray-800"
                        @click="openTransportOrdersModal(item.transport_orders)"
                    >
                        {{ item.transport_orders.length }}
                    </button>
                </template>

                <template
                    #cell(transportOrderInbound.deliveryLocation.address)="{
                        value,
                        item,
                    }"
                >
                    <div class="flex gap-1.5">
                        {{
                            item.transport_order_inbound[0]?.delivery_location
                                ?.address
                        }}
                    </div>
                </template>

                <template
                    #cell(transportOrderInbound.statuses)="{ value, item }"
                >
                    <div class="flex gap-1.5">
                        {{
                            item.transport_order_inbound[0]?.statuses
                                ? findCreatedAtStatusDate(
                                      item.transport_order_inbound[0].statuses,
                                      TransportOrderStatus.Cmr_waybill
                                  )
                                : ""
                        }}
                    </div>
                </template>

                <template
                    #cell(transport_order_inbound.updated_at)="{ value, item }"
                >
                    <div class="flex gap-1.5">
                        {{
                            item.transport_order_inbound[0]?.updated_at
                                ? dateTimeToLocaleString(
                                      item.transport_order_inbound[0]
                                          ?.updated_at
                                  )
                                : ""
                        }}
                    </div>
                </template>

                <template #cell(workflow.process.name)="{ value, item }">
                    <div class="flex gap-1.5">
                        {{ item.workflow?.process.name }}
                    </div>
                </template>

                <template
                    #cell(workflow.hasPassedSampleInspection)="{ value, item }"
                >
                    <div class="flex gap-1.5">
                        <!-- {{
                            dateToLocaleString(
                                findStep(
                                    item,
                                    "hasPassedSampleInspection",
                                    "additional_value"
                                )
                            )
                        }} -->
                    </div>
                </template>

                <template
                    #cell(workflow.hasUploadedBpmDeclaration)="{ value, item }"
                >
                    <div class="flex gap-1.5">
                        <!-- {{
                            dateTimeToLocaleString(
                                findStep(
                                    item,
                                    "hasUploadedBpmDeclaration",
                                    "created_at"
                                )
                            )
                        }} -->
                    </div>
                </template>

                <template
                    #cell(workflow.hasReceivedOriginalDocuments)="{
                        value,
                        item,
                    }"
                >
                    <div class="flex gap-1.5">
                        <!-- {{
                            dateTimeToLocaleString(
                                findStep(
                                    item,
                                    "hasReceivedOriginalDocuments",
                                    "created_at"
                                )
                            )
                        }} -->
                    </div>
                </template>

                <template #cell(workflow.hasSentBpmInvoice)="{ value, item }">
                    <div class="flex gap-1.5">
                        <!-- {{
                            dateTimeToLocaleString(
                                findStep(
                                    item,
                                    "hasSentBpmInvoice",
                                    "additional_value"
                                )
                            )
                        }} -->
                    </div>
                </template>

                <template
                    #cell(workflow.hasUploadedVehicleIntakeForm)="{
                        value,
                        item,
                    }"
                >
                    <div class="flex gap-1.5">
                        <!-- {{
                            findStep(
                                item,
                                "hasUploadedVehicleIntakeForm",
                                "additional_value"
                            )
                                ? __("Document uploaded")
                                : __("Vehicle Received")
                        }} -->
                    </div>
                </template>

                <template
                    #cell(purchaseOrder.purchaser.full_name)="{ value, item }"
                >
                    <div class="flex gap-1.5">
                        {{ item.purchase_order[0]?.purchaser.full_name }}
                    </div>
                </template>

                <template #cell(option)="{ value, item }">
                    <div
                        class="flex gap-1.5 cursor-pointer"
                        @click="openOptionsModal(value)"
                    >
                        {{ limitCharacters(value, 35) }}
                    </div>
                </template>

                <template #cell(document_lines)="{ value, item }">
                    <div class="flex gap-1.5">
                        {{
                            value.find(
                                (document_line: DocumentLine) =>
                                    document_line.documentable_id === item.id
                            )?.price_include_vat
                        }}
                    </div>
                </template>

                <template #cell(document_lines_after_approve)="{ value, item }">
                    <div class="flex gap-1.5">
                        {{
                            item.document_lines.find(
                                (document_line: DocumentLine) =>
                                    document_line.documentable_id === item.id
                            )?.price_include_vat
                        }}
                    </div>
                </template>

                <template #cell(inspection_photos)="{ value, item }">
                    <div class="flex gap-1.5">
                        {{ __("Not available") }}
                    </div>
                </template>

                <template #cell(deel_one)="{ value, item }">
                    <div class="flex gap-1.5">
                        {{ __("Not available") }}
                    </div>
                </template>

                <template #cell(workflow.documentsInbound)="{ value, item }">
                    <div class="flex gap-1.5">
                        <!-- <span
                            v-if="
                                findStep(
                                    item,
                                    'hasReceivedOriginalDocuments',
                                    'additional_value'
                                )
                            "
                            class="border border-[#E9E7E7] rounded-md py-1 px-2"
                        >
                            {{ __("Original") }}
                        </span>

                        <span
                            v-if="
                                findStep(
                                    item,
                                    'hasReceivedForeignVehicleDocuments',
                                    'additional_value'
                                )
                            "
                            class="border border-[#E9E7E7] rounded-md py-1 px-2"
                        >
                            {{ __("Foreign") }}
                        </span>

                        <span
                            v-if="
                                findStep(
                                    item,
                                    'hasReceivedNlVehicleDocuments',
                                    'additional_value'
                                )
                            "
                            class="border border-[#E9E7E7] rounded-md py-1 px-2"
                        >
                            {{ __("NL") }}
                        </span> -->
                    </div>
                </template>

                <template #cell(action)="{ value, item }">
                    <div class="flex gap-1.5">
                        <Link
                            v-if="$can('edit-vehicle')"
                            class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                            :href="route('vehicles.edit', item.id)"
                        >
                            <IconPencilSquare
                                classes="w-4 h-4 text-[#909090]"
                            />
                        </Link>

                        <Link
                            v-if="$can('view-workflow') && item.workflow"
                            :href="route('workflows.show', item.workflow.id)"
                            class="border border-[#008FE3] bg-[#008FE3] text-white rounded-md p-1 active:scale-90 transition"
                        >
                            <IconDocument classes="w-4 h-4" />
                        </Link>
                    </div>
                </template>
            </DataTableTable>
        </div>

        <Modal
            :show="showTransportOrdersModal"
            @close="closeTransportOrdersModal"
        >
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{ __("Transport Orders") }}
            </div>

            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs uppercase text-black bg-[#F0F0F0]">
                    <tr>
                        <th class="px-6 py-3 border-r">
                            {{ __("Action") }}
                        </th>
                        <th class="px-6 py-3 border-r">#</th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Status") }}
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Transport Type") }}
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Date") }}
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="transportOrder in transportOrders"
                        v-if="transportOrders.length > 0"
                        :key="transportOrder.id"
                        class="bg-white border-b border-[#E9E7E7]"
                    >
                        <td class="whitespace-nowrap px-6 py-2">
                            <div class="flex gap-1.5">
                                <Link
                                    v-if="$can('edit-transport-order')"
                                    class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                    :href="
                                        route(
                                            'transport-orders.edit',
                                            transportOrder.id
                                        )
                                    "
                                >
                                    <IconPencilSquare
                                        classes="w-4 h-4 text-[#909090]"
                                    />
                                </Link>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-2">
                            {{ transportOrder.id }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-2">
                            {{
                                replaceEnumUnderscores(
                                    findEnumKeyByValue(
                                        TransportOrderStatus,
                                        transportOrder.status
                                    )
                                )
                            }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-2">
                            {{
                                findEnumKeyByValue(
                                    TransportType,
                                    transportOrder.transport_type
                                )
                            }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-2">
                            {{
                                dateTimeToLocaleString(
                                    transportOrder.created_at
                                )
                            }}
                        </td>
                    </tr>

                    <tr v-else>
                        <td
                            class="bg-white text-center py-5 text-lg font-semibold border-b border-[#E9E7E7]"
                            colspan="5"
                        >
                            {{ __("No found data") }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="col-span-2 flex justify-end gap-3 mt-2 pt-1 pb-3 px-4">
                <button
                    class="bg-[#F0F0F0] px-12 py-2 rounded hover:opacity-80 active:scale-95 transition"
                    @click="closeTransportOrdersModal"
                >
                    {{ __("Close") }}
                </button>
            </div>
        </Modal>

        <Modal :show="showOptionsModal" @close="closeOptionsModal">
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{ __("Options") }}
            </div>

            <div class="p-3.5">
                {{ options }}
            </div>

            <div class="col-span-2 flex justify-end gap-3 mt-2 pt-1 pb-3 px-4">
                <button
                    class="bg-[#F0F0F0] px-12 py-2 rounded hover:opacity-80 active:scale-95 transition"
                    @click="closeOptionsModal"
                >
                    {{ __("Close") }}
                </button>
            </div>
        </Modal>
    </AppLayout>
</template>
