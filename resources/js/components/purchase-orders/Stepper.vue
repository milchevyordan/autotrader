<script setup lang="ts">
import { useForm, usePage } from "@inertiajs/vue3";
import {computed, ref} from "vue";

import InformationRow from "@/components/html/InformationRow.vue";
import Modal from "@/components/Modal.vue";
import Select from "@/components/Select.vue";
import { Multiselect } from "@/data-table/types";
import { Currency } from "@/enums/Currency";
import { Locale } from "@/enums/Locale";
import { PurchaseOrderStatus } from "@/enums/PurchaseOrderStatus";
import { SupplierOrIntermediary } from "@/enums/SupplierOrIntermediary";
import { setFlashMessages } from "@/globals";
import IconCheck from "@/icons/Check.vue";
import { $can } from "@/plugins/permissions";
import { localeFormRules } from "@/rules/locale-form-rules";
import { __ } from "@/translations";
import {
    Company,
    OrderFiles,
    PurchaseOrderForm,
    UpdateStatusForm,
    Vehicle,
} from "@/types";
import {
    replaceEnumUnderscores,
    findEnumKeyByValue,
    findKeyByValue,
    downLoadFile,
    sendMailPdf,
    booleanRepresentation,
    findCreatedAtStatusDate,
    dateTimeToLocaleString,
    changeStatus,
} from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    purchaseOrder: PurchaseOrderForm;
    selectedVehicleIds: Array<number>;
    vehicles: Vehicle[];
    companies: Multiselect<Company>;
    formIsDirty: boolean;
    files: OrderFiles;
}>();

const updateStatusForm = useForm<UpdateStatusForm>({
    _method: "patch",
    id: props.purchaseOrder.id,
    status: null!,
    locale: usePage().props.locale,
    route: "purchase-orders",
});

const showApproveRejectModal = ref(false);
const showSentToSupplierModal = ref(false);

const openApproveRejectModal = () => {
    if (props.formIsDirty) {
        setFlashMessages({
            error: "You need to save before changing status",
        });

        return;
    }

    showApproveRejectModal.value = true;
};

const statuses = Object.entries(PurchaseOrderStatus)
    .filter(([name]) => isNaN(Number(name)))
    .map(([name, value]) => ({
        name,
        value,
    }));

const checkColoredLink = (index: number) => {
    return index <= props.purchaseOrder.status;
};

const closeApproveRejectModal = () => {
    showApproveRejectModal.value = false;
};

const closeSentToSupplierModal = () => {
    showSentToSupplierModal.value = false;
};

const generatePdf = async () => {
    validate(updateStatusForm, localeFormRules);

    closeSentToSupplierModal();
    await changeStatus(updateStatusForm, PurchaseOrderStatus.Sent_to_supplier);
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
                mailable_type: "App\\Models\\PurchaseOrder",
                mailable_id: props.purchaseOrder.id,
            },
            props.purchaseOrder.supplier?.email ??
                props.purchaseOrder.supplier_company.email,
            props.files.generatedPdf[props.files.generatedPdf.length - 1]
                .unique_name
        );
    }
};

const clickableStatus = (status: string) => {
    const orderStatus =
        PurchaseOrderStatus[status as keyof typeof PurchaseOrderStatus];
    const currentStatus = props.purchaseOrder.status;

    return (
        !updateStatusForm.processing &&
        (orderStatus - currentStatus === 1 ||
            $can("super-change-status") ||
            (!props.purchaseOrder.down_payment &&
                currentStatus ===
                    PurchaseOrderStatus.Uploaded_signed_contract &&
                orderStatus === PurchaseOrderStatus.Completed))
    );
};

const emit = defineEmits(["saveAndSubmit"]);

const saveAndSubmit = () => {
    if (!clickableStatus("Submitted")) {
        return;
    }

    emit("saveAndSubmit");
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
        case "Sent_to_supplier":
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
                PurchaseOrderStatus.Uploaded_signed_contract
            );

            break;

        case "Down_payment_done":
            if (
                !props.purchaseOrder.down_payment ||
                props.purchaseOrder.down_payment_amount <= 0
            ) {
                setFlashMessages({
                    error: "Not saved down payment, need to enter down payment",
                });

                break;
            }

            changeStatus(
                updateStatusForm,
                PurchaseOrderStatus.Down_payment_done
            );

            break;

        case "Completed":
            if (
                !props.purchaseOrder.total_payment_amount ||
                props.purchaseOrder.total_payment_amount <= 0
            ) {
                setFlashMessages({
                    error: __(
                        "Total payment amount is required, when completing the purchase order"
                    ),
                });

                break;
            }

            changeStatus(updateStatusForm, PurchaseOrderStatus.Completed);

            break;

        default:
            changeStatus(
                updateStatusForm,
                PurchaseOrderStatus[status as keyof typeof PurchaseOrderStatus]
            );
            break;
    }
};

