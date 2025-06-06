<script setup lang="ts">
import { Link, router, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";

import Input from "@/components/html/Input.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Section from "@/components/html/Section.vue";
import Modal from "@/components/Modal.vue";
import Select from "@/components/Select.vue";
import { DocumentableType } from "@/enums/DocumentableType";
import { Locale } from "@/enums/Locale";
import { SalesOrderStatus } from "@/enums/SalesOrderStatus";
import { TransportableType } from "@/enums/TransportableType";
import { TransportType } from "@/enums/TransportType";
import { VehicleStock } from "@/enums/VehicleStock";
import { WorkOrderType } from "@/enums/WorkOrderType";
import { setFlashMessages } from "@/globals";
import { baseFormRules } from "@/rules/base-form-rules";
import { localeFormRules } from "@/rules/locale-form-rules";
import { workOrderFromVehicleFormRules } from "@/rules/work-order-from-vehicle-form-rules";
import { workflowFormRules } from "@/rules/workflow-form-rules";
import {
    Enum,
    Document,
    PurchaseOrder,
    SalesOrder,
    TransportOrder,
    VehicleForm,
    WorkOrder,
    DatabaseFile,
    WorkflowProcess,
    Multiselect,
} from "@/types";
import { downLoadFile, findLastFileStartingWith, omitEnumKeys } from "@/utils";
import { validate } from "@/validations";

const model: string = "App\\Models\\Vehicle";

const props = defineProps<{
    vehicle?: VehicleForm;
    workflowProcesses?: Multiselect<WorkflowProcess>;
    purchaseOrder?: PurchaseOrder[];
    salesOrder?: SalesOrder[];
    workOrder?: WorkOrder;
    transportOrders?: TransportOrder[];
    documents?: Document[];
    internalFiles?: DatabaseFile[];
    transferVehicleToken?: string;
    disabledWorkflowCreate?: boolean
}>();

const showCreateWorkflowModal = ref(false);
const showCreateTransferLinkModal = ref(false);
const showCreateSalesOrderModal = ref(false);
const showCreateWorkOrderModal = ref(false);
const showCreateTransportOrderModal = ref(false);
const showCreateQuoteModal = ref(false);

const createForm = useForm<{
    id: number;
    locale?: Enum<typeof Locale>;
}>({
    id: props.vehicle?.id,
    locale: usePage().props.locale,
});

const createWorkflowForm = useForm<{
    workflow_process_class: string;
    vehicleable_type: string;
    vehicleable_id: number;
}>({
    workflow_process_class: null!,
    vehicleable_type: model,
    vehicleable_id: props.vehicle?.id,
});

const downloadQuoteSinglePdf = async () => {
    validate(createForm, localeFormRules);

    await new Promise<void>((resolve, reject) => {
        createForm.post(route("vehicles.generate-quote", createForm.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                resolve();
            },
            onError: () => {
                reject();
            },
        });
        closeCreateQuoteModal();
        createForm.reset();
    });

    const lastFile = findLastFileStartingWith(props.internalFiles, "quote");

    if (lastFile) {
        downLoadFile((lastFile as DatabaseFile).unique_name);
    }
};

const closeCreateQuoteModal = () => {
    showCreateQuoteModal.value = false;
};

const createTransportOrderForm = useForm<{
    id: number;
    transport_type: Enum<typeof TransportType>;
}>({
    id: props.vehicle?.id,
    transport_type: null!,
});

const openCreateWorkflowModal = async () => {
    if (!props.workflowProcesses) {
        await new Promise((resolve, reject) => {
            router.reload({
                only: ["workflowProcesses"],
                onSuccess: resolve,
                onError: reject,
            });
        });
    }

    showCreateWorkflowModal.value = true;
};

const transferLink = ref<string>(null!);
const transferLinkForm = useForm<{
    email: string | undefined;
}>({
    email: props.salesOrder?.length
        ? props.salesOrder[0]?.customer?.email
        : null!,
});

