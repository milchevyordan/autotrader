<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";

import Header from "@/components/Header.vue";
import Modal from "@/components/Modal.vue";
import Table from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import { DocumentStatus } from "@/enums/DocumentStatus";
import { PurchaseOrderStatus } from "@/enums/PurchaseOrderStatus";
import { QuoteStatus } from "@/enums/QuoteStatus";
import { SalesOrderStatus } from "@/enums/SalesOrderStatus";
import { TransportOrderStatus } from "@/enums/TransportOrderStatus";
import CheckIcon from "@/icons/Check.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import {
    Enum,
    Document,
    PurchaseOrder,
    Quote,
    SalesOrder,
    TransportOrder,
    Vehicle,
} from "@/types";
import {
    dateTimeToLocaleString,
    findCreatedAtStatusDate,
    findEnumKeyByValue,
    replaceEnumUnderscores,
} from "@/utils";

defineProps<{
    dataTable: DataTable<Vehicle>;
    title: string;
}>();

const getStatusColorClassesByTaskCompletion = (
    statusEnum: any,
    currentStatus: number
) => {
    const totalSteps = statusEnum[Object.keys(statusEnum).pop()!];
    const percentageFinished = (currentStatus / totalSteps) * 100;
    let classes;
    switch (true) {
        case percentageFinished < 51:
            classes = "bg-red-100 text-red-800";
            break;
        case percentageFinished < 100:
            classes = "bg-yellow-100 text-yellow-800";
            break;
        default:
            classes = "bg-green-100 text-green-800";
            break;
    }

    return classes;
};

const showPurchaseOrderModal = ref(false);
const purchaseOrder = ref<PurchaseOrder>(null!);

const openPurchaseOrderModal = (item: PurchaseOrder) => {
    purchaseOrder.value = item;
    showPurchaseOrderModal.value = true;
};

const convertStatusEnum = (status: Enum<any>) => {
    return Object.entries(status)
        .filter(([name]) => isNaN(Number(name)))
        .map(([name, value]) => ({
            name,
            value: Number(value),
        }));
};

const closePurchaseOrderModal = () => {
    showPurchaseOrderModal.value = false;
    purchaseOrder.value = null!;
};

const purchaseOrderStatuses = convertStatusEnum(PurchaseOrderStatus);

const showSalesOrderModal = ref(false);
const salesOrder = ref<SalesOrder>(null!);

const openSalesOrderModal = (item: SalesOrder) => {
    salesOrder.value = item;
    showSalesOrderModal.value = true;
};

const closeSalesOrderModal = () => {
    showSalesOrderModal.value = false;
    salesOrder.value = null!;
};

const salesOrderStatuses = convertStatusEnum(SalesOrderStatus);

const showTransportOrderModal = ref(false);
const transportOrder = ref<TransportOrder>(null!);

const openTransportOrderModal = (item: TransportOrder) => {
    transportOrder.value = item;
    showTransportOrderModal.value = true;
};

const closeTransportOrderModal = () => {
    showTransportOrderModal.value = false;
    transportOrder.value = null!;
};

const transportOrderStatuses = convertStatusEnum(TransportOrderStatus);

const showDocumentModal = ref(false);
const document = ref<Document>(null!);

const openDocumentModal = (item: Document) => {
    document.value = item;
    showDocumentModal.value = true;
};

const closeDocumentModal = () => {
    showDocumentModal.value = false;
    document.value = null!;
};

const documentStatuses = convertStatusEnum(DocumentStatus);

const showQuoteModal = ref(false);
const quotes = ref<Quote[]>(null!);

const openQuoteModal = (items: Quote[]) => {
    quotes.value = items;
    showQuoteModal.value = true;
};

const closeQuoteModal = () => {
    showQuoteModal.value = false;
    quotes.value = null!;
};

const quoteStatuses = convertStatusEnum(QuoteStatus);
</script>

