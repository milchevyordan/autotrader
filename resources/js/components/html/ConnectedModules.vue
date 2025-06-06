<script setup lang="ts">
import ConnectedToModulesRow from "@/components/html/ConnectedToModulesRow.vue";
import Section from "@/components/html/Section.vue";
import { ConnectedToModulesType } from "@/enums/ConnectedToModulesType";
import {
    Document,
    PreOrder,
    PurchaseOrder,
    Quote,
    SalesOrder,
    ServiceOrder,
    TransportOrder,
    Workflow,
    WorkOrder,
} from "@/types";

defineProps<{
    preOrder?: PreOrder;
    preOrderCollectionFirst?: PreOrder;
    purchaseOrder?: PurchaseOrder[];
    salesOrder?: SalesOrder[];
    serviceOrder?: ServiceOrder;
    transportOrders?: TransportOrder[];
    workflow?: Workflow;
    documents?: Document[];
    workOrder?: WorkOrder;
    quotes?: Quote[];
}>();
</script>
<template>
    <Section classes="p-4 mt-4">
        <div class="font-semibold text-xl sm:text-2xl mb-4">
            {{ __("Connected to the Modules") }}
        </div>

        <ConnectedToModulesRow
            :title="__('Pre Order')"
            :type="ConnectedToModulesType.Single_record"
            :resource="preOrder"
            :has-permission="$can('edit-pre-order')"
            href="pre-orders"
        />

        <ConnectedToModulesRow
            :title="__('Origin Pre Order')"
            :type="ConnectedToModulesType.Collection_first"
            :resource="preOrderCollectionFirst"
            :has-permission="$can('edit-pre-order')"
            href="pre-orders"
        />

        <ConnectedToModulesRow
            :title="__('Purchase Order')"
            :type="ConnectedToModulesType.Collection_first"
            :resource="purchaseOrder"
            :has-permission="$can('edit-purchase-order')"
            href="purchase-orders"
        />

        <ConnectedToModulesRow
            :title="__('Sales Order')"
            :type="ConnectedToModulesType.Collection_first"
            :resource="salesOrder"
            :has-permission="$can('edit-sales-order')"
            href="sales-orders"
        />

        <ConnectedToModulesRow
            :title="__('Service Order')"
            :type="ConnectedToModulesType.Single_record"
            :resource="serviceOrder"
            :has-permission="$can('edit-service-order')"
            href="service-orders"
        />

        <ConnectedToModulesRow
            :title="__('Transport Order')"
            :type="ConnectedToModulesType.Collection"
            :resource="transportOrders"
            :has-permission="$can('edit-transport-order')"
            href="transport-orders"
        />

        <ConnectedToModulesRow
            :title="__('Workflow')"
            :type="ConnectedToModulesType.Single_record"
            :resource="workflow"
            :has-permission="$can('view-workflow')"
            href="workflows"
            method="show"
        />

        <ConnectedToModulesRow
            :title="__('Document')"
            :type="ConnectedToModulesType.Collection_first"
            :resource="documents"
            :has-permission="$can('edit-document')"
            href="documents"
        />

        <ConnectedToModulesRow
            :title="__('Work Order')"
            :type="ConnectedToModulesType.Single_record"
            :resource="workOrder"
            :has-permission="$can('edit-work-order')"
            href="work-orders"
        />

        <ConnectedToModulesRow
            :title="__('Quote')"
            :type="ConnectedToModulesType.Collection"
            :resource="quotes"
            :has-permission="$can('edit-quote')"
            href="quotes"
        />
    </Section>
</template>
