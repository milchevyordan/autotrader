<script setup lang="ts">
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";

import VehicleSummary from "@/common/VehicleSummary.vue";
import Header from "@/components/Header.vue";
import AdditionalServices from "@/components/html/AdditionalServices.vue";
import ConnectedModules from "@/components/html/ConnectedModules.vue";
import ItemsComponent from "@/components/html/Items.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Modal from "@/components/Modal.vue";
import ShowStatusAction from "@/components/quote-invitations/ShowStatusAction.vue";
import SummaryFinancialInformation from "@/components/quote-invitations/SummaryFinancialInformation.vue";
import { QuoteInvitationStatus } from "@/enums/QuoteInvitationStatus";
import { QuoteStatus } from "@/enums/QuoteStatus";
import AppLayout from "@/layouts/AppLayout.vue";
import { baseFormRules } from "@/rules/base-form-rules";
import { Quote, QuoteInvitation } from "@/types";
import { convertCurrencyToUnits } from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    quoteInvitation: QuoteInvitation;
    allItemsAndAdditionals: boolean;
}>();

const showAcceptModal = ref(false);
const showRejectModal = ref(false);

const openAcceptModal = () => {
    showAcceptModal.value = true;
};

const closeAcceptModal = () => {
    showAcceptModal.value = false;
};

const openRejectModal = () => {
    showRejectModal.value = true;
};

const closeRejectModal = () => {
    showRejectModal.value = false;
};

const form = useForm<{
    _method: string;
    id: number;
}>({
    _method: "patch",
    id: props.quoteInvitation.id,
});

const quoteIsReservedForAnotherProfile = computed(() => {
    return (
        props.quoteInvitation.quote.reservation_until &&
        new Date(props.quoteInvitation.quote.reservation_until) > new Date() &&
        props.quoteInvitation.quote.reservation_customer_id !=
            usePage().props.auth.user.id
    );
});

const accept = () => {
    validate(form, baseFormRules);

    form.post(route("quote-invitations.accept", form.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {},
    });
    closeAcceptModal();
};

const reject = () => {
    validate(form, baseFormRules);

    form.post(route("quote-invitations.reject", form.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {},
    });
    closeRejectModal();
};

const vehiclesCount = computed(
    () => props.quoteInvitation.quote.vehicles.length ?? 1
);

const vehiclesPriceFixed = computed(
    () => props.quoteInvitation.quote.status >= QuoteStatus.Approved
);

const salesPriceServiceItemsPerVehicleUnits = computed(
    () =>
        Number(
            convertCurrencyToUnits(
                props.quoteInvitation.quote.total_sales_price_service_items
            )
        ) / vehiclesCount.value
);
</script>

<template>
    <Head :title="__('Quote Invitation')" />

    <AppLayout>
        <Header :text="__('Quote Invitation')" />

        <div class="flex justify-center">
            <div class="w-full max-w-[1721px]">
                <div class="flex justify-around flex-wrap">
                    <VehicleSummary
                        v-for="(vehicle, index) in quoteInvitation.quote
                            .vehicles"
                        :key="index"
                        :vehicles-price-fixed="vehiclesPriceFixed"
                        :vehicle="vehicle"
                        :quote="quoteInvitation.quote"
                        :sales-price-service-items-per-vehicle-units="
                            salesPriceServiceItemsPerVehicleUnits
                        "
                        :delivery-week="vehicle.pivot?.delivery_week"
                        :all-items-and-additionals="allItemsAndAdditionals"
                    />
                </div>

                <ShowStatusAction :status="quoteInvitation.status" />

                <SummaryFinancialInformation
                    :quote="quoteInvitation.quote"
                    :all-items-and-additionals="allItemsAndAdditionals"
                    :vehicles-count="vehiclesCount"
                />

                <ItemsComponent
                    :form="{
                        items: quoteInvitation.quote.order_items,
                    }"
                    :vehicles-count="vehiclesCount"
                    :hide-purchase-price="true"
                    :form-disabled="true"
                    class="bg-white mx-0"
                />

                <AdditionalServices
                    :form="{
                        additional_services:
                            quoteInvitation.quote.order_services,
                    }"
                    :vehicles-count="vehiclesCount"
                    :hide-purchase-price="true"
                    :form-disabled="true"
                    class="bg-white mx-0"
                />

                <div
                    v-if="$can('edit-quote')"
                    class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8"
                >
                    <ConnectedModules
                        :quotes="[{
                            id: quoteInvitation.quote.id
                        } as Quote]"
                    />
                </div>
            </div>
        </div>

        <div
            v-if="
                $can('accept-or-reject-quote-invitation') &&
                !quoteIsReservedForAnotherProfile &&
                quoteInvitation.status === QuoteInvitationStatus.Concept
            "
            class="w-auto rounded-lg shadow-lg fixed bg-white z-50 bottom-20 md:bottom-2.5 right-2 sm:right-5 md:right-4 p-2 element-center gap-2 text-white"
        >
            <button
                class="bg-[#00A793] text-white rounded px-10 py-2 hover:opacity-80 active:scale-95 transition"
                @click="openAcceptModal"
            >
                {{ __("Accept") }} {{ __("Quote") }}
            </button>

            <button
                class="bg-[#E50000] text-white rounded px-10 py-2 hover:opacity-80 active:scale-95 transition"
                @click="openRejectModal"
            >
                {{ __("Reject") }} {{ __("Quote") }}
            </button>
        </div>

        <Modal :show="showAcceptModal" @close="closeAcceptModal">
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{ __("Are you sure you would like to accept the quote") }}
                ?
            </div>

            <ModalSaveButtons
                :processing="form.processing"
                :save-text="__('Yes')"
                @cancel="closeAcceptModal"
                @save="accept"
            />
        </Modal>

        <Modal :show="showRejectModal" @close="closeRejectModal">
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{ __("Are you sure you would like to reject the quote") }}
                ?
            </div>

            <ModalSaveButtons
                :processing="form.processing"
                :save-text="__('Yes')"
                @cancel="closeRejectModal"
                @save="reject"
            />
        </Modal>
    </AppLayout>
</template>
