<script setup lang="ts">
import { Link } from "@inertiajs/vue3";

import InformationRow from "@/components/html/InformationRow.vue";
import { Currency } from "@/enums/Currency";
import { PurchaseOrderStatus } from "@/enums/PurchaseOrderStatus";
import { SupplierOrIntermediary } from "@/enums/SupplierOrIntermediary";
import { PurchaseOrder } from "@/types";
import {
    booleanRepresentation,
    findEnumKeyByValue,
    replaceEnumUnderscores,
} from "@/utils";

defineProps<{
    resource: PurchaseOrder;
}>();
</script>

<template>
    <div class="p-4 border-b border-[#E9E7E7] font-bold">
        {{ __("Purchase order") }} #{{ resource.id }}
    </div>

    <InformationRow :title="__('Status')">
        {{
            replaceEnumUnderscores(
                findEnumKeyByValue(PurchaseOrderStatus, resource.status)
            )
        }}
    </InformationRow>

    <InformationRow :title="__('Supplier')">
        {{ resource.supplier_company?.name }}
    </InformationRow>

    <InformationRow
        v-if="resource.supplier"
        :title="__('Contact person supplier')"
    >
        {{ resource.supplier.full_name }}
    </InformationRow>

    <InformationRow
        v-if="resource.intermediary_company"
        :title="__('Intermediary')"
    >
        {{ resource.intermediary_company?.name }}
    </InformationRow>

    <InformationRow
        v-if="resource.intermediary"
        :title="__('Intermediary contact person')"
    >
        {{ resource.intermediary.full_name }}
    </InformationRow>

    <InformationRow :title="__('Invoice from')">
        {{
            findEnumKeyByValue(
                SupplierOrIntermediary,
                resource.document_from_type
            )
        }}
    </InformationRow>

    <InformationRow :title="__('Currency of the PO')">
        {{
            __(
                replaceEnumUnderscores(
                    findEnumKeyByValue(Currency, resource.currency_po)
                )
            )
        }}
    </InformationRow>

    <InformationRow :title="__('Company Purchaser')">
        {{ resource.purchaser.full_name }}
    </InformationRow>

    <InformationRow :title="__('VAT deposit / Kaution')">
        {{ booleanRepresentation(resource.vat_deposit) }}
    </InformationRow>

    <InformationRow
        v-if="resource.vat_percentage"
        :title="__('VAT percentage')"
    >
        {{ resource.vat_percentage }}%
    </InformationRow>

    <InformationRow :title="__('Down payment')">
        {{ booleanRepresentation(resource.down_payment) }}
    </InformationRow>

    <InformationRow
        v-if="resource.down_payment"
        :title="__('Down payment amount')"
    >
        {{ resource.down_payment_amount }}
    </InformationRow>

    <div
        v-if="$can('edit-purchase-order')"
        class="px-4 py-3 flex items-center justify-between"
    >
        <div class="text-[#6D6D73]">
            {{ __("Purchase order") }}
        </div>

        <Link
            :href="route('purchase-orders.edit', resource.id)"
            class="border border-[#008FE3] text-[#008FE3] px-3 py-0.5 rounded-md hover:opacity-60 active:scale-95 transition"
        >
            {{ __("Go to") }}
        </Link>
    </div>
</template>
