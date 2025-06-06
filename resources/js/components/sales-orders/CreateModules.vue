<script setup lang="ts">
import { router, useForm } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";

import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Section from "@/components/html/Section.vue";
import Toggle from "@/components/html/Toggle.vue";
import Modal from "@/components/Modal.vue";
import Select from "@/components/Select.vue";
import { DocumentableType } from "@/enums/DocumentableType";
import { SalesOrderStatus } from "@/enums/SalesOrderStatus";
import { TransportableType } from "@/enums/TransportableType";
import { TransportType } from "@/enums/TransportType";
import {
    Enum,
    Document,
    SalesOrderForm,
    TransportOrder,
    Vehicle,
} from "@/types";
import { omitEnumKeys } from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    salesOrder: SalesOrderForm;
}>();

const vehicles = computed<Vehicle[]>(() => props.salesOrder.vehicles ?? []);
const allVehicleIds = computed<number[]>(() =>
    vehicles.value.map((vehicle: Vehicle) => vehicle.id)
);

const showCreateWorkOrdersModal = ref(false);

const createDocumentForm = useForm<{
    id: number;
}>({
    id: props.salesOrder.id,
});

const openCreateWorkOrdersModal = () => {
    showCreateWorkOrdersModal.value = true;
};

const closeCreateWorkOrdersModal = () => {
    showCreateWorkOrdersModal.value = false;
    createWorkOrderForm.reset();
    vehicleSelectionMap.value = {};
};

const createWorkOrderForm = useForm<{
    ids: number[];
}>({
    ids: [],
});

const createWorkOrderFormRules = {
    ids: {
        required: true,
        type: "array",
    },
};

const handleCreateWorkOrders = () => {
    validate(createWorkOrderForm, createWorkOrderFormRules);

    createWorkOrderForm.post(
        route("sales-orders.work-order", createDocumentForm.id),
        {
            preserveScroll: true,
        }
    );

    closeCreateWorkOrdersModal();
};

const vehicleSelectionMap = ref<Record<number, boolean>>({});

const downPaymentInvoiceCount = computed<number | undefined>(
    () =>
        props.salesOrder.documents?.filter(
            (document: Document) =>
                document.documentable_type ==
                DocumentableType.Sales_order_down_payment
        ).length
);

const completedInvoiceCount = computed<number | undefined>(
    () =>
        props.salesOrder.documents?.filter(
            (document: Document) =>
                document.documentable_type == DocumentableType.Sales_order
        ).length
);

const ableToCreatePurchaseOrders = computed(() => {
    if (vehicles.value.length === 0) {
        return false;
    }

    const firstCompanyId = vehicles.value[0].supplier_company_id;

    if (!firstCompanyId) {
        return false;
    }

    for (const vehicle of props.salesOrder.vehicles ?? []) {
        if (
            vehicle.purchase_order?.length ||
            vehicle.supplier_company_id != firstCompanyId
        ) {
            return false;
        }
    }

    return true;
});

const ableToCreateWorkOrders = computed<boolean>(() => {
    for (const vehicle of props.salesOrder.vehicles ?? []) {
        if (!vehicle.work_order) {
            return true;
        }
    }

    return false;
});

const handleCreateDocument = (documentableType: number) => {
    router.get(
        route("documents.create", {
            documentable_type: documentableType,
            filter: {
                id: createDocumentForm.id,
            },
            customer_company_id: props.salesOrder.customer_company_id,
            customer_id: props.salesOrder.customer_id,
        })
    );
};

watch(
    vehicleSelectionMap,
    (newSelection) => {
        createWorkOrderForm.ids = Object.keys(newSelection)
            .filter((id) => newSelection[Number(id)])
            .map(Number);
    },
    { deep: true }
);

const vehiclesWithoutWorkOrder = computed(() => {
    return props.salesOrder.vehicles.filter(
        (vehicle: Vehicle) => !vehicle.work_order
    );
});

const handleCreatePurchaseOrder = () => {
    router.get(
        route("purchase-orders.create", {
            filter: {
                ids: allVehicleIds.value,
            },
        })
    );
};

const showCreateTransportOrderModal = ref(false);

const openCreateTransportOrderModal = () => {
    showCreateTransportOrderModal.value = true;
};

const closeCreateTransportOrderModal = () => {
    showCreateTransportOrderModal.value = false;
};

const transportOrderFormRules = {
    ids: {
        required: true,
        type: "array",
    },
    transport_type: {
        required: true,
        type: "number",
    },
};

const createTransportOrderForm = useForm<{
    ids: number[];
    transport_type: Enum<typeof TransportType>;
}>({
    ids: allVehicleIds.value,
    transport_type: null!,
});

