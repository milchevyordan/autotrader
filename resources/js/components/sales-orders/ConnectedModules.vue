<script setup lang="ts">
import { computed } from "vue";

import ConnectedToModulesRow from "@/components/html/ConnectedToModulesRow.vue";
import Section from "@/components/html/Section.vue";
import { ConnectedToModulesType } from "@/enums/ConnectedToModulesType";
import { $can } from "@/plugins/permissions";
import {
    PurchaseOrder,
    Quote,
    SalesOrderForm,
    TransportOrder,
    Vehicle,
    Document,
    WorkOrder,
} from "@/types";

const props = defineProps<{
    salesOrder: SalesOrderForm;
}>();

const allWorkOrders = computed(() => {
    return props.salesOrder.vehicles
        .map((vehicle: Vehicle) => vehicle.work_order)
        .filter((workOrder: WorkOrder) => workOrder != null);
});

const allPurchaseOrders = computed(() => {
    const seenIds = new Set<number>(); // To track unique purchase order IDs
    return props.salesOrder.vehicles
        .flatMap((vehicle: Vehicle) => vehicle.purchase_order ?? [])
        .filter((purchaseOrder: PurchaseOrder) => {
            if (purchaseOrder && !seenIds.has(purchaseOrder.id)) {
                seenIds.add(purchaseOrder.id); // Mark the purchase order as seen
                return true;
            }
            return false;
        });
});

const allTransportOrders = computed(() => {
    const seenIds = new Set<number>(); // To track unique transport order IDs
    return props.salesOrder.vehicles
        .flatMap((vehicle: Vehicle) => vehicle.transport_orders ?? [])
        .filter((transportOrder: TransportOrder) => {
            if (transportOrder && !seenIds.has(transportOrder.id)) {
                seenIds.add(transportOrder.id); // Mark the transport order as seen
                return true;
            }
            return false;
        });
});

const allVehicleInvoices = computed(() => {
    const seenIds = new Set<number>(); // To track unique transport order IDs
    return props.salesOrder.vehicles
        .flatMap((vehicle: Vehicle) => vehicle.documents ?? [])
        .filter((invoice: Document) => {
            if (invoice && !seenIds.has(invoice.id)) {
                seenIds.add(invoice.id); // Mark the invoice as seen
                return true;
            }
            return false;
        });
});

const allQuotes = computed(() => {
    const seenIds = new Set<number>(); // To track unique transport order IDs
    return props.salesOrder.vehicles
        .flatMap((vehicle: Vehicle) => vehicle.quotes ?? [])
        .filter((quote: Quote) => {
            if (quote && !seenIds.has(quote.id)) {
                seenIds.add(quote.id); // Mark the transport order as seen
                return true;
            }
            return false;
        });
});
</script>
<template>
    <Section classes="p-4 mt-4">
        <div class="font-semibold text-xl sm:text-2xl mb-4">
            {{ __("Connected to the Modules") }}
        </div>

        <ConnectedToModulesRow
            :title="__('Purchase Order')"
            :type="ConnectedToModulesType.Collection"
            :resource="allPurchaseOrders"
            :has-permission="$can('edit-purchase-order')"
            href="purchase-orders"
        />

        <ConnectedToModulesRow
            :title="__('Transport Order')"
            :type="ConnectedToModulesType.Collection"
            :resource="allTransportOrders"
            :has-permission="$can('edit-transport-order')"
            href="transport-orders"
        />

        <ConnectedToModulesRow
            :title="__('Work Order')"
            :type="ConnectedToModulesType.Collection"
            :resource="allWorkOrders"
            :has-permission="$can('edit-work-order')"
            href="work-orders"
        />

        <ConnectedToModulesRow
            :title="__('Invoice')"
            :type="ConnectedToModulesType.Collection"
            :resource="salesOrder.documents"
            :has-permission="$can('edit-document')"
            href="documents"
        />

        <ConnectedToModulesRow
            :title="__('Vehicle Invoice')"
            :type="ConnectedToModulesType.Collection"
            :resource="allVehicleInvoices"
            :has-permission="$can('edit-document')"
            href="documents"
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
