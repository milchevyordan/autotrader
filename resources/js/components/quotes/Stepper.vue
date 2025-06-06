<script setup lang="ts">
import { router, useForm, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import { computed } from "vue";

import VehicleSummary from "@/common/VehicleSummary.vue";
import AdditionalInformationAndConditions from "@/components/AdditionalInformationAndConditions.vue";
import AdditionalServices from "@/components/html/AdditionalServices.vue";
import DatePicker from "@/components/html/DatePicker.vue";
import ItemsComponent from "@/components/html/Items.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Modal from "@/components/Modal.vue";
import SummaryFinancialInformation from "@/components/quote-invitations/SummaryFinancialInformation.vue";
import EmailText from "@/components/quotes/EmailText.vue";
import GeneralInformation from "@/components/quotes/GeneralInformation.vue";
import Select from "@/components/Select.vue";
import SelectMultiple from "@/components/SelectMultiple.vue";
import { Multiselect } from "@/data-table/types";
import { Locale } from "@/enums/Locale";
import { QuoteStatus } from "@/enums/QuoteStatus";
import { setFlashMessages } from "@/globals";
import IconCheck from "@/icons/Check.vue";
import { $can } from "@/plugins/permissions";
import { Item, UpdateStatusForm } from "@/types";
import {
    Company,
    OwnerProps,
    Quote,
    QuoteForm,
    QuoteInvitationForm,
    ServiceLevel,
    User,
    UserGroup,
} from "@/types";
import {
    changeStatus,
    convertCurrencyToUnits,
    dateTimeToLocaleString,
    findCreatedAtStatusDate,
    replaceEnumUnderscores,
} from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    quote: Quote;
    form: QuoteForm;
    formIsDirty: boolean;
    userGroups: Multiselect<UserGroup>;
    allCustomers: Multiselect<User>;
    serviceLevels?: Multiselect<ServiceLevel>;
    companies: Multiselect<Company>;
    ownerProps: OwnerProps;
    vehiclesCount: number;
}>();

const showApproveRejectModal = ref<boolean>(false);
const showSendQuoteModal = ref<boolean>(false);
const showReserveModal = ref<boolean>(false);
const showAcceptCustomerModal = ref<boolean>(false);

const updateStatusForm = useForm<UpdateStatusForm>({
    _method: "patch",
    id: props.quote.id,
    status: null!,
    route: "quotes",
});

const quoteInvitationForm = useForm<QuoteInvitationForm>({
    quote_id: props.quote.id,
    customer_ids: [],
    user_group_id: null!,
    locale: usePage().props.locale,
});

const quoteReservationForm = useForm<{
    _method: string;
    reservation_customer_id: number;
    reservation_until: string;
}>({
    _method: "patch",
    reservation_customer_id: props.quote.reservation_customer_id,
    reservation_until: props.quote.reservation_until,
});

const acceptQuoteForm = useForm<{
    _method: string;
    customer_id?: number;
}>({
    _method: "patch",
    customer_id: props.quote.customer?.id,
});

const salesPriceServiceItemsPerVehicleUnits = computed(
    () =>
        Number(
            convertCurrencyToUnits(props.form.total_sales_price_service_items)
        ) / props.vehiclesCount
);

const allItemsAndAdditionals = computed(() =>
    props.form.items
        .concat(props.form.additional_services)
        .some((item: Item) => item.in_output)
);

const openApproveRejectModal = () => {
    if (props.formIsDirty) {
        setFlashMessages({
            error: "You need to save before changing status",
        });

        return;
    }

    showApproveRejectModal.value = true;
};

const openSendQuoteModal = () => {
    showSendQuoteModal.value = true;
};

const closeSendQuoteModal = () => {
    showSendQuoteModal.value = false;
    quoteInvitationForm.reset();
};

const closeApproveRejectModal = () => {
    showApproveRejectModal.value = false;
};

const openReserveModal = () => {
    if (props.quote.status == QuoteStatus.Sent) {
        showReserveModal.value = true;
    }
};

const closeReserveModal = () => {
    quoteReservationForm.reservation_customer_id = null!;
    quoteReservationForm.reservation_until = null!;
    showReserveModal.value = false;
};

const openAcceptCustomerModal = () => {
    showAcceptCustomerModal.value = true;
};

const closeAcceptCustomerModal = () => {
    showAcceptCustomerModal.value = false;
};

