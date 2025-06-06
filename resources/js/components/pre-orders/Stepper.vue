<script setup lang="ts">
import { useForm, usePage } from "@inertiajs/vue3";
import {computed, ref} from "vue";

import InformationRow from "@/components/html/InformationRow.vue";
import InputIcon from "@/components/html/InputIcon.vue";
import Modal from "@/components/Modal.vue";
import Select from "@/components/Select.vue";
import { Multiselect } from "@/data-table/types";
import { Currency } from "@/enums/Currency";
import { Locale } from "@/enums/Locale";
import { PreOrderStatus } from "@/enums/PreOrderStatus";
import { SupplierOrIntermediary } from "@/enums/SupplierOrIntermediary";
import { setFlashMessages } from "@/globals";
import IconCarFront from "@/icons/CarFront.vue";
import IconCheck from "@/icons/Check.vue";
import { $can } from "@/plugins/permissions";
import { duplicateVehicleFormRules } from "@/rules/duplicate-vehicle-form-rules";
import { localeFormRules } from "@/rules/locale-form-rules";
import {
    Company,
    Form,
    OrderFiles,
    PreOrderForm,
    UpdateStatusForm,
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
import {__} from "@/translations";

const props = defineProps<{
    preOrder: PreOrderForm;
    companies: Multiselect<Company>;
    formIsDirty: boolean;
    files: OrderFiles;
}>();

const updateStatusForm = useForm<UpdateStatusForm>({
    _method: "patch",
    id: props.preOrder.id,
    status: null!,
    locale: usePage().props.locale,
    route: "pre-orders",
});

const createVehiclesForm = useForm<Form>({
    id: props.preOrder.id,
    duplications: null!,
    first_registration_date: true,
    kilometers: true,
    specific_exterior_color: true,
    sales_price_total: true,
    option: true,
});

const showApproveRejectModal = ref(false);
const showVehicleCreationModal = ref(false);
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

const generatePdf = async () => {
    validate(updateStatusForm, localeFormRules);

    closeSentToSupplierModal();
    await changeStatus(updateStatusForm, PreOrderStatus.Sent_to_supplier);
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
                mailable_type: "App\\Models\\PreOrder",
                mailable_id: props.preOrder.id,
            },
            props.preOrder.supplier?.email ??
                props.preOrder.supplier_company.email,
            props.files.generatedPdf[props.files.generatedPdf.length - 1]
                .unique_name
        );
    }
};

const closeApproveRejectModal = () => {
    showApproveRejectModal.value = false;
};

const closeVehicleCreationModal = () => {
    showVehicleCreationModal.value = false;
    createVehiclesForm.reset();
};

const closeSentToSupplierModal = () => {
    showSentToSupplierModal.value = false;
};

const createVehicles = () => {
    validate(createVehiclesForm, duplicateVehicleFormRules);

    createVehiclesForm.post(route("pre-orders.vehicles"), {
        preserveScroll: true,
        onSuccess: () => {
            createVehiclesForm.reset();
        },
        onError: () => {},
    });

    closeVehicleCreationModal();
};

const clickableStatus = (status: string) => {
    const statusNumber = PreOrderStatus[status as keyof typeof PreOrderStatus];
    const currentStatusNumber = props.preOrder.status;

    return (
        (!updateStatusForm.processing &&
            (statusNumber === currentStatusNumber + 1 ||
                $can("super-change-status"))) ||
        (!props.preOrder.down_payment &&
            currentStatusNumber === PreOrderStatus.Uploaded_signed_contract &&
            statusNumber === PreOrderStatus.Files_creation)
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
        case "Submitted":
            if (!props.preOrder.pre_order_vehicle_id) {
                setFlashMessages({
                    error: "Not selected any vehicles, need to select Vehicle",
                });

                break;
            }

            changeStatus(updateStatusForm, PreOrderStatus.Submitted);

            break;

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
                PreOrderStatus.Uploaded_signed_contract
            );

            break;

        case "Down_payment_done":
            if (
                !props.preOrder.down_payment ||
                props.preOrder.down_payment_amount <= 0
            ) {
                setFlashMessages({
                    error: "Not saved down payment, need to enter down payment",
                });

                break;
            }

            changeStatus(updateStatusForm, PreOrderStatus.Down_payment_done);

            break;

        case "Files_creation":
            showVehicleCreationModal.value = true;
            createVehiclesForm.duplications = props.preOrder.amount_of_vehicles;
            break;

        default:
            changeStatus(
                updateStatusForm,
                PreOrderStatus[status as keyof typeof PreOrderStatus]
            );
            break;
    }
};