const emailToSendTo = computed( () => {
    return (props.purchaseOrder.supplier?.email ??
        props.purchaseOrder.supplier_company.email)
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
                    purchaseOrder.status === PurchaseOrderStatus.Concept &&
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
                    purchaseOrder.status == PurchaseOrderStatus.Submitted &&
                    status.name == 'Rejected' &&
                    $can('approve-purchase-order')
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
                        {{ __("Approve") }}
                    </span>
                </span>
            </div>

            <div
                v-else-if="
                    purchaseOrder.status === PurchaseOrderStatus.Concept &&
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
                    !(
                        purchaseOrder.status === PurchaseOrderStatus.Rejected &&
                        status.value as number >= 4
                    ) &&
                        !(
                            (purchaseOrder.status ===
                                PurchaseOrderStatus.Approved ||
                                purchaseOrder.status >
                                PurchaseOrderStatus.Rejected) &&
                            status.name === 'Rejected'
                        ) &&
                        !(
                            (purchaseOrder.status ===
                                PurchaseOrderStatus.Submitted ||
                                purchaseOrder.status ===
                                PurchaseOrderStatus.Concept) &&
                            (status.name === 'Rejected' ||
                                status.name === 'Approved')
                        ) &&
                        !(
                            !purchaseOrder.down_payment && status.name === 'Down_payment_done'
                        )
                "
                class="status-container px-1 md:px-3 lg:px-5"
            >
                <span
                    class="flex justify-center items-center after:hidden sm:after:mx-2"
                    :class="{
                        'border-blue-200 text-blue-400':
                            status.value <= purchaseOrder.status,
                        'border-gray-200': status.value > purchaseOrder.status,
                        'cursor-pointer':
                            purchaseOrder.status ===
                                PurchaseOrderStatus.Concept &&
                            !$can('submit-purchase-order')
                                ? false
                                : clickableStatus(status.name),
                    }"
                    @click="
                        purchaseOrder.status === PurchaseOrderStatus.Concept &&
                        !$can('submit-purchase-order')
                            ? ''
                            : handleStatusChange(status.name)
                    "
                >
                    <span
                        class="border sm:border-0 rounded-full px-2 py-0.5"
                        :class="
                            status.value <= purchaseOrder.status
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
                                          purchaseOrder.created_at
                                      )
                                    : findCreatedAtStatusDate(
                                          purchaseOrder.statuses,
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
            {{ __("Approve or reject the Purchase order") }}
        </div>

        <hr />

        <InformationRow :title="__('Supplier')">
            {{ findKeyByValue(companies, purchaseOrder.supplier?.company_id) }}
        </InformationRow>

        <InformationRow
            v-if="purchaseOrder.intermediary_id"
            :title="__('Intermediary')"
        >
            {{
                findKeyByValue(
                    companies,
                    purchaseOrder.intermediary?.company_id
                )
            }}
        </InformationRow>

        <InformationRow :title="__('Invoice from')">
            {{
                findEnumKeyByValue(
                    SupplierOrIntermediary,
                    purchaseOrder.document_from_type
                )
            }}
        </InformationRow>

        <InformationRow :title="__('Currency of the PO')">
            {{
                __(
                    replaceEnumUnderscores(
                        findEnumKeyByValue(Currency, purchaseOrder.currency_po)
                    )
                )
            }}
        </InformationRow>

        <InformationRow :title="__('Company Purchaser')">
            {{ purchaseOrder.purchaser?.full_name }}
        </InformationRow>

        <InformationRow :title="__('VAT deposit / Kaution')">
            {{ booleanRepresentation(purchaseOrder.vat_deposit) }}
        </InformationRow>

        <InformationRow
            v-if="purchaseOrder.vat_percentage"
            :title="__('VAT percentage')"
        >
            {{ purchaseOrder.vat_percentage }}%
        </InformationRow>

        <InformationRow :title="__('Down payment')">
            {{ booleanRepresentation(purchaseOrder.down_payment) }}
        </InformationRow>

        <InformationRow
            v-if="purchaseOrder.down_payment_amount"
            :title="__('Down payment amount')"
        >
            {{ purchaseOrder.down_payment_amount }}
        </InformationRow>

        <InformationRow :title="__('Number of vehicles')">
            {{ selectedVehicleIds.length }}
        </InformationRow>

        <InformationRow
            v-if="purchaseOrder.total_purchase_price_eur"
            :title="__('Total purchase value in EUR')"
        >
            <span>{{ purchaseOrder.total_purchase_price_eur }}</span>
        </InformationRow>

        <div
            class="flex justify-center border border-[#E9E7E7] px-3.5 p-3 text-xl font-medium d-flex w-100"
        >
            <button
                :disabled="updateStatusForm.processing"
                class="bg-green-300 px-12 py-2 mx-1 rounded hover:opacity-80 active:scale-95 transition"
                @click="
                    changeStatus(
                        updateStatusForm,
                        PurchaseOrderStatus.Approved
                    );
                    closeApproveRejectModal();
                "
            >
                {{ __("Approve") }}
            </button>

            <button
                :disabled="updateStatusForm.processing"
                class="bg-red-300 px-12 py-2 mx-2 rounded hover:opacity-80 active:scale-95 transition"
                @click="
                    changeStatus(
                        updateStatusForm,
                        PurchaseOrderStatus.Rejected
                    );
                    closeApproveRejectModal();
                "
            >
                {{ __("Reject") }}
            </button>

            <button
                :disabled="updateStatusForm.processing"
                class="bg-yellow-300 px-12 py-2 mx-2 rounded hover:opacity-80 active:scale-95 transition"
                @click="
                    changeStatus(updateStatusForm, PurchaseOrderStatus.Concept);
                    closeApproveRejectModal();
                "
            >
                {{ __("Cancel") }}
            </button>
        </div>
    </Modal>

    <Modal :show="showSentToSupplierModal" @close="closeSentToSupplierModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Sent Purchase order to supplier") }}
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
                v-if="emailToSendTo"
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
</template>
