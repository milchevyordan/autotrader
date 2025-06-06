<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3";
import { ref } from "vue";

import Header from "@/components/Header.vue";
import DataTableGroup from "@/components/html/DataTableGroup.vue";
import { DataTable } from "@/data-table/types";
import AppLayout from "@/layouts/AppLayout.vue";
import {
    Document,
    PreOrder,
    PreOrderVehicle,
    PurchaseOrder,
    Quote,
    SalesOrder,
    ServiceOrder,
    TransportOrder,
    Vehicle,
    WorkOrder,
} from "@/types";

defineProps<{
    resourcesCount: Record<string, number>;
    preOrders?: DataTable<PreOrder>;
    purchaseOrders?: DataTable<PurchaseOrder>;
    salesOrders?: DataTable<SalesOrder>;
    serviceOrders?: DataTable<ServiceOrder>;
    workOrders?: DataTable<WorkOrder>;
    documents?: DataTable<Document>;
    quotes?: DataTable<Quote>;
    vehicles?: DataTable<Vehicle>;
    preOrderVehicles?: DataTable<PreOrderVehicle>;
    transportOrders?: DataTable<TransportOrder>;
}>();

const expandedTables = ref<Record<string, boolean>>({
    preOrders: false,
    purchaseOrders: false,
    salesOrders: false,
    serviceOrders: false,
    workOrders: false,
    documents: false,
    quotes: false,
    vehicles: false,
    preOrderVehicles: false,
    transportOrders: false,
});

const toggleExpanded = async (resource: string) => {
    if (expandedTables.value[resource]) {
        expandedTables.value[resource] = false;
        return;
    }

    Object.keys(expandedTables.value).forEach((key) => {
        expandedTables.value[key] = false;
    });

    window.history.replaceState({}, document.title, window.location.pathname);

    await new Promise((resolve, reject) => {
        router.reload({
            only: [resource],
            onSuccess: resolve,
            onError: reject,
        });
    });

    expandedTables.value[resource] = true;
};
</script>

<template>
    <Head :title="__('Crm User Profile')" />
    <AppLayout>
        <Header :text="__('Crm User Profile')" />

        <div class="d-flex">
            <div
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-1 justify-evenly xl:block gap-4 xl:space-y-4"
            >
                <DataTableGroup
                    :data-table="preOrders"
                    :resource-count="resourcesCount.pre_orders_count"
                    :title="__('Pre Orders')"
                    :expanded="expandedTables['preOrders']"
                    resource="preOrders"
                    @toggle-expanded="toggleExpanded"
                />

                <DataTableGroup
                    :data-table="purchaseOrders"
                    :resource-count="resourcesCount.purchase_orders_count"
                    :title="__('Purchase Orders')"
                    :expanded="expandedTables['purchaseOrders']"
                    resource="purchaseOrders"
                    @toggle-expanded="toggleExpanded"
                />

                <DataTableGroup
                    :data-table="salesOrders"
                    :resource-count="resourcesCount.sales_orders_count"
                    :title="__('Sales Orders')"
                    :expanded="expandedTables['salesOrders']"
                    resource="salesOrders"
                    @toggle-expanded="toggleExpanded"
                />

                <DataTableGroup
                    :data-table="serviceOrders"
                    :resource-count="resourcesCount.service_orders_count"
                    :title="__('Service Orders')"
                    :expanded="expandedTables['serviceOrders']"
                    resource="serviceOrders"
                    @toggle-expanded="toggleExpanded"
                />

                <DataTableGroup
                    :data-table="workOrders"
                    :resource-count="resourcesCount.work_orders_count"
                    :title="__('Work Orders')"
                    :expanded="expandedTables['workOrders']"
                    resource="workOrders"
                    @toggle-expanded="toggleExpanded"
                />

                <DataTableGroup
                    :data-table="transportOrders"
                    :resource-count="resourcesCount.transport_orders_count"
                    :title="__('Transport Orders')"
                    :expanded="expandedTables['transportOrders']"
                    resource="transportOrders"
                    @toggle-expanded="toggleExpanded"
                />

                <DataTableGroup
                    :data-table="documents"
                    :resource-count="resourcesCount.documents_count"
                    :title="__('Documents')"
                    :expanded="expandedTables['documents']"
                    resource="documents"
                    @toggle-expanded="toggleExpanded"
                />

                <DataTableGroup
                    :data-table="vehicles"
                    :resource-count="resourcesCount.vehicles_count"
                    :title="__('Vehicles')"
                    :expanded="expandedTables['vehicles']"
                    resource="vehicles"
                    @toggle-expanded="toggleExpanded"
                />

                <DataTableGroup
                    :data-table="preOrderVehicles"
                    :resource-count="resourcesCount.pre_order_vehicles_count"
                    :title="__('Pre Order Vehicles')"
                    :expanded="expandedTables['preOrderVehicles']"
                    resource="preOrderVehicles"
                    @toggle-expanded="toggleExpanded"
                />

                <DataTableGroup
                    :data-table="quotes"
                    :resource-count="resourcesCount.quotes_count"
                    :title="__('Quotes')"
                    :expanded="expandedTables['quotes']"
                    resource="quotes"
                    @toggle-expanded="toggleExpanded"
                />
            </div>
        </div>
    </AppLayout>
</template>
