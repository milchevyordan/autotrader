<script setup lang="ts">
import { router, useForm, usePage } from "@inertiajs/vue3";
import {computed, ref} from "vue";

import InformationRow from "@/components/html/InformationRow.vue";
import Modal from "@/components/Modal.vue";
import Select from "@/components/Select.vue";
import { Multiselect } from "@/data-table/types";
import { DocumentableType } from "@/enums/DocumentableType";
import { ImportOrOriginType } from "@/enums/ImportOrOriginType";
import { Locale } from "@/enums/Locale";
import { SalesOrderStatus } from "@/enums/SalesOrderStatus";
import { setFlashMessages } from "@/globals";
import IconCheck from "@/icons/Check.vue";
import { $can } from "@/plugins/permissions";
import { localeFormRules } from "@/rules/locale-form-rules";
import { __ } from "@/translations";
import {
    Company,
    SalesOrderFiles,
    SalesOrderForm,
    UpdateStatusForm,
    Vehicle,
} from "@/types";
import {
    booleanRepresentation,
    dateTimeToLocaleString,
    downLoadFile,
    findEnumKeyByValue,
    findKeyByValue,
    findCreatedAtStatusDate,
    replaceEnumUnderscores,
    sendMailPdf,
    changeStatus,
} from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    salesOrder: SalesOrderForm;
    selectedVehicleIds: Array<number>;
    vehicles: Vehicle[];
    companies: Multiselect<Company>;
    formIsDirty: boolean;
    files: SalesOrderFiles;
    canCreateDownPaymentInvoice: boolean;
}>();

const updateStatusForm = useForm<UpdateStatusForm>({
    _method: "patch",
    id: props.salesOrder.id,
    status: null!,
    locale: usePage().props.locale,
    route: "sales-orders",
});

const showApproveRejectModal = ref(false);
const showSentToSupplierModal = ref(false);
const showCreateDocumentModal = ref(false);

const emit = defineEmits(["saveAndSubmit"]);

const saveAndSubmit = () => {
    if (!clickableStatus("Submitted")) {
        return;
    }

    emit("saveAndSubmit");
};

const openApproveRejectModal = () => {
    if (props.formIsDirty) {
        setFlashMessages({
            error: "You need to save before changing status",
        });

        return;
    }

    showApproveRejectModal.value = true;
};

const generatePdf = async () => {
    validate(updateStatusForm, localeFormRules);

    closeSentToSupplierModal();
    await changeStatus(updateStatusForm, SalesOrderStatus.Sent_to_buyer);
};

const sendManually = async () => {
    await generatePdf();

    if (props.files.generatedPdf?.[props.files.generatedPdf.length - 1]) {
        downLoadFile(
            props.files.generatedPdf[props.files.generatedPdf.length - 1]
                .unique_name
        );
    }
};

const sendViaTheSystem = async () => {
    await generatePdf();

    if (props.files.generatedPdf?.[props.files.generatedPdf.length - 1]) {
        sendMailPdf(
            {
                mailable_type: "App\\Models\\SalesOrder",
                mailable_id: props.salesOrder.id,
            },
            props.salesOrder.customer?.email ??
                props.salesOrder.customer_company.email,
            props.files.generatedPdf[props.files.generatedPdf.length - 1]
                .unique_name
        );
    }
};

const closeApproveRejectModal = () => {
    showApproveRejectModal.value = false;
};

const closeSentToSupplierModal = () => {
    showSentToSupplierModal.value = false;
};

const closeCreateDocumentModal = () => {
    showCreateDocumentModal.value = false;
};

const clickableStatus = (status: string) => {
    const orderStatus =
        SalesOrderStatus[status as keyof typeof SalesOrderStatus];
    const currentStatus = props.salesOrder.status;

    return (
        !updateStatusForm.processing &&
        (orderStatus - currentStatus === 1 ||
            $can("super-change-status") ||
            (!props.salesOrder.down_payment &&
                currentStatus === SalesOrderStatus.Uploaded_signed_contract &&
                orderStatus === SalesOrderStatus.Completed))
    );
};

