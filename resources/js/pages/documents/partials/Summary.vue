<script setup lang="ts">
import { Link } from "@inertiajs/vue3";

import InformationRow from "@/components/html/InformationRow.vue";
import { DocumentStatus } from "@/enums/DocumentStatus";
import { PaymentCondition } from "@/enums/PaymentCondition";
import { Document } from "@/types";
import { findEnumKeyByValue, replaceEnumUnderscores } from "@/utils";

defineProps<{
    resource: Document;
}>();
</script>

<template>
    <div class="p-4 border-b border-[#E9E7E7] font-bold">
        {{ __("Invoice") }} #{{ resource.id }}
    </div>

    <InformationRow :title="__('Status')">
        {{
            replaceEnumUnderscores(
                findEnumKeyByValue(DocumentStatus, resource.status)
            )
        }}
    </InformationRow>

    <InformationRow :title="__('Creator')">
        {{ resource.creator?.full_name }}
    </InformationRow>

    <InformationRow :title="__('Payment Condition')">
        {{
            replaceEnumUnderscores(
                findEnumKeyByValue(PaymentCondition, resource.payment_condition)
            )
        }}
    </InformationRow>

    <InformationRow v-if="resource.number" :title="__('Document Number')">
        {{ resource.number }}
    </InformationRow>

    <InformationRow
        v-if="resource.total_price_exclude_vat"
        :title="__('Total Price Exclude Vat')"
    >
        {{ resource.total_price_exclude_vat }}
    </InformationRow>

    <InformationRow v-if="resource.total_vat" :title="__('Total Vat')">
        {{ resource.total_vat }}
    </InformationRow>

    <InformationRow
        v-if="resource.total_price_include_vat"
        :title="__('Total Price Include Vat')"
    >
        {{ resource.total_price_include_vat }}
    </InformationRow>

    <div
        v-if="$can('edit-document')"
        class="px-4 py-3 flex items-center justify-between"
    >
        <div class="text-[#6D6D73]">
            {{ __("Invoice") }}
        </div>

        <Link
            :href="route('documents.edit', resource.id)"
            class="border border-[#008FE3] text-[#008FE3] px-3 py-0.5 rounded-md hover:opacity-60 active:scale-95 transition"
        >
            {{ __("Go to") }}
        </Link>
    </div>
</template>
