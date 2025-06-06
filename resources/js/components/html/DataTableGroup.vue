<script setup lang="ts">
import WeekRangePicker from "@/components/html/WeekRangePicker.vue";
import Table from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import { DocumentableType } from "@/enums/DocumentableType";
import { DocumentStatus } from "@/enums/DocumentStatus";
import { FuelType } from "@/enums/FuelType";
import { ImportEuOrWorldType } from "@/enums/ImportEuOrWorldType";
import { ImportOrOriginType } from "@/enums/ImportOrOriginType";
import { ItemType } from "@/enums/ItemType";
import { NationalEuOrWorldType } from "@/enums/NationalEuOrWorldType";
import { PaymentCondition } from "@/enums/PaymentCondition";
import { PreOrderStatus } from "@/enums/PreOrderStatus";
import { PurchaseOrderStatus } from "@/enums/PurchaseOrderStatus";
import { QuoteStatus } from "@/enums/QuoteStatus";
import { SalesOrderStatus } from "@/enums/SalesOrderStatus";
import { ServiceLevelType } from "@/enums/ServiceLevelType";
import { ServiceOrderStatus } from "@/enums/ServiceOrderStatus";
import { TransportableType } from "@/enums/TransportableType";
import { TransportOrderStatus } from "@/enums/TransportOrderStatus";
import { TransportType } from "@/enums/TransportType";
import { WorkOrderStatus } from "@/enums/WorkOrderStatus";
import { WorkOrderType } from "@/enums/WorkOrderType";
import IconBottomArrowAccordion from "@/icons/BottomArrowAccordion.vue";
import { Enum } from "@/types";
import {
    dateTimeToLocaleString,
    findEnumKeyByValue,
    replaceEnumUnderscores,
} from "@/utils";

const props = defineProps<{
    dataTable?: DataTable<any>;
    resourceCount?: number;
    resource: string;
    title: string;
    expanded: boolean;
}>();

const emit = defineEmits(["toggle-expanded"]);

const toggleExpanded = () => {
    emit("toggle-expanded", props.resource);
};

const statusEnumMap: Record<string, Enum<any>> = {
    preOrders: PreOrderStatus,
    purchaseOrders: PurchaseOrderStatus,
    salesOrders: SalesOrderStatus,
    serviceOrders: ServiceOrderStatus,
    workOrders: WorkOrderStatus,
    transportOrders: TransportOrderStatus,
    documents: DocumentStatus,
    quotes: QuoteStatus,
};

const typeEnumMap: Record<string, Enum<any>> = {
    purchaseOrders: NationalEuOrWorldType,
    workOrders: WorkOrderType,
    serviceLevels: ServiceLevelType,
    items: ItemType,
    documents: DocumentableType,
};

const typeEnumMapWithDefault = new Proxy(typeEnumMap, {
    get(target, prop: string) {
        // If the key exists, return the corresponding enum, otherwise return the default
        return prop in target ? target[prop] : ImportEuOrWorldType;
    },
});
</script>

<template>
    <div v-if="resourceCount" class="py-1">
        <div
            class="flex items-center justify-between py-2.5 bg-white shadow-md border rounded-t-lg border-[#E9E7E7] px-4"
        >
            <div class="flex items-center gap-2">
                <div
                    class="w-7 h-6 lg:w-8 lg:h-7 rounded-md element-center bg-[#008FE3] text-white text-sm font-semibold"
                >
                    {{ resourceCount }}
                </div>

                <div class="font-semibold text-slate-800 text-sm xl:text-base">
                    {{ title }}
                </div>
            </div>

            <div class="flex items-center gap-2">
                <IconBottomArrowAccordion
                    class="w-5 h-5 transition-all duration-500 text-gray-500 cursor-pointer"
                    :class="{
                        'rotate-180': !expanded,
                    }"
                    @click="toggleExpanded"
                />
            </div>
        </div>

        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div v-if="expanded && dataTable">
                <Table
                    :data-table="dataTable"
                    :per-page-options="[5, 10, 15, 20, 50]"
                    :global-search="true"
                    :prop-name="resource"
                >
                    <template #cell(status)="{ value, item }">
                        {{
                            replaceEnumUnderscores(
                                findEnumKeyByValue(
                                    statusEnumMap[resource],
                                    value
                                )
                            )
                        }}
                    </template>

                    <template #cell(delivery_week)="{ value, item }">
                        <div class="flex gap-1.5 min-w-[270px]">
                            <WeekRangePicker
                                :model-value="value"
                                name="delivery_week"
                                disabled
                            />
                        </div>
                    </template>

                    <template #cell(purchaser.company.name)="{ value, item }">
                        {{ item.purchaser?.company?.name }}
                    </template>

                    <template #cell(seller.company.name)="{ value, item }">
                        {{ item.seller?.company?.name }}
                    </template>

                    <template #cell(type)="{ value, item }">
                        {{
                            replaceEnumUnderscores(
                                findEnumKeyByValue(
                                    typeEnumMapWithDefault[resource],
                                    value
                                )
                            )
                        }}
                    </template>

                    <template #cell(type_of_sale)="{ value, item }">
                        {{
                            replaceEnumUnderscores(
                                findEnumKeyByValue(ImportOrOriginType, value)
                            )
                        }}
                    </template>

                    <template #cell(payment_condition)="{ value, item }">
                        <div class="flex gap-1.5">
                            {{
                                replaceEnumUnderscores(
                                    findEnumKeyByValue(PaymentCondition, value)
                                )
                            }}
                        </div>
                    </template>

                    <template #cell(documentable_type)="{ value, item }">
                        <div class="flex gap-1.5">
                            {{
                                replaceEnumUnderscores(
                                    findEnumKeyByValue(DocumentableType, value)
                                )
                            }}
                        </div>
                    </template>

                    <template #cell(vehicle_type)="{ value, item }">
                        <div class="flex gap-1.5">
                            {{
                                replaceEnumUnderscores(
                                    findEnumKeyByValue(TransportableType, value)
                                )
                            }}
                        </div>
                    </template>

                    <template #cell(transport_type)="{ value, item }">
                        <div class="flex gap-1.5">
                            {{
                                replaceEnumUnderscores(
                                    findEnumKeyByValue(TransportType, value)
                                )
                            }}
                        </div>
                    </template>

                    <template #cell(transportCompany.name)="{ value, item }">
                        <div class="flex gap-1.5">
                            {{ item.transport_company?.name }}
                        </div>
                    </template>

                    <template #cell(fuel)="{ value, item }">
                        <div class="flex gap-1.5">
                            {{
                                replaceEnumUnderscores(
                                    findEnumKeyByValue(FuelType, value)
                                )
                            }}
                        </div>
                    </template>

                    <template #cell(mainUser.full_name)="{ value, item }">
                        {{ item.main_user?.full_name }}
                    </template>

                    <template #cell(roles.name)="{ value, item }">
                        <div v-for="(role, index) in item.roles" :key="index">
                            {{ role.name }}
                        </div>
                    </template>

                    <template #cell(created_at)="{ value, item }">
                        <div class="flex gap-1.5">
                            {{ dateTimeToLocaleString(value) }}
                        </div>
                    </template>

                    <template #cell(updated_at)="{ value, item }">
                        <div class="flex gap-1.5">
                            {{ dateTimeToLocaleString(value) }}
                        </div>
                    </template>
                </Table>
            </div>
        </Transition>
    </div>
</template>
