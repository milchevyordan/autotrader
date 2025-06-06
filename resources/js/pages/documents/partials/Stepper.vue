<script setup lang="ts">
import { useForm, usePage } from "@inertiajs/vue3";
import {computed, ref} from "vue";

import Modal from "@/components/Modal.vue";
import Select from "@/components/Select.vue";
import { DocumentStatus } from "@/enums/DocumentStatus";
import { Locale } from "@/enums/Locale";
import { setFlashMessages } from "@/globals";
import IconCheck from "@/icons/Check.vue";
import { $can } from "@/plugins/permissions";
import { localeFormRules } from "@/rules/locale-form-rules";
import { DatabaseFile, Document, UpdateStatusForm } from "@/types";
import {
    changeStatus,
    dateTimeToLocaleString,
    downLoadFile,
    findCreatedAtStatusDate,
    findLastFileStartingWith,
    replaceEnumUnderscores,
    sendMailPdf,
} from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    document: Document;
    formIsDirty: boolean;
}>();

const updateStatusForm = useForm<UpdateStatusForm>({
    _method: "patch",
    id: props.document.id,
    status: null!,
    locale: usePage().props.locale,
    route: "documents",
});

const showApproveRejectModal = ref(false);
const showSendingPdfModal = ref(false);
const sendPdfStatusAndFile = ref<{
    fileName: string;
    status: DocumentStatus;
}>({
    fileName: "",
    status: null!,
});

const openApproveRejectModal = () => {
    if (props.formIsDirty) {
        setFlashMessages({
            error: "You need to save before changing status",
        });

        return;
    }

    showApproveRejectModal.value = true;
};

const closeApproveRejectModal = () => {
    showApproveRejectModal.value = false;
};

const closeSendingPdfModal = () => {
    showSendingPdfModal.value = false;
};

const clickableStatus = (status: string) => {
    const statusKey: number =
        DocumentStatus[status as keyof typeof DocumentStatus];
    const currentStatus: number = props.document.status;

    return (
        !updateStatusForm.processing &&
        (statusKey - currentStatus === 1 || $can("super-change-status"))
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
        case "Pro_forma":
            showSendingPdfModal.value = true;
            sendPdfStatusAndFile.value.status = DocumentStatus.Pro_forma;
            sendPdfStatusAndFile.value.fileName = "pro-forma";
            break;

        case "Create_invoice":
            showSendingPdfModal.value = true;
            sendPdfStatusAndFile.value.status = DocumentStatus.Create_invoice;
            sendPdfStatusAndFile.value.fileName = "";
            break;

        case "Sent_to_customer":
            showSendingPdfModal.value = true;
            sendPdfStatusAndFile.value.status = DocumentStatus.Sent_to_customer;
            sendPdfStatusAndFile.value.fileName = "invoice";
            break;

        case "Credit_invoice":
            showSendingPdfModal.value = true;
            sendPdfStatusAndFile.value.status = DocumentStatus.Credit_invoice;
            sendPdfStatusAndFile.value.fileName = "credit-invoice";
            break;

        case "Paid":
            if (!props.document.paid_at) {
                setFlashMessages({
                    error: "You need to enter payment date",
                });

                break;
            }

            changeStatus(updateStatusForm, DocumentStatus.Paid);

            break;

        default:
            changeStatus(
                updateStatusForm,
                DocumentStatus[status as keyof typeof DocumentStatus]
            );
            break;
    }
};

const checkColoredLink = (index: number) => {
    return index <= props.document.status;
};

const statuses = Object.entries(DocumentStatus)
    .filter(([name]) => isNaN(Number(name)))
    .map(([name, value]) => ({
        name,
        value: Number(value),
    }));

const sendManually = async () => {
    validate(updateStatusForm, localeFormRules);

    closeSendingPdfModal();

    await changeStatus(updateStatusForm, sendPdfStatusAndFile.value.status);

    const lastFile = findLastFileStartingWith(
        props.document.files,
        sendPdfStatusAndFile.value.fileName
    );

    if (lastFile) {
        downLoadFile((lastFile as DatabaseFile).unique_name);
    }
};

const createInvoice = async () => {
    validate(updateStatusForm, localeFormRules);

    closeSendingPdfModal();

    await changeStatus(updateStatusForm, sendPdfStatusAndFile.value.status);
};

const sendViaTheSystem = async () => {
    validate(updateStatusForm, localeFormRules);

    closeSendingPdfModal();
    await changeStatus(updateStatusForm, sendPdfStatusAndFile.value.status);

    const lastFile = findLastFileStartingWith(
        props.document.files,
        sendPdfStatusAndFile.value.fileName
    );

    if (lastFile) {
        sendMailPdf(
            {
                mailable_type: "App\\Models\\Document",
                mailable_id: props.document.id,
            },
            props.document.customer?.email ??
                props.document.customer_company.email,
            (lastFile as DatabaseFile).unique_name
        );
    }
};

