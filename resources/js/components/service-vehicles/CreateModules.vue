<script setup lang="ts">
import { Link, router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";

import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Section from "@/components/html/Section.vue";
import Modal from "@/components/Modal.vue";
import Select from "@/components/Select.vue";
import { DocumentableType } from "@/enums/DocumentableType";
import { TransportableType } from "@/enums/TransportableType";
import { TransportType } from "@/enums/TransportType";
import { WorkOrderType } from "@/enums/WorkOrderType";
import { workOrderFromVehicleFormRules } from "@/rules/work-order-from-vehicle-form-rules";
import {
    Document,
    Enum,
    ServiceOrder,
    TransportOrder,
    WorkOrder,
} from "@/types";
import { omitEnumKeys } from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    serviceVehicleId: number;
    serviceOrder?: ServiceOrder;
    transportOrders?: TransportOrder[];
    documents?: Document[];
    workOrder?: WorkOrder;
}>();

const handleCreateServiceOrder = () => {
    router.get(
        route("service-orders.create", {
            filter: {
                id: props.serviceVehicleId,
            },
        })
    );
};

const transportOrderFormRules = {
    id: {
        required: true,
        type: "number",
    },
    transport_type: {
        required: true,
        type: "number",
    },
};

const createTransportOrderForm = useForm<{
    id: number | undefined;
    transport_type: Enum<typeof TransportType>;
}>({
    id: props.serviceVehicleId,
    transport_type: null!,
});

const showCreateTransportOrderModal = ref(false);
const showCreateWorkOrderModal = ref(false);

const createWorkOrderForm = useForm<{
    vehicleable_id: number;
    type: number;
}>({
    vehicleable_id: props.serviceVehicleId,
    type: WorkOrderType.Service_vehicle,
});

const openCreateTransportOrderModal = () => {
    showCreateTransportOrderModal.value = true;
};

const closeCreateTransportOrderModal = () => {
    showCreateTransportOrderModal.value = false;
};

const closeCreateWorkOrderModal = () => {
    showCreateWorkOrderModal.value = false;
};

const inboundTransportOrdersCount = computed<number | undefined>(
    () =>
        props.transportOrders?.filter(
            (order: TransportOrder) =>
                order.transport_type == TransportType.Inbound
        ).length
);
const outboundTransportOrdersCount = computed<number | undefined>(
    () =>
        props.transportOrders?.filter(
            (order: TransportOrder) =>
                order.transport_type == TransportType.Outbound
        ).length
);

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
                id: createTransportOrderForm.id,
            },
            transport_type: createTransportOrderForm.transport_type,
            vehicle_type: TransportableType.Service_vehicles,
        })
    );
};

const handleCreateDocument = () => {
    router.get(
        route("documents.create", {
            filter: {
                id: props.serviceVehicleId,
            },
            documentable_type: DocumentableType.Service_vehicle,
        })
    );
};

const openCreateWorkOrderModal = () => {
    showCreateWorkOrderModal.value = true;
};

const handleCreateWorkOrder = () => {
    validate(createWorkOrderForm, workOrderFromVehicleFormRules);

    createWorkOrderForm.post(route("work-orders.create-from-vehicle"), {
        preserveScroll: true,
    });

    closeCreateWorkOrderModal();
};
</script>
<template>
    <Section classes="p-4 mt-4 h-fit">
        <div class="font-semibold text-xl sm:text-2xl mb-4">
            {{ __("Create Modules") }}
        </div>

        <div class="w-full flex gap-2 flex-wrap">
            <button
                v-if="$can('create-work-order') && !workOrder"
                class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                @click="openCreateWorkOrderModal"
            >
                {{ __("Create") }} {{ __("Work Order") }}
            </button>

            <button
                v-if="$can('create-service-order') && !serviceOrder"
                class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                @click="handleCreateServiceOrder"
            >
                {{ __("Create") }} {{ __("Service Order") }}
            </button>

            <button
                v-if="$can('create-transport-order')"
                class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                @click="openCreateTransportOrderModal"
            >
                {{ __("Create") }} {{ __("Transport Order") }}
            </button>

            <button
                v-if="$can('create-document') && !documents?.length"
                class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                @click="handleCreateDocument"
            >
                {{ __("Create") }} {{ __("Invoice") }}
            </button>

            <Link
                v-if="$can('create-crm-company')"
                class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                :href="route('crm.companies.create')"
            >
                {{ __("Create") }} {{ __("Company") }}
            </Link>

            <Link
                v-if="$can('create-crm-user')"
                class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                :href="route('crm.users.create')"
            >
                {{ __("Create") }} {{ __("Crm User") }}
            </Link>
        </div>
    </Section>

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

    <Modal :show="showCreateWorkOrderModal" @close="closeCreateWorkOrderModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Create Work Order for this vehicle") }}
            ?
        </div>

        <ModalSaveButtons
            :processing="createWorkOrderForm.processing"
            :save-text="__('Create')"
            @cancel="closeCreateWorkOrderModal"
            @save="handleCreateWorkOrder"
        />
    </Modal>
</template>