const clickableStatus = (status: string) => {
    const statusNumber = QuoteStatus[status as keyof typeof QuoteStatus];
    const currentStatus = props.quote.status;

    return (
        !updateStatusForm.processing &&
        (statusNumber - currentStatus == 1 ||
            $can("super-change-status") ||
            (statusNumber == QuoteStatus.Stop_quote &&
                props.quote.status == QuoteStatus.Sent) ||
            (statusNumber == QuoteStatus.Approved &&
                props.quote.status == QuoteStatus.Accepted_by_client) ||
            (currentStatus == QuoteStatus.Sent &&
                statusNumber == QuoteStatus.Reserve))
    );
};

const quoteIsReserved = computed(() => {
    return (
        props.quote.reservation_customer_id &&
        props.quote.reservation_until &&
        new Date(props.quote.reservation_until) > new Date()
    );
});

const handleStatusChange = async (status: string) => {
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
            if (!props.form.vehicleIds.length) {
                setFlashMessages({
                    error: "Not selected any vehicles, need to select Vehicle",
                });

                break;
            }

            await changeStatus(updateStatusForm, QuoteStatus.Submitted);

            break;

        case "Reserve":
            openReserveModal();

            break;

        case "Accepted_by_client":
            openAcceptCustomerModal();

            break;

        case "Created_sales_order":
            if (props.quote.sales_order_id) {
                setFlashMessages({
                    error: "Sales order already made, look in connected to the modules",
                });

                break;
            }

            router.post(route("quotes.sales-order", props.quote.id));

            break;

        default:
            await changeStatus(
                updateStatusForm,
                QuoteStatus[status as keyof typeof QuoteStatus]
            );
            break;
    }
};

const checkColoredLink = (index: number) => {
    return index <= props.quote.status;
};

const statuses = Object.entries(QuoteStatus)
    .filter(([name]) => isNaN(Number(name)))
    .map(([name, value]) => ({
        name,
        value,
    }));

const quoteInvitationFormRules = {
    quote_id: {
        required: true,
        type: "number",
    },
    user_group_id: {
        required: false,
        type: "number",
    },
    customer_ids: {
        required: false,
        type: "array",
    },
    locale: {
        required: true,
        type: "number",
    },
};

const quoteReservationFormRules = {
    reservation_customer_id: {
        required: true,
        type: "number",
    },
    reservation_until: {
        required: true,
        type: "date",
    },
};

const sendQuote = () => {
    validate(quoteInvitationForm, quoteInvitationFormRules);

    return new Promise<void>((resolve, reject) => {
        quoteInvitationForm.post(route("quote-invitations.store"), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                resolve();
            },
            onError: () => {
                reject();
            },
        });
        closeSendQuoteModal();
    });
};

const handleQuoteReservation = () => {
    validate(quoteReservationForm, quoteReservationFormRules);

    return new Promise<void>((resolve, reject) => {
        quoteReservationForm.post(route("quotes.reserve", props.quote.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                resolve();
            },
            onError: () => {
                reject();
            },
        });
        closeReserveModal();
    });
};

const handleCancelReservation = () => {
    validate(quoteReservationForm, quoteReservationFormRules);

    return new Promise<void>((resolve, reject) => {
        quoteReservationForm.post(
            route("quotes.cancel-reservation", props.quote.id),
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    resolve();
                },
                onError: () => {
                    reject();
                },
            }
        );
        closeReserveModal();
    });
};

