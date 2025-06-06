<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import { defineAsyncComponent, ref } from "vue";

import Header from "@/components/Header.vue";
import InformationRow from "@/components/html/InformationRow.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Section from "@/components/html/Section.vue";
import Modal from "@/components/Modal.vue";
import ShowStatusAction from "@/components/ownerships/ShowStatusAction.vue";
import { OwnershipStatus } from "@/enums/OwnershipStatus";
import AppLayout from "@/layouts/AppLayout.vue";
import { baseFormRules } from "@/rules/base-form-rules";
import { Ownership } from "@/types";
import { validate } from "@/validations";

const props = defineProps<{
    ownership: Ownership;
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
    id: props.ownership.id,
});

const modalComponentMap: Record<
    string,
    ReturnType<typeof defineAsyncComponent>
> = {
    "App\\Models\\Vehicle": defineAsyncComponent(
        () => import("@/components/ownerships/VehicleSummary.vue")
    ),

    "App\\Models\\PurchaseOrder": defineAsyncComponent(
        () => import("@/components/purchase-orders/Summary.vue")
    ),

    "App\\Models\\SalesOrder": defineAsyncComponent(
        () => import("@/components/sales-orders/Summary.vue")
    ),

    "App\\Models\\ServiceOrder": defineAsyncComponent(
        () => import("@/components/service-orders/Summary.vue")
    ),

    "App\\Models\\Quote": defineAsyncComponent(
        () => import("@/components/quotes/Summary.vue")
    ),

    "App\\Models\\Document": defineAsyncComponent(
        () => import("@/pages/documents/partials/Summary.vue")
    ),

    "App\\Models\\WorkOrder": defineAsyncComponent(
        () => import("@/components/work-orders/Summary.vue")
    ),

    "App\\Models\\TransportOrder": defineAsyncComponent(
        () => import("@/components/transport-orders/Summary.vue")
    ),

    "App\\Models\\PreOrder": defineAsyncComponent(
        () => import("@/components/pre-orders/Summary.vue")
    ),
};

const accept = () => {
    validate(form, baseFormRules);

    form.post(route("ownerships.accept", { ownership: props.ownership }), {
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

    form.post(route("ownerships.reject", form.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {},
    });
    closeRejectModal();
};
</script>

<template>
    <Head :title="__('Ownership')" />

    <AppLayout>
        <Header :text="__('Ownership')" />

        <ShowStatusAction :status="ownership.status" />

        <div
            v-if="
                $can('accept-or-reject-ownership') &&
                ownership.status == OwnershipStatus.Pending
            "
            class="w-auto rounded-lg shadow-lg fixed z-50 bottom-20 md:bottom-2.5 right-2 sm:right-5 md:right-4 p-2 element-center gap-2 text-white"
        >
            <button
                class="bg-[#00A793] text-white rounded px-10 py-2 hover:opacity-80 active:scale-95 transition"
                @click="openAcceptModal"
            >
                {{ __("Accept") }} {{ __("Ownership") }}
            </button>

            <button
                class="bg-[#E50000] text-white rounded px-10 py-2 hover:opacity-80 active:scale-95 transition"
                @click="openRejectModal"
            >
                {{ __("Reject") }} {{ __("Ownership") }}
            </button>
        </div>

        <div>
            <div
                class="information-container flex flex-wrap justify-around my-2 p-4 gap-4 rounded-md"
            >
                <Section classes="h-fit max-w-sm">
                    <InformationRow :title="__('Creator of resource')">
                        {{ ownership.ownable.creator.full_name }}
                    </InformationRow>

                    <InformationRow :title="__('Invitation from')">
                        {{ ownership.creator.full_name }}
                    </InformationRow>

                    <component
                        :is="modalComponentMap[ownership.ownable_type]"
                        :resource="ownership.ownable"
                    />
                </Section>
            </div>
        </div>

        <Modal :show="showAcceptModal" @close="closeAcceptModal">
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{
                    __(
                        "Are you sure you would like to accept the ownership invitation"
                    )
                }}
                ?
            </div>

            <ModalSaveButtons
                class="bg-transparent"
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
                {{
                    __(
                        "Are you sure you would like to reject the ownership invitation"
                    )
                }}
                ?
            </div>

            <ModalSaveButtons
                :save-text="__('Yes')"
                :processing="form.processing"
                @cancel="closeRejectModal"
                @save="reject"
            />
        </Modal>
    </AppLayout>
</template>
