<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3";
import { ref } from "vue";

import Header from "@/components/Header.vue";
import DatatableGroup from "@/components/html/DataTableGroup.vue";
import { DataTable } from "@/data-table/types";
import AppLayout from "@/layouts/AppLayout.vue";
import {
    Company,
    CompanyGroup,
    Engine,
    Document,
    Item,
    Make,
    PreOrder,
    PreOrderVehicle,
    PurchaseOrder,
    Quote,
    SalesOrder,
    ServiceLevel,
    ServiceOrder,
    ServiceVehicle,
    TransportOrder,
    User,
    UserGroup,
    Variant,
    Vehicle,
    VehicleGroup,
    VehicleModel,
    WorkOrder,
} from "@/types";

defineProps<{
    user: User;
    preOrders?: DataTable<PreOrder>;
    purchaseOrders?: DataTable<PurchaseOrder>;
    salesOrders?: DataTable<SalesOrder>;
    serviceOrders?: DataTable<ServiceOrder>;
    workOrders?: DataTable<WorkOrder>;
    transportOrders?: DataTable<TransportOrder>;
    serviceLevels?: DataTable<ServiceLevel>;
    items?: DataTable<Item>;
    documents?: DataTable<Document>;
    vehicles?: DataTable<Vehicle>;
    preOrderVehicles?: DataTable<PreOrderVehicle>;
    serviceVehicles?: DataTable<ServiceVehicle>;
    quotes?: DataTable<Quote>;
    makes?: DataTable<Make>;
    vehicleModels?: DataTable<VehicleModel>;
    vehicleGroups?: DataTable<VehicleGroup>;
    engines?: DataTable<Engine>;
    variants?: DataTable<Variant>;
    companies?: DataTable<Company>;
    companyGroups?: DataTable<CompanyGroup>;
    users?: DataTable<User>;
    createdUserGroups?: DataTable<UserGroup>;
}>();

