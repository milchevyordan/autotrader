<script setup lang="ts">
import { useForm, usePage } from "@inertiajs/vue3";
import {computed, ref} from "vue";

import InformationRow from "@/components/html/InformationRow.vue";
import Modal from "@/components/Modal.vue";
import Select from "@/components/Select.vue";
import { Locale } from "@/enums/Locale";
import { TransportOrderStatus } from "@/enums/TransportOrderStatus";
import { TransportType } from "@/enums/TransportType";
import { setFlashMessages } from "@/globals";
import IconCheck from "@/icons/Check.vue";
import { $can } from "@/plugins/permissions";
import { localeFormRules } from "@/rules/locale-form-rules";
import { DatabaseFile, TransportOrderForm, UpdateStatusForm } from "@/types";
import {
    dateTimeToLocaleString,
    downLoadFile,
    findCreatedAtStatusDate,
    replaceEnumUnderscores,
    sendMailPdf,
    changeStatus,
    findLastFileStartingWith,
} from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    transportOrder: TransportOrderForm;
    formIsDirty: boolean;
    selectedTransportableIds: number[];
    generatedPdf?: DatabaseFile[];
    cmrWaybillFiles?: DatabaseFile[];
}>();

const updateStatusForm = useForm<UpdateStatusForm>({
    _method: "patch",
    id: props.transportOrder.id,
    status: null!,
    locale: usePage().props.locale,
    route: "transport-orders",
});

const clickableStatus = (status: string): boolean => {
    const statusKey: number =
        TransportOrderStatus[status as keyof typeof TransportOrderStatus];
    const differance: number =
        (statusKey === TransportOrderStatus.Issued && props.transportOrder.status == TransportOrderStatus.Concept) ? 2 : 1;

    return (
        !updateStatusForm.processing &&
        (statusKey - props.transportOrder.status == differance ||
            $can("super-change-status"))
    );
};

const showSentToTransportCompanyModal = ref<boolean>(false);

const closeSentToTransportCompanyModal = () => {
    showSentToTransportCompanyModal.value = false;
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
        case "Offer_requested":
            if (props.selectedTransportableIds.length == 0) {
                setFlashMessages({
                    error: "Not selected any vehicles, need to select at least one Vehicle",
                });

                break;
            }

            showSentToTransportCompanyModal.value = true;

            break;

        case "Issued":
            if (
                !props.transportOrder.transport_company_use &&
                props.selectedTransportableIds.length == 0
            ) {
                setFlashMessages({
                    error: "Not selected any vehicles, need to select at least one Vehicle",
                });

                break;
            }

            changeStatus(updateStatusForm, TransportOrderStatus.Issued);
            break;

        case "Cmr_waybill":
            if (!props.cmrWaybillFiles?.length) {
                setFlashMessages({
                    error: "Not uploaded crm waybill, need to upload crm waybill",
                });

                break;
            }

            changeStatus(updateStatusForm, TransportOrderStatus.Cmr_waybill);
            break;

        default:
            changeStatus(
                updateStatusForm,
                TransportOrderStatus[
                    status as keyof typeof TransportOrderStatus
                ]
            );
            break;
    }
};

const checkColoredLink = (index: number) => {
    return index <= props.transportOrder.status;
};

const generatePdf = async () => {
    validate(updateStatusForm, localeFormRules);

    closeSentToTransportCompanyModal();
    await changeStatus(updateStatusForm, TransportOrderStatus.Offer_requested);
};

const sendManually = async () => {
    await generatePdf();

    const lastFile = findLastFileStartingWith(
        props.generatedPdf,
        "transport-order"
    );

    if (lastFile) {
        downLoadFile((lastFile as DatabaseFile).unique_name);
    }
};