const handleStatusChange = (status: string) => {
    if (!clickableStatus(status)) {
        return;
    }

    if (props.formIsDirty) {
        setFlashMessages({
            error: "You need to save before changing status",
        });

        return;
    }

    switch (status) {
        case "Sent_to_buyer":
            showSentToSupplierModal.value = true;
            break;

        case "Uploaded_signed_contract":
            if (!props.files.contractSignedFiles.length) {
                setFlashMessages({
                    error: "Not uploaded signed contract, need to upload signed contract",
                });

                break;
            }

            changeStatus(
                updateStatusForm,
                SalesOrderStatus.Uploaded_signed_contract
            );

            break;

        case "Ready_for_down_payment_invoice":
            showCreateDocumentModal.value = true;

            break;

        case "Down_payment_done":
            if (
                !props.salesOrder.down_payment ||
                props.salesOrder.down_payment_amount <= 0
            ) {
                setFlashMessages({
                    error: "Not saved down payment, need to enter down payment",
                });

                break;
            }

            changeStatus(updateStatusForm, SalesOrderStatus.Down_payment_done);

            break;

        default:
            changeStatus(
                updateStatusForm,
                SalesOrderStatus[status as keyof typeof SalesOrderStatus]
            );
            break;
    }
};

const checkColoredLink = (index: number) => {
    return index <= props.salesOrder.status;
};

const statuses = Object.entries(SalesOrderStatus)
    .filter(([name]) => isNaN(Number(name)))
    .map(([name, value]) => ({
        name,
        value,
    }));

const handleDocumentCreate = async () => {
    await changeStatus(
        updateStatusForm,
        SalesOrderStatus.Ready_for_down_payment_invoice
    );

    router.get(
        route("documents.create", {
            filter: {
                id: props.salesOrder.id,
            },
            documentable_type: DocumentableType.Sales_order_down_payment,
            payment_condition: props.salesOrder.payment_condition,
            customer_company_id: props.salesOrder.customer_company_id,
            customer_id: props.salesOrder.customer_id,
        })
    );
};

const emailToSendTo = computed( () => {
    return (props.salesOrder.customer?.email ??
        props.salesOrder.customer_company?.email)
});
</script>