const copyTransferVehicleToken = async () => {
    validate(transferLinkForm, {
        email: {
            required: false,
            type: "email",
        },
    });
    await new Promise((resolve, reject) => {
        router.reload({
            data: { email: transferLinkForm.email },
            only: ["transferVehicleToken"],
            onSuccess: resolve,
            onError: reject,
        });
    });

    closeTransferVehicleTokenModal();

    transferLink.value = route("vehicles.transfer", props.transferVehicleToken);

    try {
        await navigator.clipboard.writeText(transferLink.value);
        setFlashMessages({
            success: "Link copied to clipboard.",
        });
    } catch (err) {
        setFlashMessages({
            error: "Error copying to clipboard.",
        });
    }
};

const closeTransferVehicleTokenModal = () => {
    showCreateTransferLinkModal.value = false;
    transferLinkForm.reset();
};

const closeCreateWorkflowModal = () => {
    showCreateWorkflowModal.value = false;
};

const openCreateSalesOrderModal = () => {
    showCreateSalesOrderModal.value = true;
};

const closeCreateSalesOrderModal = () => {
    showCreateSalesOrderModal.value = false;
};

const openCreateWorkOrderModal = () => {
    showCreateWorkOrderModal.value = true;
};

const closeCreateWorkOrderModal = () => {
    showCreateWorkOrderModal.value = false;
};

const openCreateTransportOrderModal = () => {
    showCreateTransportOrderModal.value = true;
};

const closeCreateTransportOrderModal = () => {
    showCreateTransportOrderModal.value = false;
};

const handleCreateSalesOrder = () => {
    validate(createForm, baseFormRules);

    createForm.post(route("vehicles.sales-order", createForm.id), {
        preserveScroll: true,
    });

    closeCreateSalesOrderModal();
};

const createWorkOrderForm = useForm<{
    vehicleable_id: number;
    type: number;
}>({
    vehicleable_id: props.vehicle?.id,
    type: WorkOrderType.Vehicle,
});