const handleQuoteAcceptance = async () => {
    validate(acceptQuoteForm, {
        customer_id: {
            required: true,
            type: "number",
        },
    });

    return new Promise<void>((resolve, reject) => {
        acceptQuoteForm.post(route("quotes.accept-customer", props.quote.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                closeAcceptCustomerModal();
                resolve();
            },
            onError: () => {
                reject();
            },
        });
    });
};
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
                    quote.status == QuoteStatus.Submitted &&
                    status.name == 'Sent' &&
                    $can('create-quote-invitation')
                "
                class="div status-container"
            >
                <span
                    class="flex justify-center items-center after:hidden sm:after:mx-2 border-gray-200 cursor-pointer"
                    @click="openSendQuoteModal"
                >
                    <span
                        class="border sm:border-0 rounded-full px-2 py-0.5 border-gray-200"
                    >
                        {{ __("Send Quote") }}
                    </span>
                </span>
            </div>

            <!-- Adds Approve/Reject status if status Reserve -->
            <div
                v-if="
                    quote.status == QuoteStatus.Accepted_by_client &&
                    status.name == 'Rejected' &&
                    $can('approve-quote')
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
            <!-- / Adds Approve/Reject status before rejected -->

            <!-- Adds Reserved status -->
            <div
                v-if="
                    quote.status <= QuoteStatus.Sent && status.name == 'Reserve'
                "
                class="div mx-4"
            >
                <span
                    class="flex justify-center items-center after:hidden sm:after:mx-2"
                    :class="{
                        'border-blue-200 text-blue-400': quoteIsReserved,
                        'cursor-pointer': quote.status == QuoteStatus.Sent,
                    }"
                    @click="openReserveModal"
                >
                    <span
                        class="border sm:border-0 rounded-full px-2 py-0.5"
                        :class="
                            quoteIsReserved
                                ? 'border-blue-200'
                                : 'border-gray-200'
                        "
                    >
                        <IconCheck
                            v-if="quoteIsReserved"
                            classes="w-5 h-5 border-blue-200 text-blue-400 mx-4"
                            stroke="2.5"
                        />
                    </span>

                    <span
                        class="hidden md:inline-flex flex-col whitespace-nowrap"
                    >
                        <span>{{
                            quoteIsReserved ? __("Reserved") : __("Reserve")
                        }}</span>
                        <span class="text-xs">
                            <span v-if="quoteIsReserved">
                                {{ __("Until") }} :

                                {{ quote.reservation_until }}
                            </span>
                        </span>
                    </span>
                </span>
            </div>
            <!-- / Adds Reserved status -->

            <!-- If status rejected - DONT show the other statuses -->
            <div
                v-if="
                    quote.status <= QuoteStatus.Sent &&
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
            <!-- / If status rejected - DONT show the other statuses -->

            <div
                v-else-if="
                    !(
                        quote.status == QuoteStatus.Rejected &&
                        status.value as number > QuoteStatus.Rejected
                    ) &&
                        !(
                            quote.status >= QuoteStatus.Reserve &&
                            status.value as number == QuoteStatus.Stop_quote
                        ) &&
                        !(status.name == 'Rejected' && quote.status != QuoteStatus.Rejected) &&
                        !(status.name == 'Approved' && quote.status < QuoteStatus.Approved) &&
                        !(status.name == 'Sent' && quote.status == QuoteStatus.Submitted) &&
                        status.name != 'Reserve' &&
                        !(status.name == 'Closed' && quote.status != QuoteStatus.Closed) &&
                        !(
                            quote.status == QuoteStatus.Closed &&
                            status.value as number > QuoteStatus.Closed
                        )

                "
                class="status-container px-1 md:px-3 lg:px-5"
            >
                <span
                    class="flex justify-center items-center after:hidden sm:after:mx-2"
                    :class="{
                        'border-blue-200 text-blue-400':
                            status.value as number <= quote.status,
                        'border-gray-200': status.value as number > quote.status,
                        'cursor-pointer': clickableStatus(status.name),
                    }"
                    @click="handleStatusChange(status.name)"
                >
                    <span
                        class="border sm:border-0 rounded-full px-2 py-0.5"
                        :class="
                            status.value as number <= quote.status
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
                                    ? dateTimeToLocaleString(quote.created_at)
                                    : findCreatedAtStatusDate(
                                          quote.statuses,
                                          status.value as number
                                      )
                            }}
                        </span>
                    </span>
                </span>
            </div>
        </div>
    </div>

    <Modal :show="showReserveModal" @close="closeReserveModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Reserve the quote") }}
        </div>

        <hr />

        <div class="container p-4">
            <label for="reservation_until">
                {{ __("Until") }}
                <span class="text-red-500"> *</span>
            </label>
            <DatePicker
                v-model="quoteReservationForm.reservation_until"
                name="reservation_until"
                class="mb-3.5"
            />

            <label for="reservation_customer_id">
                {{ __("Customer") }}
                <span class="text-red-500"> *</span>
            </label>

            <Select
                id="reservation_customer_id"
                v-model="quoteReservationForm.reservation_customer_id"
                :name="'reservation_customer_id'"
                :options="allCustomers"
                :placeholder="__('Customers')"
                class="w-full mb-3.5 sm:mb-0"
            />
        </div>

        <ModalSaveButtons
            :processing="quoteReservationForm.processing"
            :save-text="__('Reserve')"
            @cancel="closeReserveModal"
            @save="handleQuoteReservation"
        >
            <button
                :disabled="quoteReservationForm.processing"
                :class="{
                    'bg-red-500 hover:opacity-80':
                        !quoteReservationForm.processing,
                    'bg-red-500 cursor-not-allowed':
                        quoteReservationForm.processing,
                    'px-12 py-2 text-white rounded  active:scale-95 transition': true,
                }"
                @click="handleCancelReservation"
            >
                {{ __("Cancel the reservation") }}
            </button>
        </ModalSaveButtons>
    </Modal>

    <Modal :show="showAcceptCustomerModal" @close="closeAcceptCustomerModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Accept this quote for") }}
        </div>

        <hr />

        <div class="container p-4">
            <label for="customer_id">
                {{ __("Customer") }}
                <span class="text-red-500"> *</span>
            </label>

            <Select
                id="customer_id"
                v-model="acceptQuoteForm.customer_id"
                :name="'customer_id'"
                :options="allCustomers"
                :placeholder="__('Customers')"
                class="w-full mb-3.5 sm:mb-0"
            />
        </div>

        <ModalSaveButtons
            :processing="acceptQuoteForm.processing"
            :save-text="__('Accept')"
            @cancel="closeAcceptCustomerModal"
            @save="handleQuoteAcceptance"
        />
    </Modal>

    <Modal :show="showApproveRejectModal" @close="closeApproveRejectModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Approve or reject the quote") }}
        </div>

        <hr />

        <div
            class="flex justify-center border border-[#E9E7E7] px-3.5 p-3 text-xl font-medium d-flex w-100"
        >
            <button
                :disabled="updateStatusForm.processing"
                class="bg-green-300 px-12 py-2 mx-1 rounded hover:opacity-80 active:scale-95 transition"
                @click="
                    changeStatus(updateStatusForm, QuoteStatus.Approved);
                    closeApproveRejectModal();
                "
            >
                {{ __("Approve") }}
            </button>

            <button
                :disabled="updateStatusForm.processing"
                class="bg-red-300 px-12 py-2 mx-2 rounded hover:opacity-80 active:scale-95 transition"
                @click="
                    changeStatus(updateStatusForm, QuoteStatus.Rejected);
                    closeApproveRejectModal();
                "
            >
                {{ __("Reject") }}
            </button>
        </div>
    </Modal>

    <Modal
        :show="showSendQuoteModal"
        max-width="6xl"
        @close="closeSendQuoteModal"
    >
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 d-flex w-100">
            <GeneralInformation
                v-if="showSendQuoteModal"
                :customers="allCustomers"
                :form="form"
                :companies="companies"
                :service-levels="serviceLevels"
                :preview-pdf-disabled="true"
                :form-disabled="true"
                :owner-props="ownerProps"
            />

            <div class="flex justify-around flex-wrap">
                <VehicleSummary
                    v-for="(vehicle, index) in quote.vehicles"
                    :key="index"
                    :vehicle="vehicle"
                    :sales-price-service-items-per-vehicle-units="
                        salesPriceServiceItemsPerVehicleUnits
                    "
                    :all-items-and-additionals="allItemsAndAdditionals"
                    :quote="quote"
                    :vehicles-price-fixed="false"
                />
            </div>

            <SummaryFinancialInformation
                v-if="showSendQuoteModal"
                :all-items-and-additionals="allItemsAndAdditionals"
                :quote="quote"
                :vehicles-count="vehiclesCount"
            />

            <ItemsComponent
                :form="form"
                :vehicles-count="vehiclesCount"
                :form-disabled="true"
            />

            <AdditionalServices
                :form="form"
                :vehicles-count="vehiclesCount"
                :form-disabled="true"
            />

            <EmailText :form="form" :form-disabled="true" />

            <AdditionalInformationAndConditions
                :form="form"
                :form-disabled="true"
            />
        </div>

        <div class="container p-4 border-b border-[#E9E7E7]">
            <label for="customer_ids"> {{ __("Customers") }}</label>

            <SelectMultiple
                v-model="quoteInvitationForm.customer_ids"
                :name="'customer_ids'"
                :options="allCustomers"
                :placeholder="__('Customers')"
                class="w-full mb-3.5"
            />

            <label for="user_group_id">{{ __("User group") }}</label>

            <Select
                v-model="quoteInvitationForm.user_group_id"
                :name="'user_group_id'"
                :options="userGroups"
                :placeholder="__('User groups')"
                class="w-full mb-3.5"
            />

            <label for="locale">
                {{ __("Language") }}
                <span class="text-red-500"> *</span>
            </label>
            <Select
                v-model="quoteInvitationForm.locale"
                :name="'locale'"
                :options="Locale"
                :capitalize="true"
                :placeholder="__('Language')"
                class="w-full mb-3.5"
            />
        </div>

        <ModalSaveButtons
            :processing="quoteInvitationForm.processing"
            :save-text="__('Send')"
            @cancel="closeSendQuoteModal"
            @save="sendQuote"
        />
    </Modal>
</template>
