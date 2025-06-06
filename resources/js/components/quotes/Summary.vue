<script setup lang="ts">
import { Link } from "@inertiajs/vue3";

import InformationRow from "@/components/html/InformationRow.vue";
import { QuoteStatus } from "@/enums/QuoteStatus";
import { Quote } from "@/types";
import { findEnumKeyByValue, replaceEnumUnderscores } from "@/utils";

defineProps<{
    resource: Quote;
}>();
</script>

<template>
    <div class="p-4 border-b border-[#E9E7E7] font-bold">
        {{ __("Quote") }} #{{ resource.id }}
    </div>

    <InformationRow :title="__('Status')">
        {{
            replaceEnumUnderscores(
                findEnumKeyByValue(QuoteStatus, resource.status)
            )
        }}
    </InformationRow>

    <InformationRow :title="__('Creator')">
        {{ resource.creator?.full_name }}
    </InformationRow>

    <InformationRow v-if="resource.name" :title="__('Name')">
        {{ resource.name }}
    </InformationRow>

    <InformationRow v-if="resource.customer_company" :title="__('Customer')">
        {{ resource.customer_company?.name }}
    </InformationRow>

    <InformationRow
        v-if="resource.customer"
        :title="__('Customer contact person')"
    >
        {{ resource.customer?.full_name }}
    </InformationRow>

    <InformationRow
        v-if="resource.total_quote_price"
        :title="__('Total quote price')"
    >
        {{ resource.total_quote_price }}
    </InformationRow>

    <div
        v-if="$can('edit-quote')"
        class="px-4 py-3 flex items-center justify-between"
    >
        <div class="text-[#6D6D73]">
            {{ __("Quote") }}
        </div>

        <Link
            :href="route('quotes.edit', resource.id)"
            class="border border-[#008FE3] text-[#008FE3] px-3 py-0.5 rounded-md hover:opacity-60 active:scale-95 transition"
        >
            {{ __("Go to") }}
        </Link>
    </div>
</template>