const handleCreateWorkOrder = () => {
    validate(createWorkOrderForm, workOrderFromVehicleFormRules);

    createWorkOrderForm.post(route("work-orders.create-from-vehicle"), {
        preserveScroll: true,
    });

    closeCreateWorkOrderModal();
};
const handleCreateQuote = () => {
    router.get(
        route("quotes.create", {
            filter: { id: createForm.id },
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

const handleCreateWorkflow = () => {
    validate(createWorkflowForm, workflowFormRules);

    createWorkflowForm.post(
        route("workflows.store", createWorkflowForm.vehicleable_id as number),
        {
            preserveScroll: true,
        }
    );
    closeCreateWorkflowModal();
};

const handleCreateTransportOrder = () => {
    validate(createTransportOrderForm, transportOrderFormRules);

    router.get(
        route("transport-orders.create", {
            filter: {
                id: createTransportOrderForm.id,
            },
            transport_type: createTransportOrderForm.transport_type,
            vehicle_type: TransportableType.Vehicles,
        })
    );
};

const handleCreatePurchaseOrder = () => {
    router.get(
        route("purchase-orders.create", {
            filter: {
                id: createForm.id,
            },
            supplier_company_id: props.vehicle?.supplier_company_id,
            supplier_id: props.vehicle?.supplier_id,
        })
    );
};

const handleCreateDocument = () => {
    router.get(
        route("documents.create", {
            documentable_type: DocumentableType.Vehicle,
            filter: {
                id: createForm.id,
            },
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
                    $can('create-purchase-order') &&
                    !purchaseOrder?.length &&
                    vehicle?.supplier_company_id
                "
                class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                @click="handleCreatePurchaseOrder"
            >
                {{ __("Create") }} {{ __("Purchase Order") }}
            </button>

            <button
                v-if="$can('create-workflow') && !vehicle?.workflow && !disabledWorkflowCreate"
                class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                @click="openCreateWorkflowModal"
            >
                {{ __("Create") }} {{ __("Workflow") }}
            </button>

            <button
                v-if="$can('create-sales-order') && !salesOrder?.length"
                class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                @click="openCreateSalesOrderModal"
            >
                {{ __("Create") }} {{ __("Sales Order") }}
            </button>

            <button
                v-if="$can('create-work-order') && !workOrder"
                class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                @click="openCreateWorkOrderModal"
            >
                {{ __("Create") }} {{ __("Work Order") }}
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

            <button
                v-if="
                    $can('create-quote') && vehicle?.stock != VehicleStock.Sold
                "
                class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                @click="handleCreateQuote"
            >
                {{ __("Create") }} {{ __("Quote") }}
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

            <button
                class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                @click="showCreateQuoteModal = true"
            >
                {{ __("Download Offer") }}
            </button>

            <button
                v-if="
                    $can('transfer-vehicle') &&
                    salesOrder?.length &&
                    salesOrder[0].status >= SalesOrderStatus.Approved
                "
                class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                @click="showCreateTransferLinkModal = true"
            >
                {{ __("Copy Transfer Vehicle Link") }}
            </button>

            <div class="text-wrap break-words max-w-full">
                {{ transferLink }}
            </div>
        </div>
    </Section>

    <Modal :show="showCreateWorkflowModal" @close="closeCreateWorkflowModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Create Workflow for this vehicle") }}
            ?
        </div>

        <div class="border border-[#E9E7E7] px-3.5 p-3">
            <label for="">
                {{ __("Workflow") }} {{ __("Type") }}
                <span class="text-red-500"> *</span>
            </label>

            <Select
                v-model="createWorkflowForm.workflow_process_class"
                :name="'workflow_process_class'"
                :options="workflowProcesses"
                :placeholder="__('Workflow')"
                class="w-full mb-3.5 sm:mb-0"
            />
        </div>

        <ModalSaveButtons
            :processing="createWorkflowForm.processing"
            :save-text="__('Create')"
            @cancel="closeCreateWorkflowModal"
            @save="handleCreateWorkflow"
        />
    </Modal>

    <Modal
        :show="showCreateTransferLinkModal"
        @close="closeTransferVehicleTokenModal"
    >
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Create transfer link for this vehicle") }}
            ?
        </div>

        <div class="border border-[#E9E7E7] px-3.5 p-3">
            <label for="email">
                {{ __("Email") }}
            </label>

            <Input
                v-model="transferLinkForm.email"
                :name="'email'"
                type="email"
                :placeholder="__('Send link to email if you would like')"
                class="mb-3.5 sm:mb-0"
            />
        </div>

        <ModalSaveButtons
            :processing="transferLinkForm.processing"
            :save-text="
                transferLinkForm.email ? __('Send Mail') : __('Copy Link')
            "
            @cancel="closeTransferVehicleTokenModal"
            @save="copyTransferVehicleToken"
        />
    </Modal>

    <Modal
        :show="showCreateSalesOrderModal"
        @close="closeCreateSalesOrderModal"
    >
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Create Sales Order for this vehicle") }}
            ?
        </div>

        <ModalSaveButtons
            :processing="createForm.processing"
            :save-text="__('Create')"
            @cancel="closeCreateSalesOrderModal"
            @save="handleCreateSalesOrder"
        />
    </Modal>

    <Modal :show="showCreateWorkOrderModal" @close="closeCreateWorkOrderModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Create Work Order for this vehicle") }}
            ?
        </div>

        <ModalSaveButtons
            :processing="createForm.processing"
            :save-text="__('Create')"
            @cancel="closeCreateWorkOrderModal"
            @save="handleCreateWorkOrder"
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

    <Modal :show="showCreateQuoteModal" @close="closeCreateQuoteModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Generate and Download Quote Pdf") }}
        </div>

        <hr />

        <div class="container p-4">
            <label for="locale">
                {{ __("Language") }}
                <span class="text-red-500"> *</span>
            </label>
            <Select
                v-model="createForm.locale"
                :name="'locale'"
                :options="Locale"
                :capitalize="true"
                :placeholder="__('Language')"
                class="w-full mb-3.5 sm:mb-0"
            />
        </div>

        <div
            class="flex justify-center border border-[#E9E7E7] px-3.5 p-3 text-xl font-medium d-flex w-100"
        >
            <button
                :disabled="createForm.processing"
                class="bg-[#008FE3] text-white px-12 py-2 mx-1 rounded hover:opacity-80 active:scale-95 transition"
                @click="downloadQuoteSinglePdf"
            >
                {{ __("Download Quote Pdf") }}
            </button>

            <button
                class="bg-[#F0F0F0] px-12 py-2 rounded hover:opacity-80 active:scale-95 transition"
                @click="closeCreateQuoteModal"
            >
                {{ __("Close") }}
            </button>
        </div>
    </Modal>
</template>