const emailToSendTo = computed(() => {
    return (props.document.customer?.email ??
        props.document.customer_company.email);
})
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
                    document.status === DocumentStatus.Pro_forma &&
                    status.name == 'Rejected' &&
                    $can('approve-document')
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
                    document.status === DocumentStatus.Concept &&
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
                        document.status === DocumentStatus.Rejected &&
                        status.value >= 4
                    ) &&
                    !(
                        (document.status === DocumentStatus.Approved ||
                            document.status > DocumentStatus.Rejected) &&
                        status.name === 'Rejected'
                    ) &&
                    !(
                        (document.status === DocumentStatus.Pro_forma ||
                            document.status === DocumentStatus.Concept) &&
                        (status.name === 'Rejected' ||
                            status.name === 'Approved')
                    )
                "
                class="status-container px-1 md:px-3 lg:px-5"
            >
                <span
                    class="flex justify-center items-center after:hidden sm:after:mx-2"
                    :class="{
                        'border-blue-200 text-blue-400':
                            status.value <= document.status,
                        'border-gray-200': status.value > document.status,
                        'cursor-pointer': clickableStatus(status.name),
                    }"
                    @click="handleStatusChange(status.name)"
                >
                    <span
                        class="border sm:border-0 rounded-full px-2 py-0.5"
                        :class="
                            status.value <= document.status
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
                        <span>{{ replaceEnumUnderscores(status.name) }}</span>
                        <span class="text-xs">
                            {{
                                status.value == 1
                                    ? dateTimeToLocaleString(
                                          document.created_at
                                      )
                                    : findCreatedAtStatusDate(
                                          document.statuses,
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
            {{ __("Approve or reject the Invoice") }}
        </div>

        <hr />

        <div
            class="flex justify-center border border-[#E9E7E7] px-3.5 p-3 text-xl font-medium d-flex w-100"
        >
            <button
                :disabled="updateStatusForm.processing"
                class="bg-green-300 px-12 py-2 mx-1 rounded hover:opacity-80 active:scale-95 transition"
                @click="
                    changeStatus(updateStatusForm, DocumentStatus.Approved);
                    closeApproveRejectModal();
                "
            >
                {{ __("Approve") }}
            </button>

            <button
                :disabled="updateStatusForm.processing"
                class="bg-red-300 px-12 py-2 mx-2 rounded hover:opacity-80 active:scale-95 transition"
                @click="
                    changeStatus(updateStatusForm, DocumentStatus.Rejected);
                    closeApproveRejectModal();
                "
            >
                {{ __("Reject") }}
            </button>

            <button
                :disabled="updateStatusForm.processing"
                class="bg-yellow-300 px-12 py-2 mx-2 rounded hover:opacity-80 active:scale-95 transition"
                @click="
                    changeStatus(updateStatusForm, DocumentStatus.Concept);
                    closeApproveRejectModal();
                "
            >
                {{ __("Cancel") }}
            </button>
        </div>
    </Modal>

    <Modal :show="showSendingPdfModal" @close="closeSendingPdfModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            <span
                v-if="
                    sendPdfStatusAndFile.status != DocumentStatus.Create_invoice
                "
            >
                {{ __("Send invoice to customer") }}
            </span>
            <span v-else>
                {{ __("Create Invoice") }}
            </span>
        </div>

        <div
            v-if="
                sendPdfStatusAndFile.status != DocumentStatus.Sent_to_customer
            "
            class="container p-4"
        >
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
            class="flex justify-center border border-[#E9E7E7] px-3.5 p-3 text-xl font-medium d-flex w-100 space-x-2"
        >
            <button
                v-if="
                    sendPdfStatusAndFile.status != DocumentStatus.Create_invoice
                "
                :disabled="updateStatusForm.processing"
                class="bg-[#008FE3] text-white px-12 py-2 rounded hover:opacity-80 active:scale-95 transition"
                @click="sendManually"
            >
                {{ __("Send manually") }}
            </button>

            <button
                v-if="emailToSendTo && sendPdfStatusAndFile.status != DocumentStatus.Create_invoice"
                :disabled="updateStatusForm.processing"
                class="bg-yellow-400 text-white px-12 py-2 rounded hover:opacity-80 active:scale-95 transition"
                :title="__('To: ') + emailToSendTo"
                @click="sendViaTheSystem"
            >
                {{ __("Send via the system") }}
            </button>

            <div
                v-else-if="sendPdfStatusAndFile.status != DocumentStatus.Create_invoice"
                class="bg-red-400 text-white px-12 py-2 mx-2 rounded active:scale-95 transition"
            >
                {{ __("No email found") }}
            </div>

            <button
                v-if="sendPdfStatusAndFile.status == DocumentStatus.Create_invoice"
                :disabled="updateStatusForm.processing"
                class="bg-[#00A793] text-white hover:opacity-80 px-12 py-2 rounded active:scale-95 transition"
                @click="createInvoice"
            >
                {{ __("Save") }}
            </button>

            <button
                class="bg-[#F0F0F0] px-12 py-2 rounded hover:opacity-80 active:scale-95 transition"
                @click="closeSendingPdfModal"
            >
                {{ __("Close") }}
            </button>
        </div>
    </Modal>
</template>
