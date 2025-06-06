<script setup lang="ts">
import Section from "@/components/html/Section.vue";
import PurchaseOrderSummary from "@/components/purchase-orders/Summary.vue";
import QuoteSummary from "@/components/quotes/Summary.vue";
import SalesOrderSummary from "@/components/sales-orders/Summary.vue";
import ServiceOrderSummary from "@/components/service-orders/Summary.vue";
import TransportOrderSummary from "@/components/transport-orders/Summary.vue";
import WorkOrderSummary from "@/components/work-orders/Summary.vue";
import DocumentSummary from "@/pages/documents/partials/Summary.vue";
import {
    Document,
    PurchaseOrder,
    Quote,
    SalesOrder,
    ServiceOrder,
    TransportOrder,
    WorkOrder,
} from "@/types";

defineProps<{
    purchaseOrder?: PurchaseOrder[];
    salesOrder?: SalesOrder[];
    serviceOrder?: ServiceOrder;
    transportOrders?: TransportOrder[];
    workOrder?: WorkOrder;
    documents?: Document[];
    quotes?: Quote[];
}>();
</script>

<template>
    <Section classes="mt-4">
        <div class="font-semibold text-xl sm:text-2xl p-4">
            {{ __("Modules") }}
        </div>

        <div
            class="information-container flex flex-wrap justify-around my-2 p-4 gap-4 rounded-md bg-white"
        >
            <div
                v-for="order in purchaseOrder"
                v-if="purchaseOrder?.length"
                :key="order.id"
                class="bg-white border border-[#E9E7E7] rounded-lg h-fit shadow-lg"
            >
                <PurchaseOrderSummary :resource="order" />
            </div>

            <div
                v-for="order in salesOrder"
                v-if="salesOrder?.length"
                :key="order.id"
                class="bg-white border border-[#E9E7E7] rounded-lg h-fit shadow-lg"
            >
                <SalesOrderSummary :resource="order" />
            </div>

            <div
                v-if="serviceOrder"
                class="bg-white border border-[#E9E7E7] rounded-lg h-fit shadow-lg"
            >
                <ServiceOrderSummary :resource="serviceOrder" />
            </div>

            <div
                v-if="workOrder"
                class="bg-white border border-[#E9E7E7] rounded-lg h-fit shadow-lg"
            >
                <WorkOrderSummary :resource="workOrder" />
            </div>

            <div
                v-for="transportOrder in transportOrders"
                v-if="transportOrders?.length"
                :key="transportOrder.id"
                class="bg-white border border-[#E9E7E7] rounded-lg h-fit shadow-lg"
            >
                <TransportOrderSummary :resource="transportOrder" />
            </div>

            <div
                v-for="document in documents"
                v-if="documents?.length"
                :key="document.id"
                class="bg-white border border-[#E9E7E7] rounded-lg h-fit shadow-lg"
            >
                <DocumentSummary :resource="document" />
            </div>

            <div
                v-for="quote in quotes"
                v-if="quotes?.length"
                :key="quote.id"
                class="bg-white border border-[#E9E7E7] rounded-lg h-fit shadow-lg"
            >
                <QuoteSummary :resource="quote" />
            </div>
        </div>
    </Section>
</template>