<template>
    <div
        class="flex flex-wrap w-full text-sm font-medium text-center text-gray-500 sm:text-base rounded-lg border border-[#E9E7E7] shadow-2xl bg-white mb-10 py-4 sm:py-5 px-4 sticky top-24 z-20"
    >
        <div
            v-for="(status, index) in statuses"
            :key="index"
            class="flex items-center"
        >
            <div
                v-if="
                    salesOrder.status === SalesOrderStatus.Concept &&
                    status.name == 'Submitted'
                "
                class="div status-container"
            >
                <span
                    class="flex justify-center items-center after:hidden sm:after:mx-2 border-gray-200"
                    :class="{
                        'cursor-pointer': clickableStatus('Submitted'),
                    }"
                    @click="saveAndSubmit"
                >
                    <span
                        class="border sm:border-0 rounded-full px-2 py-0.5 border-gray-200"
                    >
                        {{ __("Save & Submit") }}
                    </span>
                </span>
            </div>

            <div
                v-else-if="
                    salesOrder.status <
                        SalesOrderStatus.Uploaded_signed_contract &&
                    status.name == 'Uploaded_signed_contract'
                "
                class="div status-container"
            >
                <span
                    class="flex justify-center items-center after:hidden sm:after:mx-2 border-gray-200"
                    :class="{
                        'cursor-pointer': clickableStatus(
                            'Uploaded_signed_contract'
                        ),
                    }"
                    @click="handleStatusChange(status.name)"
                >
                    <span
                        class="border sm:border-0 rounded-full px-2 py-0.5 border-gray-200"
                    >
                        {{ __("Upload signed contract") }}
                    </span>
                </span>
            </div>

            <div
                v-else-if="
                    salesOrder.status === SalesOrderStatus.Submitted &&
                    status.name == 'Rejected' &&
                    $can('approve-sales-order')
                "
                class="div status-container"
            >
                <span
                    class="flex justify-center items-center after:hidden sm:after:mx-2 border-gray-200 cursor-pointer"
                    @click="openApproveRejectModal"
                >
                    <span
                        class="border sm:border-0 rounded-full px-2 py-0.5 border-gray-200"
                    >
                        {{ __("Approve/Reject") }}
                    </span>
                </span>
            </div>

            <div
                v-else-if="
                    salesOrder.status === SalesOrderStatus.Concept &&
                    status.name == 'Rejected'
                "
                class="div status-container"
            >
                <span
                    class="flex justify-center items-center after:hidden sm:after:mx-2 border-gray-200"
                >
                    <span
                        class="border sm:border-0 rounded-full px-2 py-0.5 border-gray-200"
                    >
                        {{ __("Approved/Rejected") }}
                    </span>
                </span>
            </div>

            <div
                v-else-if="
                    !canCreateDownPaymentInvoice &&
                    (status.name == 'Ready_for_down_payment_invoice' ||
                        status.name == 'Down_payment_invoice_sent' ||
                        status.name == 'Down_payment_done')
                "
                class="div status-container"
            >
                <div
                    v-if="
                        !canCreateDownPaymentInvoice &&
                        status.name === 'Ready_for_down_payment_invoice'
                    "
                    class="status-container bg-red-700 text-white p-2 rounded"
                >
                    {{ __("Submitted Purchase order required") }}
                </div>
            </div>

            <div
                v-else-if="
                    !(
                        salesOrder.status === SalesOrderStatus.Rejected &&
                        status.value as number >= 4
                    ) &&
                        !(
                            (salesOrder.status === SalesOrderStatus.Approved ||
                                salesOrder.status > SalesOrderStatus.Rejected) &&
                            status.name === 'Rejected'
                        ) &&
                        !(
                            (salesOrder.status === SalesOrderStatus.Submitted ||
                                salesOrder.status === SalesOrderStatus.Concept) &&
                            (status.name === 'Rejected' ||
                                status.name === 'Approved')
                        ) &&
                        !(
                            !salesOrder.down_payment && (status.name == 'Ready_for_down_payment_invoice' ||
                                status.name === 'Down_payment_invoice_sent' ||
                                status.name === 'Down_payment_done')
                        )
                "
                class="status-container px-1 md:px-3 lg:px-5"
            >
                <span
                    class="flex justify-center items-center after:hidden sm:after:mx-2"
                    :class="{
                        'border-blue-200 text-blue-400':
                            status.value <= salesOrder.status,
                        'border-gray-200': status.value > salesOrder.status,
                        'cursor-pointer':
                            salesOrder.status === SalesOrderStatus.Concept &&
                            !$can('submit-sales-order')
                                ? false
                                : clickableStatus(status.name),
                    }"
                    @click="
                        salesOrder.status === SalesOrderStatus.Concept &&
                        !$can('submit-sales-order')
                            ? ''
                            : handleStatusChange(status.name)
                    "
                >
                    <span
                        class="border sm:border-0 rounded-full px-2 py-0.5"
                        :class="
                            status.value <= salesOrder.status
                                ? 'border-blue-200'
                                : 'border-gray-200'
                        "
                    >
                        <span v-if="checkColoredLink(status.value as number)">
                            <IconCheck classes="w-5 h-5" stroke="2.5" />
                        </span>
                    </span>

                    <span
                        class="hidden md:inline-flex flex-col whitespace-nowrap"
                    >
                        <span>{{
                            replaceEnumUnderscores(status.name, true)
                        }}</span>
                        <span class="text-xs">
                            {{
                                status.value == 1
                                    ? dateTimeToLocaleString(
                                          salesOrder.created_at
                                      )
                                    : findCreatedAtStatusDate(
                                          salesOrder.statuses,
                                          status.value as number
                                      )
                            }}
                        </span>
                    </span>
                </span>
            </div>
        </div>
    </div>

    <Modal :show="showApproveRejectModal" @close="closeApproveRejectModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Approve or reject the Sales order") }}
        </div>

        <InformationRow
            v-if="salesOrder.customer_company_id"
            :title="__('Customer')"
        >
            {{ findKeyByValue(companies, salesOrder.customer_company_id) }}
        </InformationRow>

        <InformationRow
            v-if="salesOrder.customer_id"
            :title="__('Customer contact person')"
        >
            {{ salesOrder?.customer?.full_name }}
        </InformationRow>

        <InformationRow :title="__('Sales Person')">
            {{ salesOrder.seller?.full_name }}
        </InformationRow>

        <InformationRow
            v-if="salesOrder.type_of_sale"
            :title="__('Type of sale')"
        >
            {{
                replaceEnumUnderscores(
                    findEnumKeyByValue(
                        ImportOrOriginType,
                        salesOrder.type_of_sale
                    )
                )
            }}
        </InformationRow>

        <InformationRow :title="__('Down payment')">
            {{ booleanRepresentation(salesOrder.down_payment) }}
        </InformationRow>

        <InformationRow
            v-if="salesOrder.down_payment_amount"
            :title="__('Down payment amount')"
        >
            {{ salesOrder.down_payment_amount }}
        </InformationRow>

        <InformationRow :title="__('Number of vehicles')">
            {{ selectedVehicleIds.length }}
        </InformationRow>

        <InformationRow
            v-if="salesOrder.total_sales_price"
            :title="__('Total sale price')"
        >
            {{ salesOrder.total_sales_price }}
        </InformationRow>
        <hr />
        <div
            class="flex justify-center border border-[#E9E7E7] px-3.5 p-3 text-xl font-medium d-flex w-100"
        >
            <button
                :disabled="updateStatusForm.processing"
                class="bg-green-300 px-12 py-2 mx-1 rounded hover:opacity-80 active:scale-95 transition"
                @click="
                    changeStatus(updateStatusForm, SalesOrderStatus.Approved);
                    closeApproveRejectModal();
                "
            >
                {{ __("Approve") }}
            </button>

            <button
                :disabled="updateStatusForm.processing"
                class="bg-red-300 px-12 py-2 mx-2 rounded hover:opacity-80 active:scale-95 transition"
                @click="
                    changeStatus(updateStatusForm, SalesOrderStatus.Rejected);
                    closeApproveRejectModal();
                "
            >
                {{ __("Reject") }}
            </button>

            <button
                :disabled="updateStatusForm.processing"
                class="bg-yellow-300 px-12 py-2 mx-2 rounded hover:opacity-80 active:scale-95 transition"
                @click="
                    changeStatus(updateStatusForm, SalesOrderStatus.Concept);
                    closeApproveRejectModal();
                "
            >
                {{ __("Cancel") }}
            </button>
        </div>
    </Modal>

    <Modal :show="showSentToSupplierModal" @close="closeSentToSupplierModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Sent Sales order to buyer") }}
        </div>

        <hr />

        <div class="container p-4">
            <label for="locale">
                {{ __("Language") }}
                <span class="text-red-500"> *</span>
            </label>
            <Select
                v-model="updateStatusForm.locale"
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
                :disabled="updateStatusForm.processing"
                class="bg-[#008FE3] text-white px-12 py-2 mx-1 rounded hover:opacity-80 active:scale-95 transition"
                @click="sendManually"
            >
                {{ __("Send manually") }}
            </button>

            <button
                v-if="emailToSendTo && salesOrder.customer_company"
                :disabled="updateStatusForm.processing"
                class="bg-yellow-400 text-white px-12 py-2 mx-2 rounded hover:opacity-80 active:scale-95 transition"
                :title="__('To: ') + emailToSendTo"
                @click="sendViaTheSystem"
            >
                {{ __("Send via the system") }}
            </button>

            <div
                v-else
                class="bg-red-400 text-white px-12 py-2 mx-2 rounded active:scale-95 transition"
            >
                {{ __("No email found") }}
            </div>

            <button
                class="bg-[#F0F0F0] px-12 py-2 rounded hover:opacity-80 active:scale-95 transition"
                @click="closeSentToSupplierModal"
            >
                {{ __("Close") }}
            </button>
        </div>
    </Modal>

    <Modal :show="showCreateDocumentModal" @close="closeCreateDocumentModal">
        <div
            class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium d-flex w-100"
        >
            <button
                :disabled="updateStatusForm.processing"
                class="bg-[#008FE3] text-white px-12 py-2 mx-1 rounded hover:opacity-80 active:scale-95 transition"
                @click="
                    changeStatus(
                        updateStatusForm,
                        SalesOrderStatus.Ready_for_down_payment_invoice
                    );
                    closeCreateDocumentModal();
                "
            >
                {{ __("Change status") }}
            </button>

            <button
                :disabled="updateStatusForm.processing"
                class="bg-yellow-400 text-white px-12 py-2 mx-2 rounded hover:opacity-80 active:scale-95 transition"
                @click="handleDocumentCreate"
            >
                {{ __("Change status & Create invoice") }}
            </button>

            <div class="col-span-2 flex justify-end gap-3 mt-2 pt-1 pb-3 px-4">
                <button
                    class="bg-[#F0F0F0] px-12 py-2 rounded hover:opacity-80 active:scale-95 transition"
                    @click="closeCreateDocumentModal"
                >
                    {{ __("Close") }}
                </button>
            </div>
        </div>
    </Modal>
</template>