const checkColoredLink = (index: number) => {
    return index <= props.preOrder.status;
};

const statuses = Object.entries(PreOrderStatus)
    .filter(([name]) => isNaN(Number(name)))
    .map(([name, value]) => ({
        name,
        value,
    }));

const emailToSendTo = computed( () => {
    return (props.preOrder.supplier?.email ??
        props.preOrder.supplier_company.email)
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
                    preOrder.status === PreOrderStatus.Submitted &&
                    status.name == 'Rejected' &&
                    $can('approve-pre-order')
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
                    preOrder.status === PreOrderStatus.Concept &&
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
                        preOrder.status === PreOrderStatus.Rejected &&
                        status.value as number >= 4
                    ) &&
                        !(
                            (preOrder.status === PreOrderStatus.Approved ||
                                preOrder.status > PreOrderStatus.Rejected) &&
                            status.name === 'Rejected'
                        ) &&
                        !(
                            (preOrder.status === PreOrderStatus.Submitted || preOrder.status === PreOrderStatus.Concept) &&
                            (status.name === 'Rejected' ||
                                status.name === 'Approved')
                        ) &&
                        !(
                            !preOrder.down_payment && status.name == 'Down_payment_done'
                        )
                "
                class="status-container px-1 md:px-3 lg:px-5"
            >
                <span
                    class="flex justify-center items-center after:hidden sm:after:mx-2"
                    :class="{
                        'border-blue-200 text-blue-400':
                            status.value <= preOrder.status,
                        'border-gray-200': status.value > preOrder.status,
                        'cursor-pointer':
                            preOrder.status === PreOrderStatus.Concept &&
                            !$can('submit-pre-order')
                                ? false
                                : clickableStatus(status.name),
                    }"
                    @click="
                        preOrder.status === PreOrderStatus.Concept &&
                        !$can('submit-pre-order')
                            ? ''
                            : handleStatusChange(status.name)
                    "
                >
                    <span
                        class="border sm:border-0 rounded-full px-2 py-0.5"
                        :class="
                            status.value <= preOrder.status
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
                                          preOrder.created_at
                                      )
                                    : findCreatedAtStatusDate(
                                          preOrder.statuses,
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
            {{ __("Approve or reject the Pre order") }}
        </div>

        <hr />

        <InformationRow :title="__('Supplier')">
            {{ findKeyByValue(companies, preOrder.supplier?.company_id) }}
        </InformationRow>

        <InformationRow
            v-if="preOrder.intermediary_id"
            :title="__('Intermediary')"
        >
            {{ findKeyByValue(companies, preOrder.intermediary?.company_id) }}
        </InformationRow>

        <InformationRow :title="__('Invoice from')">
            {{
                findEnumKeyByValue(
                    SupplierOrIntermediary,
                    preOrder.document_from_type
                )
            }}
        </InformationRow>

        <InformationRow :title="__('Currency of the PO')">
            {{
                __(
                    replaceEnumUnderscores(
                        findEnumKeyByValue(Currency, preOrder.currency_po)
                    )
                )
            }}
        </InformationRow>

        <InformationRow :title="__('Purchasing entity')">
            {{ findKeyByValue(companies, preOrder.purchaser?.company_id) }}
        </InformationRow>

        <InformationRow :title="__('VAT deposit / Kaution')">
            {{ booleanRepresentation(preOrder.vat_deposit) }}
        </InformationRow>

        <InformationRow
            v-if="preOrder.vat_percentage"
            :title="__('VAT percentage')"
        >
            {{ preOrder.vat_percentage }}%
        </InformationRow>

        <InformationRow :title="__('Down payment')">
            {{ booleanRepresentation(preOrder.down_payment) }}
        </InformationRow>

        <InformationRow
            v-if="preOrder.down_payment_amount"
            :title="__('Down payment amount')"
        >
            {{ preOrder.down_payment_amount }}
        </InformationRow>

        <InformationRow
            v-if="preOrder.total_purchase_price_eur"
            :title="__('Total purchase value in EUR')"
        >
            <span>{{ preOrder.total_purchase_price_eur }}</span>
        </InformationRow>

        <div
            class="flex justify-center border border-[#E9E7E7] px-3.5 p-3 text-xl font-medium d-flex w-100"
        >
            <button
                :disabled="updateStatusForm.processing"
                class="bg-green-300 px-12 py-2 mx-1 rounded hover:opacity-80 active:scale-95 transition"
                @click="
                    changeStatus(updateStatusForm, PreOrderStatus.Approved);
                    closeApproveRejectModal();
                "
            >
                {{ __("Approve") }}
            </button>

            <button
                :disabled="updateStatusForm.processing"
                class="bg-red-300 px-12 py-2 mx-2 rounded hover:opacity-80 active:scale-95 transition"
                @click="
                    changeStatus(updateStatusForm, PreOrderStatus.Rejected);
                    closeApproveRejectModal();
                "
            >
                {{ __("Reject") }}
            </button>

            <button
                :disabled="updateStatusForm.processing"
                class="bg-yellow-300 px-12 py-2 mx-2 rounded hover:opacity-80 active:scale-95 transition"
                @click="
                    changeStatus(updateStatusForm, PreOrderStatus.Concept);
                    closeApproveRejectModal();
                "
            >
                {{ __("Cancel") }}
            </button>
        </div>
    </Modal>

    <Modal :show="showSentToSupplierModal" @close="closeSentToSupplierModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Sent Pre order to supplier") }}
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
                class="bg-[#008FE3] text-white px-12 py-2 mx-2 rounded hover:opacity-80 active:scale-95 transition"
                @click="sendManually"
            >
                {{ __("Send manually") }}
            </button>

            <button
                v-if="emailToSendTo"
                :disabled="updateStatusForm.processing"
                :title="__('To: ') + emailToSendTo"
                class="bg-yellow-400 text-white px-12 py-2 mx-2 rounded hover:opacity-80 active:scale-95 transition"
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
                :disabled="updateStatusForm.processing"
                class="bg-[#F0F0F0] px-12 py-2 mx-2 rounded hover:opacity-80 active:scale-95 transition"
                @click="
                    changeStatus(updateStatusForm, PreOrderStatus.Concept);
                    closeApproveRejectModal();
                "
            >
                {{ __("Cancel") }}
            </button>
        </div>
    </Modal>

    <Modal :show="showVehicleCreationModal" @close="closeVehicleCreationModal">
        <div class="modal-content p-5 w-100">
            <label for="amount_vehicles">
                {{ __("Amount of vehicles") }}
                <span class="text-red-500"> *</span>
            </label>

            <InputIcon
                v-model="createVehiclesForm.duplications"
                :name="'duplications'"
                type="number"
                :placeholder="__('Amount of vehicles')"
                class="my-3"
            >
                <template #secondIcon>
                    <IconCarFront stroke="1.2" class="text-[#909090]" />
                </template>
            </InputIcon>

            <div
                class="border-top border-top-[#E9E7E7] text-xl font-medium d-flex"
            >
                <div class="content">
                    <button
                        :disabled="createVehiclesForm.processing"
                        class="bg-green-300 px-12 py-2 mx-1 rounded hover:opacity-80 active:scale-95 transition"
                        @click="createVehicles"
                    >
                        {{ __("Create") }}
                    </button>
                </div>
            </div>
        </div>
    </Modal>
</template>
