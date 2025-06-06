<script setup lang="ts">
import { computed } from "vue";

import ConnectedToModulesRow from "@/components/html/ConnectedToModulesRow.vue";
import Section from "@/components/html/Section.vue";
import { ConnectedToModulesType } from "@/enums/ConnectedToModulesType";
import { DocumentableType } from "@/enums/DocumentableType";
import { $can } from "@/plugins/permissions";
import {
    Document,
    PreOrderVehicle,
    PurchaseOrder,
    Quote,
    SalesOrder,
    ServiceOrder,
    ServiceVehicle,
    TransportOrder,
    Vehicle,
} from "@/types";

const props = defineProps<{
    document: Document;
}>();

const allPreOrders = computed(() => {
    if (
        props.document.documentable_type != DocumentableType.Pre_order_vehicle
    ) {
        return [];
    }

    return props.document.pre_order_vehicles?.flatMap(
        (preOrderVehicle: PreOrderVehicle) => preOrderVehicle.pre_order ?? []
    );
});

const allPurchaseOrders = computed(() => {
    const seenIds = new Set<number>(); // To track unique purchase order IDs

    switch (props.document.documentable_type) {
        case DocumentableType.Vehicle:
            return props.document.vehicles
                ?.flatMap((vehicle: Vehicle) => vehicle.purchase_order ?? [])
                .filter((purchaseOrder: PurchaseOrder) => {
                    if (purchaseOrder && !seenIds.has(purchaseOrder.id)) {
                        seenIds.add(purchaseOrder.id); // Mark the purchase order as seen
                        return true;
                    }
                    return false;
                });

        case DocumentableType.Sales_order_down_payment:
        case DocumentableType.Sales_order:
            return props.document.sales_orders?.flatMap(
                (salesOrder: SalesOrder) =>
                    salesOrder.vehicles
                        ?.flatMap((vehicle: Vehicle) => vehicle.purchase_order)
                        .filter(
                            (purchaseOrder: PurchaseOrder) =>
                                purchaseOrder &&
                                !seenIds.has(purchaseOrder.id) &&
                                seenIds.add(purchaseOrder.id)
                        )
            );

        default:
            return [];
    }
});

const allSalesOrders = computed(() => {
    if (props.document.documentable_type != DocumentableType.Vehicle) {
        return [];
    }

    const seenIds = new Set<number>(); // To track unique sales order IDs
    return props.document.vehicles
        ?.flatMap((vehicle: Vehicle) => vehicle.sales_order ?? [])
        .filter((salesOrder: SalesOrder) => {
            if (salesOrder && !seenIds.has(salesOrder.id)) {
                seenIds.add(salesOrder.id); // Mark the sales order as seen
                return true;
            }
            return false;
        });
});

const allServiceOrders = computed(() => {
    if (props.document.documentable_type != DocumentableType.Service_vehicle) {
        return [];
    }

    return props.document.service_vehicles?.flatMap(
        (serviceVehicles: ServiceVehicle) => serviceVehicles.service_order ?? []
    );
});

const allTransportOrders = computed(() => {
    const seenIds = new Set<number>(); // To track unique transport order IDs

    switch (props.document.documentable_type) {
        case DocumentableType.Vehicle:
            return props.document.vehicles
                ?.flatMap((vehicle: Vehicle) => vehicle.transport_orders ?? [])
                .filter((transportOrder: TransportOrder) => {
                    if (transportOrder && !seenIds.has(transportOrder.id)) {
                        seenIds.add(transportOrder.id); // Mark the transport order as seen
                        return true;
                    }
                    return false;
                });

        case DocumentableType.Service_vehicle:
            return props.document.service_vehicles
                ?.flatMap(
                    (serviceVehicle: ServiceVehicle) =>
                        serviceVehicle.transport_orders ?? []
                )
                .filter((transportOrder: TransportOrder) => {
                    if (transportOrder && !seenIds.has(transportOrder.id)) {
                        seenIds.add(transportOrder.id); // Mark the transport order as seen
                        return true;
                    }
                    return false;
                });

        case DocumentableType.Pre_order_vehicle:
            return props.document.pre_order_vehicles
                ?.flatMap(
                    (preOrderVehicle: PreOrderVehicle) =>
                        preOrderVehicle.transport_orders ?? []
                )
                .filter((transportOrder: TransportOrder) => {
                    if (transportOrder && !seenIds.has(transportOrder.id)) {
                        seenIds.add(transportOrder.id); // Mark the transport order as seen
                        return true;
                    }
                    return false;
                });

        case DocumentableType.Sales_order_down_payment:
        case DocumentableType.Sales_order:
            return props.document.sales_orders?.flatMap(
                (salesOrder: SalesOrder) =>
                    salesOrder.vehicles
                        ?.flatMap(
                            (vehicle: Vehicle) => vehicle.transport_orders
                        )
                        .filter(
                            (transportOrder: TransportOrder) =>
                                transportOrder &&
                                !seenIds.has(transportOrder.id) &&
                                seenIds.add(transportOrder.id)
                        )
            );

        case DocumentableType.Service_order:
            return props.document.service_orders?.flatMap(
                (serviceOrder: ServiceOrder) =>
                    serviceOrder.service_vehicle?.transport_orders?.filter(
                        (transportOrder: TransportOrder) =>
                            transportOrder &&
                            !seenIds.has(transportOrder.id) &&
                            seenIds.add(transportOrder.id)
                    )
            );

        default:
            return [];
    }
});

