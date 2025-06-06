<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3";
import { defineAsyncComponent } from "vue";
import { ref } from "vue";

import Header from "@/components/Header.vue";
import { DataTable } from "@/data-table/types";
import AppLayout from "@/layouts/AppLayout.vue";
import Tabs from "@/pages/work-filters/partials/Tabs.vue";
import { Vehicle, QuoteInvitation, WorkFilterTab } from "@/types";

// ucfirst of the php table method
const props = defineProps<{
    dataTableType:
        | "VehiclesDataTable"
        | "serviceVehiclesDataTable"
        | "QuoteInvitationsDataTable"
        | "QuotesDataTable"
        | "PurchaseOrdersDataTable"
        | "SalesOrdersDataTable";
    method: string;
    dataTable: DataTable<Vehicle | QuoteInvitation>;
}>();

const tableComponentMap: Record<
    string,
    ReturnType<typeof defineAsyncComponent>
> = {
    VehiclesDataTable: defineAsyncComponent(
        () => import("@/pages/work-filters/partials/VehiclesDataTable.vue")
    ),

    ServiceVehiclesDataTable: defineAsyncComponent(
        () =>
            import("@/pages/work-filters/partials/ServiceVehiclesDataTable.vue")
    ),

    QuoteInvitationsDataTable: defineAsyncComponent(
        () =>
            import(
                "@/pages/work-filters/partials/QuoteInvitationsDataTable.vue"
            )
    ),

    QuotesDataTable: defineAsyncComponent(
        () => import("@/pages/work-filters/partials/QuotesDataTable.vue")
    ),

    PurchaseOrdersDataTable: defineAsyncComponent(
        () =>
            import("@/pages/work-filters/partials/PurchaseOrdersDataTable.vue")
    ),

    SalesOrdersDataTable: defineAsyncComponent(
        () => import("@/pages/work-filters/partials/SalesOrdersDataTable.vue")
    ),

    ServiceOrdersDataTable: defineAsyncComponent(
        () => import("@/pages/work-filters/partials/ServiceOrdersDataTable.vue")
    ),
};
const tableComponentEl = tableComponentMap[props.dataTableType];

const params = new URLSearchParams(window.location.search);
const refFlagsFilter = ref(params.get("flags"));

const handleTabSwitched = (tab: WorkFilterTab) => {
    changeGetParams(tab);
};

const changeGetParams = (tab: WorkFilterTab) => {
    const currentUrl = new URL(window.location.href);
    const pathName = currentUrl.pathname;

    // Check if the current URL ends with "/flags"
    if (tab == "redFlags" && !pathName.endsWith("/flags")) {
        // Add "/flags" to the URL
        currentUrl.pathname = `${pathName}/flags`;
    } else {
        // Remove "/flags" from the URL
        if (tab == "allRecords")
            currentUrl.pathname = pathName.replace("/flags", "");
    }

    // Update the URL without reloading the page
    window.history.pushState({}, "", currentUrl.href);

    // Update the refFlagsFilter to reflect the change
    refFlagsFilter.value = currentUrl.pathname.endsWith("/flags")
        ? "Red"
        : null;

    router.reload();
};
</script>

<template>
    <Head :title="__('Work Filters') + ' / ' + method" />

    <AppLayout>
        <Header :text="__('Work Filters') + ' / ' + method" />

        <Tabs @switched="handleTabSwitched" />

        <component
            :is="tableComponentEl"
            :data-table="dataTable"
            :per-page-options="[5, 10, 15, 20, 50]"
        />
    </AppLayout>
</template>