const allTransportOrders = computed(() =>
    vehicles.value.flatMap((vehicle: Vehicle) => vehicle.transport_orders ?? [])
);

const inboundTransportOrdersCount = computed<number | undefined>(() => {
    const inboundOrders = allTransportOrders.value.filter(
        (order: TransportOrder) =>
            order.transport_type === TransportType.Inbound
    );

    return inboundOrders.length;
});

const outboundTransportOrdersCount = computed<number | undefined>(() => {
    const outboundOrders = allTransportOrders.value.filter(
        (order: TransportOrder) =>
            order.transport_type === TransportType.Outbound
    );

    return outboundOrders.length;
});

const dynamicEnum = omitEnumKeys(TransportType, [
    ...(inboundTransportOrdersCount.value
        ? (["Inbound"] as Array<keyof typeof TransportType>)
        : []),
    ...(outboundTransportOrdersCount.value
        ? (["Outbound"] as Array<keyof typeof TransportType>)
        : []),
]);

const handleCreateTransportOrder = () => {
    validate(createTransportOrderForm, transportOrderFormRules);

    router.get(
        route("transport-orders.create", {
            filter: {
                ids: createTransportOrderForm.ids,
            },
            transport_type: createTransportOrderForm.transport_type,
            vehicle_type: TransportableType.Vehicles,
        })
    );
};
</script>

<template>
    <Section classes="p-4 mt-4 h-fit">
        <div class="font-semibold text-xl sm:text-2xl mb-4">
            {{ __("Create Modules") }}
        </div>

        <div class="w-full flex gap-2 flex-wrap">
            <button
                v-if="
                    $can('create-purchase-order') && ableToCreatePurchaseOrders
                "
                class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                @click="handleCreatePurchaseOrder"
            >
                {{ __("Create") }} {{ __("Purchase Order") }}
            </button>

            <button
                v-if="$can('create-transport-order')"
                class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                @click="openCreateTransportOrderModal"
            >
                {{ __("Create") }} {{ __("Transport Order") }}
            </button>

            <button
                v-if="$can('create-work-order') && ableToCreateWorkOrders"
                class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                @click="openCreateWorkOrdersModal"
            >
                {{ __("Create") }} {{ __("Work Order") }}
            </button>

            <button
                v-if="
                    $can('create-document') &&
                    !downPaymentInvoiceCount &&
                    salesOrder.status ==
                        SalesOrderStatus.Ready_for_down_payment_invoice
                "
                class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                @click="
                    handleCreateDocument(
                        DocumentableType.Sales_order_down_payment
                    )
                "
            >
                {{ __("Create") }} {{ __("Down Payment") }}
                {{ __("Invoice Document") }}
            </button>

            <button
                v-if="
                    $can('create-document') &&
                    !completedInvoiceCount &&
                    salesOrder.status == SalesOrderStatus.Completed
                "
                class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                @click="handleCreateDocument(DocumentableType.Sales_order)"
            >
                {{ __("Create") }} {{ __("Invoice Document") }}
            </button>
        </div>
    </Section>

    <Modal
        :show="showCreateWorkOrdersModal"
        @close="closeCreateWorkOrdersModal"
    >
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Create separate Work Order for every vehicle") }}
        </div>

        <div class="p-3.5 sm:gap-y-2">
            <label>
                {{ __("Choose vehicles") }}
            </label>

            <div class="grid grid-cols-2 mt-3 gap-2.5 items-center">
                <Toggle
                    v-for="(vehicle, index) in vehiclesWithoutWorkOrder"
                    :key="index"
                    v-model="vehicleSelectionMap[vehicle.id]"
                    :label="`#${vehicle.id} ${vehicle.make?.name ?? ''} ${
                        vehicle.vehicle_model?.name ?? ''
                    }`"
                />
            </div>
        </div>

        <ModalSaveButtons
            :processing="createDocumentForm.processing"
            :save-text="__('Create')"
            @cancel="closeCreateWorkOrdersModal"
            @save="handleCreateWorkOrders"
        />
    </Modal>

    <Modal
        :show="showCreateTransportOrderModal"
        @close="closeCreateTransportOrderModal"
    >
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Create Transport Order for this vehicle") }}
        </div>

        <div class="p-3.5 sm:gap-y-2">
            <label for="transport_type">
                {{ __("Transport Type") }}
            </label>
            <Select
                v-model="createTransportOrderForm.transport_type"
                :name="'transport_type'"
                :options="dynamicEnum"
                :placeholder="__('Select Transport Type')"
                class="mb-3.5 w-full"
            />
        </div>

        <ModalSaveButtons
            :processing="createTransportOrderForm.processing"
            :save-text="__('Create')"
            @cancel="closeCreateTransportOrderModal"
            @save="handleCreateTransportOrder"
        />
    </Modal>
</template>