const sendViaTheSystem = async () => {
    await generatePdf();

    const lastFile = findLastFileStartingWith(
        props.generatedPdf,
        "transport-order"
    );
    if (lastFile) {
        sendMailPdf(
            {
                mailable_type: "App\\Models\\TransportOrder",
                mailable_id: props.transportOrder.id,
            },
            props.transportOrder.transporter?.email ??
                props.transportOrder.transport_company.email,
            (lastFile as DatabaseFile).unique_name
        );
    }
};

const statuses = Object.entries(TransportOrderStatus)
    .filter(([name]) => isNaN(Number(name)))
    .map(([name, value]) => ({
        name,
        value,
    }));

const emailToSendTo = computed( () => {
    return (props.transportOrder.transporter?.email ??
        props.transportOrder.transport_company.email)
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
                    transportOrder.status === TransportOrderStatus.Concept &&
                    status.value === TransportOrderStatus.Offer_requested &&
                    transportOrder.transport_company_use
                "
                class="status-container px-1 md:px-3 lg:px-5"
            >
                <span
                    class="flex justify-center items-center after:hidden sm:after:mx-2 border-gray-200 cursor-pointer"
                    @click="handleStatusChange(status.name)"
                >
                    <span
                        class="border sm:border-0 rounded-full px-2 py-0.5 border-gray-200"
                    >
                        {{ __("Send offer") }}
                    </span>
                </span>
            </div>

            <div
                v-else-if="
                    !(
                        status.value === TransportOrderStatus.Offer_requested &&
                        !transportOrder.transport_company_use
                    )
                "
                class="status-container px-1 md:px-3 lg:px-5"
            >
                <span
                    class="flex justify-center items-center after:hidden sm:after:mx-2"
                    :class="{
                        'border-blue-200 text-blue-400':
                            status.value <= transportOrder.status,
                        'border-gray-200': status.value > transportOrder.status,
                        'cursor-pointer': clickableStatus(status.name),
                    }"
                    @click="handleStatusChange(status.name)"
                >
                    <span
                        class="border sm:border-0 rounded-full px-2 py-0.5"
                        :class="
                            status.value <= transportOrder.status
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
                                          transportOrder.created_at
                                      )
                                    : findCreatedAtStatusDate(
                                          transportOrder.statuses,
                                          status.value as number
                                      )
                            }}
                        </span>
                    </span>
                </span>
            </div>
        </div>
    </div>

    <Modal
        :show="showSentToTransportCompanyModal"
        @close="closeSentToTransportCompanyModal"
    >
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Send offer to transport supplier") }}
        </div>

        <hr />

        <div v-if="transportOrder.transport_type == TransportType.Outbound">
            <InformationRow
                v-if="transportOrder.pick_up_location_id"
                :title="__('Pick up location')"
            >
                {{ transportOrder.pick_up_location?.address }}
            </InformationRow>

            <InformationRow
                v-if="transportOrder.pick_up_location_free_text"
                :title="__('Pick up location free text')"
            >
                {{ transportOrder.pick_up_location_free_text }}
            </InformationRow>

            <InformationRow :title="__('Number of vehicles at pick up')">
                {{ selectedTransportableIds.length }}
            </InformationRow>

            <InformationRow
                v-if="transportOrder.pick_up_notes"
                :title="__('Pick up notes')"
            >
                {{ transportOrder.pick_up_notes }}
            </InformationRow>

            <InformationRow
                v-if="transportOrder.pick_up_after_date"
                :title="__('Pick up after')"
            >
                {{ dateTimeToLocaleString(transportOrder.pick_up_after_date) }}
            </InformationRow>

            <InformationRow
                v-if="transportOrder.delivery_notes"
                :title="__('Delivery notes')"
            >
                {{ transportOrder.delivery_notes }}
            </InformationRow>

            <InformationRow
                v-if="transportOrder.deliver_before_date"
                :title="__('Deliver before')"
            >
                {{ dateTimeToLocaleString(transportOrder.deliver_before_date) }}
            </InformationRow>

            <InformationRow
                v-if="transportOrder.planned_delivery_date"
                :title="__('Planned delivery date')"
            >
                {{
                    dateTimeToLocaleString(transportOrder.planned_delivery_date)
                }}
            </InformationRow>
        </div>

        <div v-else-if="transportOrder.transport_type == TransportType.Inbound">
            <InformationRow
                v-if="transportOrder.pick_up_notes"
                :title="__('Pick up notes')"
            >
                {{ transportOrder.pick_up_notes }}
            </InformationRow>

            <InformationRow
                v-if="transportOrder.pick_up_after_date"
                :title="__('Pick up after')"
            >
                {{ dateTimeToLocaleString(transportOrder.pick_up_after_date) }}
            </InformationRow>

            <InformationRow
                v-if="transportOrder.delivery_location_id"
                :title="__('Delivery location')"
            >
                {{ transportOrder.delivery_location?.address }}
            </InformationRow>

            <InformationRow
                v-if="transportOrder.delivery_location_free_text"
                :title="__('Delivery location free text')"
            >
                {{ transportOrder.delivery_location_free_text }}
            </InformationRow>

            <InformationRow
                v-if="transportOrder.delivery_notes"
                :title="__('Delivery notes')"
            >
                {{ transportOrder.delivery_notes }}
            </InformationRow>

            <InformationRow
                v-if="transportOrder.deliver_before_date"
                :title="__('Deliver before')"
            >
                {{ dateTimeToLocaleString(transportOrder.deliver_before_date) }}
            </InformationRow>

            <InformationRow
                v-if="transportOrder.planned_delivery_date"
                :title="__('Planned delivery date')"
            >
                {{
                    dateTimeToLocaleString(transportOrder.planned_delivery_date)
                }}
            </InformationRow>
        </div>

        <div v-else>
            <InformationRow
                v-if="transportOrder.pick_up_location_id"
                :title="__('Pick up location')"
            >
                {{ transportOrder.pick_up_location?.address }}
            </InformationRow>

            <InformationRow
                v-if="transportOrder.pick_up_location_free_text"
                :title="__('Pick up location free text')"
            >
                {{ transportOrder.pick_up_location_free_text }}
            </InformationRow>

            <InformationRow
                v-if="transportOrder.pick_up_notes"
                :title="__('Pick up notes')"
            >
                {{ transportOrder.pick_up_notes }}
            </InformationRow>

            <InformationRow
                v-if="transportOrder.pick_up_after_date"
                :title="__('Pick up after')"
            >
                {{ dateTimeToLocaleString(transportOrder.pick_up_after_date) }}
            </InformationRow>

            <InformationRow
                v-if="transportOrder.delivery_location_id"
                :title="__('Delivery location')"
            >
                {{ transportOrder.delivery_location?.address }}
            </InformationRow>

            <InformationRow
                v-if="transportOrder.delivery_location_free_text"
                :title="__('Delivery location free text')"
            >
                {{ transportOrder.delivery_location_free_text }}
            </InformationRow>

            <InformationRow
                v-if="transportOrder.delivery_notes"
                :title="__('Delivery notes')"
            >
                {{ transportOrder.delivery_notes }}
            </InformationRow>

            <InformationRow
                v-if="transportOrder.deliver_before_date"
                :title="__('Deliver before')"
            >
                {{ dateTimeToLocaleString(transportOrder.deliver_before_date) }}
            </InformationRow>

            <InformationRow
                v-if="transportOrder.planned_delivery_date"
                :title="__('Planned delivery date')"
            >
                {{
                    dateTimeToLocaleString(transportOrder.planned_delivery_date)
                }}
            </InformationRow>

            <InformationRow :title="__('Number of vehicles')">
                {{ selectedTransportableIds.length }}
            </InformationRow>
        </div>

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
                class="bg-[#F0F0F0] px-12 py-2 mx-2 rounded hover:opacity-80 active:scale-95 transition"
                @click="closeSentToTransportCompanyModal"
            >
                {{ __("Close") }}
            </button>
        </div>
    </Modal>
</template>
