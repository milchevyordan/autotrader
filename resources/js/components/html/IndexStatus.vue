<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { ref } from "vue";

import Modal from "@/components/Modal.vue";
import { DocumentStatus } from "@/enums/DocumentStatus";
import { PurchaseOrderStatus } from "@/enums/PurchaseOrderStatus";
import { QuoteStatus } from "@/enums/QuoteStatus";
import { SalesOrderStatus } from "@/enums/SalesOrderStatus";
import { TransportOrderStatus } from "@/enums/TransportOrderStatus";
import CheckIcon from "@/icons/Check.vue";
import {
    PurchaseOrder,
    SalesOrder,
    TransportOrder,
    Document,
    Quote,
} from "@/types";
import {
    convertStatusEnum,
    dateTimeToLocaleString,
    findCreatedAtStatusDate,
    findEnumKeyByValue,
    getStatusColorClassesByTaskCompletion,
    replaceEnumUnderscores,
} from "@/utils.js";

const props = defineProps<{
    item?: PurchaseOrder | SalesOrder | TransportOrder | Document | Quote;
    items?: Quote[];
    statusEnum:
        | typeof PurchaseOrderStatus
        | typeof SalesOrderStatus
        | typeof TransportOrderStatus
        | typeof DocumentStatus
        | typeof QuoteStatus;
    module: string;
    multiple?: boolean;
    text: string;
}>();

const showModal = ref(false);
const showMultipleModal = ref(false);

const statuses = convertStatusEnum(props.statusEnum);
</script>

<template>
    <button
        v-if="!multiple && item"
        :class="[
            'text-sm',
            'font-medium',
            'me-2',
            'px-2.5',
            'py-0.5',
            'rounded',
            'cursor-pointer',
            getStatusColorClassesByTaskCompletion(statusEnum, item.status),
        ]"
        @click="showModal = true"
    >
        {{
            replaceEnumUnderscores(findEnumKeyByValue(statusEnum, item?.status))
        }}
    </button>

    <Modal
        v-if="!multiple && item"
        :show="showModal"
        max-width="md"
        @close="showModal = false"
    >
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ text }} # {{ item?.id }}
        </div>

        <div class="border border-[#E9E7E7] px-3.5 p-3 d-flex w-100">
            <ul class="ml-4 text-slate-700">
                <li
                    v-for="(status, index) in statuses"
                    v-show="
                        !(
                            ((item as PurchaseOrder|SalesOrder|Document|Quote).status ===
                                (statusEnum as typeof PurchaseOrderStatus | typeof SalesOrderStatus | typeof DocumentStatus | typeof QuoteStatus).Approved ||
                                (item as PurchaseOrder|SalesOrder|Document|Quote).status >
                                (statusEnum as typeof PurchaseOrderStatus | typeof SalesOrderStatus | typeof DocumentStatus | typeof QuoteStatus).Rejected) &&
                            status.name === 'Rejected'
                        ) &&
                            !(
                                !(item as SalesOrder).down_payment &&
                                (status.name ==
                                    'Ready_for_down_payment_invoice' ||
                                    status.name ===
                                    'Down_payment_invoice_sent' ||
                                    status.name === 'Down_payment_done')
                            )
                            &&
                            !(
                                status.value ===
                                TransportOrderStatus.Offer_requested &&
                                !(item as TransportOrder).transport_company_use
                            )
                    "
                    :key="index"
                    class="status-li-container flex gap-1"
                >
                    <span
                        v-if="item.status >= status.value"
                        class="text-[#00A793]"
                    >
                        <CheckIcon />
                    </span>

                    <span v-else class="text-red-600 font-semi text-xl px-2">
                        X
                    </span>

                    <span>
                        <span>{{ replaceEnumUnderscores(status.name) }}</span>
                        <span class="text-xs pl-2 text-slate-700">
                            {{
                                status.value == 1
                                    ? dateTimeToLocaleString(item.created_at)
                                    : findCreatedAtStatusDate(
                                          item.statuses,
                                          status.value as number
                                      )
                            }}
                        </span>
                    </span>
                </li>
            </ul>
        </div>

        <div class="col-span-2 flex justify-end gap-3 mt-2 pt-1 pb-3 px-4">
            <Link
                v-if="$can(`edit-${module}`)"
                class="px-12 py-2 rounded hover:opacity-80 active:scale-95 transition bg-[#00A793] text-white"
                :href="route(`${module}s.edit`, item?.id)"
            >
                {{ __("Go To") }}
            </Link>

            <button
                class="bg-[#F0F0F0] px-12 py-2 rounded hover:opacity-80 active:scale-95 transition"
                @click="showModal = false"
            >
                {{ __("Close") }}
            </button>
        </div>
    </Modal>

    <button
        v-if="multiple && items"
        class="text-sm font-medium me-2 px-2.5 py-0.5 rounded cursor-pointer bg-gray-100 text-gray-800"
        @click="showMultipleModal = true"
    >
        {{ items.length }}
    </button>

    <Modal
        v-if="multiple && items"
        :show="showMultipleModal"
        max-width="md"
        @close="showMultipleModal = false"
    >
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ text }}
        </div>

        <div class="border border-[#E9E7E7] px-3.5 p-3 d-flex w-100">
            <ul class="ml-4 text-slate-700">
                <li
                    v-for="(status, index) in statuses"
                    :key="index"
                    class="status-li-container flex gap-1"
                >
                    <span>{{ replaceEnumUnderscores(status.name) }}</span>

                    <div v-if="$can(`edit-${module}`)" class="flex gap-1">
                        <Link
                            v-for="(itemInMultipleItems, indexItem) in items"
                            v-show="itemInMultipleItems.status === status.value"
                            :key="indexItem"
                            class="border border-[#E9E7E7] rounded-md px-1 active:scale-90 transition"
                            :href="
                                route(`${module}s.edit`, itemInMultipleItems.id)
                            "
                        >
                            #{{ itemInMultipleItems.id }}
                        </Link>
                    </div>
                </li>
            </ul>
        </div>

        <div class="col-span-2 flex justify-end gap-3 mt-2 pt-1 pb-3 px-4">
            <button
                class="bg-[#F0F0F0] px-12 py-2 rounded hover:opacity-80 active:scale-95 transition"
                @click="showMultipleModal = false"
            >
                {{ __("Close") }}
            </button>
        </div>
    </Modal>
</template>
