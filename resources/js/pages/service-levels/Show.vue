<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3";
import { ref } from "vue";

import Header from "@/components/Header.vue";
import DatatableGroup from "@/components/html/DataTableGroup.vue";
import Box from "@/components/service-levels/Box.vue";
import { DataTable } from "@/data-table/types";
import IconCreditCard from "@/icons/CreditCard.vue";
import IconDocumentText from "@/icons/DocumentText.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { Quote, SalesOrder, ServiceLevel, ServiceOrder } from "@/types";
import { convertUnitsToCurrency } from "@/utils";

defineProps<{
    serviceLevel: ServiceLevel;
    turnoverData: Record<string, Record<string, number>>;
    salesOrders?: DataTable<SalesOrder>;
    serviceOrders?: DataTable<ServiceOrder>;
    quotes?: DataTable<Quote>;
}>();

const expandedTables = ref<Record<string, boolean>>({
    salesOrders: false,
    serviceOrders: false,
    quotes: false,
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
    <Head :title="__('Service Level')" />
    <AppLayout>
        <Header :text="__('Service Level')" />

        <div class="d-flex">
            <div
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-1 justify-evenly xl:block gap-4 xl:space-y-4"
            >
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 lg:grid-cols-2 gap-4 mt-4 2xl:gap-5 2xl:mt-5"
                >
                    <Box
                        :text="__('Vehicles purchased this year -> Turnover')"
                        :number="
                            turnoverData.thisYear.vehicles != 0
                                ? `${
                                      turnoverData.thisYear.vehicles
                                  } -> € ${convertUnitsToCurrency(
                                      turnoverData.thisYear.turnover
                                  )}`
                                : 0
                        "
                    >
                        <IconCreditCard
                            class="w-6 h-6 md:w-8 md:h-8"
                            stroke="1.8"
                        />
                    </Box>

                    <Box
                        :text="__('Vehicles purchased last year -> Turnover')"
                        :number="
                            turnoverData.lastYear.vehicles != 0
                                ? `${
                                      turnoverData.lastYear.vehicles
                                  } -> € ${convertUnitsToCurrency(
                                      turnoverData.lastYear.turnover
                                  )}`
                                : 0
                        "
                    >
                        <IconDocumentText
                            class="w-6 h-6 md:w-8 md:h-8"
                            stroke="1.8"
                        />
                    </Box>
                </div>

                <DatatableGroup
                    :data-table="salesOrders"
                    :resource-count="serviceLevel.sales_orders_count"
                    :title="__('Sales Orders')"
                    :expanded="expandedTables['salesOrders']"
                    resource="salesOrders"
                    @toggle-expanded="toggleExpanded"
                />

                <DatatableGroup
                    :data-table="serviceOrders"
                    :resource-count="serviceLevel.service_orders_count"
                    :title="__('Service Orders')"
                    :expanded="expandedTables['serviceOrders']"
                    resource="serviceOrders"
                    @toggle-expanded="toggleExpanded"
                />

                <DatatableGroup
                    :data-table="quotes"
                    :resource-count="serviceLevel.quotes_count"
                    :title="__('Quotes')"
                    :expanded="expandedTables['quotes']"
                    resource="quotes"
                    @toggle-expanded="toggleExpanded"
                />
            </div>
        </div>
    </AppLayout>
</template>