const expandedTables = ref<Record<string, boolean>>({
    preOrders: false,
    purchaseOrders: false,
    salesOrders: false,
    serviceOrders: false,
    workOrders: false,
    transportOrders: false,
    serviceLevels: false,
    items: false,
    documents: false,
    vehicles: false,
    preOrderVehicles: false,
    serviceVehicles: false,
    quotes: false,
    makes: false,
    vehicleModels: false,
    vehicleGroups: false,
    engines: false,
    variants: false,
    companies: false,
    companyGroups: false,
    users: false,
    createdUserGroups: false,
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
    <Head :title="__('User Profile')" />
    <AppLayout>
        <Header :text="__('User Profile')" />

        <div class="d-flex">
            <div
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-1 justify-evenly xl:block gap-4 xl:space-y-4"
            >
                <DatatableGroup
                    :data-table="preOrders"
                    :resource-count="user.pre_orders_count"
                    :title="__('Pre Orders')"
                    :expanded="expandedTables['preOrders']"
                    resource="preOrders"
                    @toggle-expanded="toggleExpanded"
                />

                <DatatableGroup
                    :data-table="purchaseOrders"
                    :resource-count="user.purchase_orders_count"
                    :title="__('Purchase Orders')"
                    :expanded="expandedTables['purchaseOrders']"
                    resource="purchaseOrders"
                    @toggle-expanded="toggleExpanded"
                />

                <DatatableGroup
                    :data-table="salesOrders"
                    :resource-count="user.sales_orders_count"
                    :title="__('Sales Orders')"
                    :expanded="expandedTables['salesOrders']"
                    resource="salesOrders"
                    @toggle-expanded="toggleExpanded"
                />

                <DatatableGroup
                    :data-table="serviceOrders"
                    :resource-count="user.service_orders_count"
                    :title="__('Service Orders')"
                    :expanded="expandedTables['serviceOrders']"
                    resource="serviceOrders"
                    @toggle-expanded="toggleExpanded"
                />

                <DatatableGroup
                    :data-table="workOrders"
                    :resource-count="user.work_orders_count"
                    :title="__('Work Orders')"
                    :expanded="expandedTables['workOrders']"
                    resource="workOrders"
                    @toggle-expanded="toggleExpanded"
                />

                <DatatableGroup
                    :data-table="transportOrders"
                    :resource-count="user.transport_orders_count"
                    :title="__('Transport Orders')"
                    :expanded="expandedTables['transportOrders']"
                    resource="transportOrders"
                    @toggle-expanded="toggleExpanded"
                />

                <DatatableGroup
                    :data-table="serviceLevels"
                    :resource-count="user.service_levels_count"
                    :title="__('Service Levels')"
                    :expanded="expandedTables['serviceLevels']"
                    resource="serviceLevels"
                    @toggle-expanded="toggleExpanded"
                />

                <DatatableGroup
                    :data-table="items"
                    :resource-count="user.items_count"
                    :title="__('Items')"
                    :expanded="expandedTables['items']"
                    resource="items"
                    @toggle-expanded="toggleExpanded"
                />

                <DatatableGroup
                    :data-table="documents"
                    :resource-count="user.documents_count"
                    :title="__('Documents')"
                    :expanded="expandedTables['documents']"
                    resource="documents"
                    @toggle-expanded="toggleExpanded"
                />

                <DatatableGroup
                    :data-table="vehicles"
                    :resource-count="user.vehicles_count"
                    :title="__('Vehicles')"
                    :expanded="expandedTables['vehicles']"
                    resource="vehicles"
                    @toggle-expanded="toggleExpanded"
                />

                <DatatableGroup
                    :data-table="preOrderVehicles"
                    :resource-count="user.pre_order_vehicles_count"
                    :title="__('Pre Order Vehicles')"
                    :expanded="expandedTables['preOrderVehicles']"
                    resource="preOrderVehicles"
                    @toggle-expanded="toggleExpanded"
                />

                <DatatableGroup
                    :data-table="serviceVehicles"
                    :resource-count="user.service_vehicles_count"
                    :title="__('Service Vehicles')"
                    :expanded="expandedTables['serviceVehicles']"
                    resource="serviceVehicles"
                    @toggle-expanded="toggleExpanded"
                />

                <DatatableGroup
                    :data-table="quotes"
                    :resource-count="user.quotes_count"
                    :title="__('Quotes')"
                    :expanded="expandedTables['quotes']"
                    resource="quotes"
                    @toggle-expanded="toggleExpanded"
                />

                <DatatableGroup
                    :data-table="makes"
                    :resource-count="user.makes_count"
                    :title="__('Makes')"
                    :expanded="expandedTables['makes']"
                    resource="makes"
                    @toggle-expanded="toggleExpanded"
                />

                <DatatableGroup
                    :data-table="vehicleModels"
                    :resource-count="user.vehicle_models_count"
                    :title="__('Vehicle Models')"
                    :expanded="expandedTables['vehicleModels']"
                    resource="vehicleModels"
                    @toggle-expanded="toggleExpanded"
                />

                <DatatableGroup
                    :data-table="vehicleGroups"
                    :resource-count="user.vehicle_groups_count"
                    :title="__('Vehicle Groups')"
                    :expanded="expandedTables['vehicleGroups']"
                    resource="vehicleGroups"
                    @toggle-expanded="toggleExpanded"
                />

                <DatatableGroup
                    :data-table="engines"
                    :resource-count="user.engines_count"
                    :title="__('Engines')"
                    :expanded="expandedTables['engines']"
                    resource="engines"
                    @toggle-expanded="toggleExpanded"
                />

                <DatatableGroup
                    :data-table="variants"
                    :resource-count="user.variants_count"
                    :title="__('Variants')"
                    :expanded="expandedTables['variants']"
                    resource="variants"
                    @toggle-expanded="toggleExpanded"
                />

                <DatatableGroup
                    :data-table="companies"
                    :resource-count="user.companies_count"
                    :title="__('Companies')"
                    :expanded="expandedTables['companies']"
                    resource="companies"
                    @toggle-expanded="toggleExpanded"
                />

                <DatatableGroup
                    :data-table="companyGroups"
                    :resource-count="user.company_groups_count"
                    :title="__('Company Groups')"
                    :expanded="expandedTables['companyGroups']"
                    resource="companyGroups"
                    @toggle-expanded="toggleExpanded"
                />

                <DatatableGroup
                    :data-table="users"
                    :resource-count="user.users_count"
                    :title="__('Users')"
                    :expanded="expandedTables['users']"
                    resource="users"
                    @toggle-expanded="toggleExpanded"
                />

                <DatatableGroup
                    :data-table="createdUserGroups"
                    :resource-count="user.created_user_groups_count"
                    :title="__('User Groups')"
                    :expanded="expandedTables['createdUserGroups']"
                    resource="createdUserGroups"
                    @toggle-expanded="toggleExpanded"
                />
            </div>
        </div>
    </AppLayout>
</template>