const allWorkflows = computed(() => {
    switch (props.document.documentable_type) {
        case DocumentableType.Vehicle:
            return props.document.vehicles?.flatMap(
                (vehicle: Vehicle) => vehicle.workflow ?? []
            );

        case DocumentableType.Service_vehicle:
            return props.document.service_vehicles?.flatMap(
                (serviceVehicle: ServiceVehicle) =>
                    serviceVehicle.workflow ?? []
            );

        case DocumentableType.Sales_order_down_payment:
        case DocumentableType.Sales_order:
            return props.document.sales_orders?.flatMap(
                (salesOrder: SalesOrder) =>
                    salesOrder.vehicles?.flatMap(
                        (vehicle: Vehicle) => vehicle.workflow ?? []
                    )
            );

        case DocumentableType.Service_order:
            return props.document.service_orders?.flatMap(
                (serviceOrder: ServiceOrder) =>
                    serviceOrder.service_vehicle?.workflow ?? []
            );

        default:
            return [];
    }
});

const allWorkOrders = computed(() => {
    switch (props.document.documentable_type) {
        case DocumentableType.Vehicle:
            return props.document.vehicles?.flatMap(
                (vehicle: Vehicle) => vehicle.work_order ?? []
            );

        case DocumentableType.Service_vehicle:
            return props.document.service_vehicles?.flatMap(
                (serviceVehicle: ServiceVehicle) =>
                    serviceVehicle.work_order ?? []
            );

        case DocumentableType.Sales_order_down_payment:
        case DocumentableType.Sales_order:
            return props.document.sales_orders?.flatMap(
                (salesOrder: SalesOrder) =>
                    salesOrder.vehicles?.flatMap(
                        (vehicle: Vehicle) => vehicle.work_order ?? []
                    )
            );

        case DocumentableType.Service_order:
            return props.document.service_orders?.flatMap(
                (serviceOrder: ServiceOrder) =>
                    serviceOrder.service_vehicle?.work_order ?? []
            );

        default:
            return [];
    }
});

const allQuotes = computed(() => {
    const seenIds = new Set<number>(); // To track unique quote IDs

    switch (props.document.documentable_type) {
        case DocumentableType.Vehicle:
            return props.document.vehicles
                ?.flatMap((vehicle: Vehicle) => vehicle.quotes ?? [])
                .filter((quote: Quote) => {
                    if (quote && !seenIds.has(quote.id)) {
                        seenIds.add(quote.id); // Mark the quote as seen
                        return true;
                    }
                    return false;
                });

        case DocumentableType.Sales_order_down_payment:
        case DocumentableType.Sales_order:
            return props.document.sales_orders?.flatMap(
                (salesOrder: SalesOrder) =>
                    salesOrder.vehicles
                        ?.flatMap((vehicle: Vehicle) => vehicle.quotes)
                        .filter(
                            (quote: Quote) =>
                                quote &&
                                !seenIds.has(quote.id) &&
                                seenIds.add(quote.id)
                        )
            );

        default:
            return [];
    }
});
</script>
<template>
    <Section classes="p-4 mt-4">
        <div class="font-semibold text-xl sm:text-2xl mb-4">
            {{ __("Connected to the Modules") }}
        </div>

        <ConnectedToModulesRow
            :title="__('Pre Order')"
            :type="ConnectedToModulesType.Collection"
            :resource="allPreOrders"
            :has-permission="$can('edit-pre-order')"
            href="pre-orders"
        />

        <ConnectedToModulesRow
            :title="__('Purchase Order')"
            :type="ConnectedToModulesType.Collection"
            :resource="allPurchaseOrders"
            :has-permission="$can('edit-purchase-order')"
            href="purchase-orders"
        />

        <ConnectedToModulesRow
            :title="__('Sales Order')"
            :type="ConnectedToModulesType.Collection"
            :resource="allSalesOrders"
            :has-permission="$can('edit-sales-order')"
            href="sales-orders"
        />

        <ConnectedToModulesRow
            :title="__('Service Order')"
            :type="ConnectedToModulesType.Collection"
            :resource="allServiceOrders"
            :has-permission="$can('edit-service-order')"
            href="service-orders"
        />

        <ConnectedToModulesRow
            :title="__('Transport Order')"
            :type="ConnectedToModulesType.Collection"
            :resource="allTransportOrders"
            :has-permission="$can('edit-transport-order')"
            href="transport-orders"
        />

        <ConnectedToModulesRow
            :title="__('Workflow')"
            :type="ConnectedToModulesType.Collection"
            :resource="allWorkflows"
            :has-permission="$can('view-workflow')"
            href="workflows"
            method="show"
        />

        <ConnectedToModulesRow
            :title="__('Work Order')"
            :type="ConnectedToModulesType.Collection"
            :resource="allWorkOrders"
            :has-permission="$can('edit-work-order')"
            href="work-orders"
        />

        <ConnectedToModulesRow
            :title="__('Quote')"
            :type="ConnectedToModulesType.Collection"
            :resource="allQuotes"
            :has-permission="$can('edit-quote')"
            href="quotes"
        />
    </Section>
</template>