<template>
    <Head :title="title" />

    <AppLayout>
        <Header :text="title" />

        <Table
            :data-table="dataTable"
            :per-page-options="[5, 10, 15, 20, 50]"
            :global-search="true"
            :row-click-link="$can('edit-vehicle') ? '/vehicles/?id/edit' : ''"
        >
            <template #cell(vehicleModel.name)="{ value, item }">
                {{ item.vehicle_model?.name }}
            </template>

            <template #cell(created_at)="{ value, item }">
                <div class="flex gap-1.5">
                    {{ dateTimeToLocaleString(value) }}
                </div>
            </template>

            <template #cell(updated_at)="{ value, item }">
                <div class="flex gap-1.5">
                    {{ dateTimeToLocaleString(value) }}
                </div>
            </template>

            <template #cell(purchaseOrder)="{ value, item }">
                <button
                    v-if="item.purchase_order[0]"
                    :class="[
                        'text-sm',
                        'font-medium',
                        'me-2',
                        'px-2.5',
                        'py-0.5',
                        'rounded',
                        'cursor-pointer',
                        getStatusColorClassesByTaskCompletion(
                            PurchaseOrderStatus,
                            item.purchase_order[0]?.status
                        ),
                    ]"
                    @click="openPurchaseOrderModal(item.purchase_order[0])"
                >
                    {{
                        replaceEnumUnderscores(
                            findEnumKeyByValue(
                                PurchaseOrderStatus,
                                item.purchase_order[0]?.status
                            )
                        )
                    }}
                </button>
            </template>

            <template #cell(salesOrder)="{ value, item }">
                <button
                    v-if="item.sales_order[0]"
                    :class="[
                        'text-sm',
                        'font-medium',
                        'me-2',
                        'px-2.5',
                        'py-0.5',
                        'rounded',
                        'cursor-pointer',
                        getStatusColorClassesByTaskCompletion(
                            SalesOrderStatus,
                            item.sales_order[0]?.status
                        ),
                    ]"
                    @click="openSalesOrderModal(item.sales_order[0])"
                >
                    {{
                        replaceEnumUnderscores(
                            findEnumKeyByValue(
                                SalesOrderStatus,
                                item.sales_order[0]?.status
                            )
                        )
                    }}
                </button>
            </template>

            <template #cell(transportOrderInbound)="{ value, item }">
                <button
                    v-if="item.transport_order_inbound[0]"
                    :class="[
                        'text-sm',
                        'font-medium',
                        'me-2',
                        'px-2.5',
                        'py-0.5',
                        'rounded',
                        'cursor-pointer',
                        getStatusColorClassesByTaskCompletion(
                            TransportOrderStatus,
                            item.transport_order_inbound[0]?.status
                        ),
                    ]"
                    @click="
                        openTransportOrderModal(item.transport_order_inbound[0])
                    "
                >
                    {{
                        replaceEnumUnderscores(
                            findEnumKeyByValue(
                                TransportOrderStatus,
                                item.transport_order_inbound[0]?.status
                            )
                        )
                    }}
                </button>
            </template>

            <template #cell(transportOrderOutbound)="{ value, item }">
                <button
                    v-if="item.transport_order_outbound[0]"
                    :class="[
                        'text-sm',
                        'font-medium',
                        'me-2',
                        'px-2.5',
                        'py-0.5',
                        'rounded',
                        'cursor-pointer',
                        getStatusColorClassesByTaskCompletion(
                            TransportOrderStatus,
                            item.transport_order_outbound[0]?.status
                        ),
                    ]"
                    @click="
                        openTransportOrderModal(
                            item.transport_order_outbound[0]
                        )
                    "
                >
                    {{
                        replaceEnumUnderscores(
                            findEnumKeyByValue(
                                TransportOrderStatus,
                                item.transport_order_outbound[0]?.status
                            )
                        )
                    }}
                </button>
            </template>

            <template #cell(documents)="{ value, item }">
                <button
                    v-if="item.documents[0]"
                    :class="[
                        'text-sm',
                        'font-medium',
                        'me-2',
                        'px-2.5',
                        'py-0.5',
                        'rounded',
                        'cursor-pointer',
                        getStatusColorClassesByTaskCompletion(
                            DocumentStatus,
                            item.documents[0]?.status
                        ),
                    ]"
                    @click="openDocumentModal(item.documents[0])"
                >
                    {{
                        replaceEnumUnderscores(
                            findEnumKeyByValue(
                                DocumentStatus,
                                item.documents[0]?.status
                            )
                        )
                    }}
                </button>
            </template>

            <template #cell(quotes)="{ value, item }">
                <button
                    v-if="item.quotes.length"
                    class="text-sm font-medium me-2 px-2.5 py-0.5 rounded cursor-pointer bg-gray-100 text-gray-800"
                    @click="openQuoteModal(item.quotes)"
                >
                    {{ item.quotes.length }}
                </button>
            </template>
        </Table>

        <Modal
            :show="showPurchaseOrderModal"
            max-width="md"
            @close="closePurchaseOrderModal"
        >
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{ __("Purchase order #") }} {{ purchaseOrder?.id }}
            </div>

            <div class="border border-[#E9E7E7] px-3.5 p-3 d-flex w-100">
                <ul class="ml-4 text-slate-700">
                    <li
                        v-for="(status, index) in purchaseOrderStatuses"
                        v-show="
                            !(
                                (purchaseOrder.status ===
                                    PurchaseOrderStatus.Approved ||
                                    purchaseOrder.status >
                                        PurchaseOrderStatus.Rejected) &&
                                status.name === 'Rejected'
                            )
                        "
                        :key="index"
                        class="status-li-container flex gap-1"
                    >
                        <span
                            v-if="purchaseOrder.status >= status.value"
                            class="text-[#00A793]"
                        >
                            <CheckIcon />
                        </span>

                        <span
                            v-else
                            class="text-red-600 font-semi text-xl px-2"
                        >
                            X
                        </span>

                        <span>
                            <span>{{
                                replaceEnumUnderscores(status.name)
                            }}</span>
                            <span class="text-xs pl-2 text-slate-700">
                                {{
                                    status.value == 1
                                        ? dateTimeToLocaleString(
                                              purchaseOrder.created_at
                                          )
                                        : findCreatedAtStatusDate(
                                              purchaseOrder.statuses,
                                              status.value as number
                                          )
                                }}
                            </span>
                        </span>
                    </li>
                </ul>
            </div>

            <div class="col-span-2 flex justify-end gap-3 mt-2 pt-1 pb-3 px-4">
                <Link
                    v-if="$can('edit-purchase-order')"
                    class="px-12 py-2 rounded hover:opacity-80 active:scale-95 transition bg-[#00A793] text-white"
                    :href="route('purchase-orders.edit', purchaseOrder?.id)"
                >
                    {{ __("Go To") }}
                </Link>

                <button
                    class="bg-[#F0F0F0] px-12 py-2 rounded hover:opacity-80 active:scale-95 transition"
                    @click="closePurchaseOrderModal"
                >
                    {{ __("Close") }}
                </button>
            </div>
        </Modal>

        <Modal
            :show="showSalesOrderModal"
            max-width="md"
            @close="closeSalesOrderModal"
        >
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{ __("Sales order #") }} {{ salesOrder?.id }}
            </div>

            <div class="border border-[#E9E7E7] px-3.5 p-3 d-flex w-100">
                <ul class="ml-4 text-slate-700">
                    <li
                        v-for="(status, index) in salesOrderStatuses"
                        v-show="
                            !(
                                (salesOrder.status ===
                                    SalesOrderStatus.Approved ||
                                    salesOrder.status >
                                        SalesOrderStatus.Rejected) &&
                                status.name === 'Rejected'
                            ) &&
                            !(
                                !salesOrder.down_payment &&
                                (status.name ==
                                    'Ready_for_down_payment_invoice' ||
                                    status.name ===
                                        'Down_payment_invoice_sent' ||
                                    status.name === 'Down_payment_done')
                            )
                        "
                        :key="index"
                        class="status-li-container flex gap-1"
                    >
                        <span
                            v-if="salesOrder.status >= status.value"
                            class="text-[#00A793]"
                        >
                            <CheckIcon />
                        </span>

                        <span
                            v-else
                            class="text-red-600 font-semi text-xl px-2"
                        >
                            X
                        </span>

                        <span>
                            <span>{{
                                replaceEnumUnderscores(status.name)
                            }}</span>
                            <span class="text-xs pl-2 text-slate-700">
                                {{
                                    status.value == 1
                                        ? dateTimeToLocaleString(
                                              salesOrder.created_at
                                          )
                                        : findCreatedAtStatusDate(
                                              salesOrder.statuses,
                                              status.value as number
                                          )
                                }}
                            </span>
                        </span>
                    </li>
                </ul>
            </div>

            <div class="col-span-2 flex justify-end gap-3 mt-2 pt-1 pb-3 px-4">
                <Link
                    v-if="$can('edit-sales-order')"
                    class="px-12 py-2 rounded hover:opacity-80 active:scale-95 transition bg-[#00A793] text-white"
                    :href="route('sales-orders.edit', salesOrder?.id)"
                >
                    {{ __("Go To") }}
                </Link>

                <button
                    class="bg-[#F0F0F0] px-12 py-2 rounded hover:opacity-80 active:scale-95 transition"
                    @click="closeSalesOrderModal"
                >
                    {{ __("Close") }}
                </button>
            </div>
        </Modal>

        <Modal
            :show="showTransportOrderModal"
            max-width="md"
            @close="closeTransportOrderModal"
        >
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{ __("Transport order #") }} {{ transportOrder?.id }}
            </div>

            <div class="border border-[#E9E7E7] px-3.5 p-3 d-flex w-100">
                <ul class="ml-4 text-slate-700">
                    <li
                        v-for="(status, index) in transportOrderStatuses"
                        v-show="
                            !(
                                status.value ===
                                    TransportOrderStatus.Offer_requested &&
                                !transportOrder.transport_company_use
                            )
                        "
                        :key="index"
                        class="status-li-container flex gap-1"
                    >
                        <span
                            v-if="transportOrder.status >= status.value"
                            class="text-[#00A793]"
                        >
                            <CheckIcon />
                        </span>

                        <span
                            v-else
                            class="text-red-600 font-semi text-xl px-2"
                        >
                            X
                        </span>

                        <span>
                            <span>{{
                                replaceEnumUnderscores(status.name)
                            }}</span>
                            <span class="text-xs pl-2 text-slate-700">
                                {{
                                    status.value == 1
                                        ? dateTimeToLocaleString(
                                              transportOrder.created_at
                                          )
                                        : findCreatedAtStatusDate(
                                              transportOrder.statuses,
                                              status.value as number
                                          )
                                }}
                            </span>
                        </span>
                    </li>
                </ul>
            </div>

            <div class="col-span-2 flex justify-end gap-3 mt-2 pt-1 pb-3 px-4">
                <Link
                    v-if="$can('edit-transport-order')"
                    class="px-12 py-2 rounded hover:opacity-80 active:scale-95 transition bg-[#00A793] text-white"
                    :href="route('transport-orders.edit', transportOrder?.id)"
                >
                    {{ __("Go To") }}
                </Link>

                <button
                    class="bg-[#F0F0F0] px-12 py-2 rounded hover:opacity-80 active:scale-95 transition"
                    @click="closeTransportOrderModal"
                >
                    {{ __("Close") }}
                </button>
            </div>
        </Modal>

        <Modal
            :show="showDocumentModal"
            max-width="md"
            @close="closeDocumentModal"
        >
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{ __("Document #") }} {{ document?.id }}
            </div>

            <div class="border border-[#E9E7E7] px-3.5 p-3 d-flex w-100">
                <ul class="ml-4 text-slate-700">
                    <li
                        v-for="(status, index) in documentStatuses"
                        v-show="
                            !(
                                (document.status === DocumentStatus.Approved ||
                                    document.status >
                                        DocumentStatus.Rejected) &&
                                status.name === 'Rejected'
                            )
                        "
                        :key="index"
                        class="status-li-container flex gap-1"
                    >
                        <span
                            v-if="document.status >= status.value"
                            class="text-[#00A793]"
                        >
                            <CheckIcon />
                        </span>

                        <span
                            v-else
                            class="text-red-600 font-semi text-xl px-2"
                        >
                            X
                        </span>

                        <span>
                            <span>{{
                                replaceEnumUnderscores(status.name)
                            }}</span>
                            <span class="text-xs pl-2 text-slate-700">
                                {{
                                    status.value == 1
                                        ? dateTimeToLocaleString(
                                              document.created_at
                                          )
                                        : findCreatedAtStatusDate(
                                              document.statuses,
                                              status.value as number
                                          )
                                }}
                            </span>
                        </span>
                    </li>
                </ul>
            </div>

            <div class="col-span-2 flex justify-end gap-3 mt-2 pt-1 pb-3 px-4">
                <Link
                    v-if="$can('edit-document')"
                    class="px-12 py-2 rounded hover:opacity-80 active:scale-95 transition bg-[#00A793] text-white"
                    :href="route('documents.edit', document?.id)"
                >
                    {{ __("Go To") }}
                </Link>

                <button
                    class="bg-[#F0F0F0] px-12 py-2 rounded hover:opacity-80 active:scale-95 transition"
                    @click="closeDocumentModal"
                >
                    {{ __("Close") }}
                </button>
            </div>
        </Modal>

        <Modal :show="showQuoteModal" max-width="md" @close="closeQuoteModal">
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{ __("Quotes") }}
            </div>

            <div class="border border-[#E9E7E7] px-3.5 p-3 d-flex w-100">
                <ul class="ml-4 text-slate-700">
                    <li
                        v-for="(status, index) in quoteStatuses"
                        :key="index"
                        class="status-li-container flex gap-1"
                    >
                        <span>{{ replaceEnumUnderscores(status.name) }}</span>

                        <div v-if="$can('edit-quote')" class="flex gap-1">
                            <Link
                                v-for="(quote, indexQuote) in quotes"
                                v-show="quote.status === status.value"
                                :key="indexQuote"
                                class="border border-[#E9E7E7] rounded-md px-1 active:scale-90 transition"
                                :href="route('quotes.edit', quote.id)"
                            >
                                #{{ quote.id }}
                            </Link>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="col-span-2 flex justify-end gap-3 mt-2 pt-1 pb-3 px-4">
                <button
                    class="bg-[#F0F0F0] px-12 py-2 rounded hover:opacity-80 active:scale-95 transition"
                    @click="closeQuoteModal"
                >
                    {{ __("Close") }}
                </button>
            </div>
        </Modal>
    </AppLayout>
</template>
