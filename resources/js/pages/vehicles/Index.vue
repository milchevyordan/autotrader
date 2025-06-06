<script setup lang="ts">
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

import Header from "@/components/Header.vue";
import CreateOrViewWorkflow from "@/components/html/CreateOrViewWorkflow.vue";
import IndexStatus from "@/components/html/IndexStatus.vue";
import Input from "@/components/html/Input.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Toggle from "@/components/html/Toggle.vue";
import Modal from "@/components/Modal.vue";
import AdvancedFilters from "@/data-table/components/AdvancedFilters.vue";
import Table from "@/data-table/Table.vue";
import { FilterValues } from "@/data-table/types";
import { DataTable } from "@/data-table/types";
import { DocumentStatus } from "@/enums/DocumentStatus";
import { PurchaseOrderStatus } from "@/enums/PurchaseOrderStatus";
import { QuoteStatus } from "@/enums/QuoteStatus";
import { SalesOrderStatus } from "@/enums/SalesOrderStatus";
import { TransportOrderStatus } from "@/enums/TransportOrderStatus";
import DocumentDuplicate from "@/icons/DocumentDuplicate.vue";
import Follow from "@/icons/Follow.vue";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import IconTrash from "@/icons/Trash.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { baseFormRules } from "@/rules/base-form-rules";
import { duplicateVehicleFormRules } from "@/rules/duplicate-vehicle-form-rules";
import { Form, Multiselect, Vehicle, WorkflowProcess } from "@/types";
import { dateTimeToLocaleString } from "@/utils";
import { validate } from "@/validations";
import { ExportType } from "@/data-table/enums/ExportType";

defineProps<{
    dataTable: DataTable<Vehicle>;
    workflowProcesses: Multiselect<WorkflowProcess>;
    vehicleTimelineItems?: {
        date: Date;
        title: string;
        route: string;
        id: number;
    }[];
}>();

const showDuplicateModal = ref(false);
const showTimelineModal = ref(false);
const deleteModalShown = ref(false);

const closeDuplicateModal = () => {
    showDuplicateModal.value = false;
    formDuplications.reset();
};

const showDeleteModal = (id: number) => {
    deleteModalShown.value = true;
    deleteForm.id = id;
};

const closeDeleteModal = () => {
    deleteModalShown.value = false;
    deleteForm.reset();
};

const timelineVehicleId = ref<number>(null!);

const openTimelineModal = async (id: number) => {
    timelineVehicleId.value = id;

    await new Promise((resolve, reject) => {
        router.reload({
            data: {
                vehicle_id: id,
            },
            only: ["vehicleTimelineItems"],
            onSuccess: resolve,
            onError: reject,
        });
    });

    showTimelineModal.value = true;
};

const closeTimelineModal = () => {
    showTimelineModal.value = false;
    timelineVehicleId.value = null!;
};

const deleteForm = useForm<{
    id: null | number;
}>({
    id: null!,
});

const followForm = useForm<{
    _method: string;
    id: null | number;
}>({
    _method: "put",
    id: null!,
});

const gridView = ref<boolean>(false);

const filterValues: FilterValues = {
    filter: {
        columns: {
            maxkw: "",
            minkw: "",
            maxhp: "",
            minhp: "",
            make: "",
            engine: "",
        },
    },
};

const formDuplications = useForm<Form>({
    id: null!,
    duplications: null!,
    first_registration_date: false,
    kilometers: false,
    specific_exterior_color: false,
    sales_price_total: false,
    option: false,
});

const duplicate = () => {
    validate(formDuplications, duplicateVehicleFormRules);

    formDuplications.post(route("vehicles.duplicate"), {
        preserveScroll: true,
        onSuccess: () => {
            formDuplications.reset();
        },
        onError: () => {},
    });
    closeDuplicateModal();
};

const handleFollow = (id: number) => {
    followForm.id = id;
    validate(followForm, baseFormRules);

    followForm.post(route("vehicles.follow", followForm.id as number), {
        preserveScroll: true,
    });
};

const handleDelete = () => {
    validate(deleteForm, baseFormRules);

    deleteForm.delete(route("vehicles.destroy", deleteForm.id as number), {
        preserveScroll: true,
    });
    closeDeleteModal();
};
</script>

<template>
    <Head :title="__('Vehicle')" />

    <AppLayout>
        <Header :text="__('Vehicles')" />

        <Table
            v-if="!gridView"
            :data-table="dataTable"
            :per-page-options="[5, 10, 15, 20, 50]"
            :global-search="true"
            :show-trashed="true"
            :export-types="[ExportType.Csv]"
        >
            <template #additionalContent>
                <div class="w-full flex gap-2">
                    <Link
                        :href="route('vehicle-groups.index')"
                        class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                    >
                        {{ __("Table View") }}
                    </Link>

                    <Link
                        v-if="$can('create-vehicle')"
                        class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                        :href="route('vehicles.create')"
                    >
                        {{ __("Create") }} {{ __("Vehicle") }}
                    </Link>
                </div>
            </template>

            <template #cell(id)="{ value, item }">
                <span class="cursor-pointer" @click="openTimelineModal(value)">
                    {{ value }}
                </span>
            </template>

            <template #cell(make.name)="{ value, item }">
                {{ item.make?.name }}
            </template>

            <template #cell(vehicleModel.name)="{ value, item }">
                {{ item.vehicle_model?.name }}
            </template>

            <template #cell(engine.name)="{ value, item }">
                {{ item.engine?.name }}
            </template>

            <template #cell(purchaseOrder)="{ value, item }">
                <IndexStatus
                    v-if="item.purchase_order[0]"
                    :item="item.purchase_order[0]"
                    :status-enum="PurchaseOrderStatus"
                    module="purchase-order"
                    :text="__('Purchase order')"
                />
            </template>

            <template #cell(salesOrder)="{ value, item }">
                <IndexStatus
                    v-if="item.sales_order[0]"
                    :item="item.sales_order[0]"
                    :status-enum="SalesOrderStatus"
                    module="sales-order"
                    :text="__('Sales order')"
                />
            </template>

            <template #cell(transportOrderInbound)="{ value, item }">
                <IndexStatus
                    v-if="item.transport_order_inbound[0]"
                    :item="item.transport_order_inbound[0]"
                    :status-enum="TransportOrderStatus"
                    module="transport-order"
                    :text="__('Transport order')"
                />
            </template>

            <template #cell(transportOrderOutbound)="{ value, item }">
                <IndexStatus
                    v-if="item.transport_order_outbound[0]"
                    :item="item.transport_order_outbound[0]"
                    :status-enum="TransportOrderStatus"
                    module="transport-order"
                    :text="__('Transport order')"
                />
            </template>

            <template #cell(documents)="{ value, item }">
                <IndexStatus
                    v-if="item.documents[0]"
                    :item="item.documents[0]"
                    :status-enum="DocumentStatus"
                    module="document"
                    :text="__('Document')"
                />
            </template>

            <template #cell(quotes)="{ value, item }">
                <IndexStatus
                    v-if="item.quotes.length"
                    :items="item.quotes"
                    :status-enum="QuoteStatus"
                    module="quote"
                    :multiple="true"
                    :text="__('Quotes')"
                />
            </template>

            <template #cell(created_at)="{ value, item }">
                <div class="flex gap-1.5">
                    <!-- {{ value }} -->
                    {{ dateTimeToLocaleString(value) }}
                </div>
            </template>

            <template #cell(updated_at)="{ value, item }">
                <div class="flex gap-1.5">
                    {{ dateTimeToLocaleString(value) }}
                </div>
            </template>

            <template #cell(action)="{ value, item }">
                <div class="flex gap-1.5">
                    <Link
                        v-if="$can('edit-vehicle')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        :href="route('vehicles.edit', item.id)"
                    >
                        <IconPencilSquare classes="w-4 h-4 text-[#909090]" />
                    </Link>

                    <CreateOrViewWorkflow
                        :vehicle="item"
                        type="App\Models\Vehicle"
                        :workflow-processes="workflowProcesses"
                    />

                    <button
                        :disabled="followForm.processing"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        :title="
                            !item.user_follows.length
                                ? __('Follow vehicle')
                                : __('Unfollow vehicle')
                        "
                        @click="handleFollow(item.id)"
                    >
                        <Follow
                            :solid="!!item.user_follows.length"
                            classes="w-4 h-4 text-[#909090]"
                        />
                    </button>

                    <button
                        v-if="$can('duplicate-vehicle')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        :title="__('Duplicate vehicle')"
                        @click="
                            showDuplicateModal = true;
                            formDuplications.id = item.id;
                        "
                    >
                        <DocumentDuplicate classes="w-4 h-4 text-[#909090]" />
                    </button>

                    <button
                        v-if="$can('delete-vehicle')"
                        :title="__('Delete')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        @click="showDeleteModal(item.id)"
                    >
                        <IconTrash classes="w-4 h-4 text-[#909090]" />
                    </button>
                </div>
            </template>

            <!-- Example template slot with join  -->
            <template #advancedFilters>
                <AdvancedFilters :filter-values="filterValues">
                    <div class="grid grid-cols-2 p-2 items-center px-4">
                        <div class="font-medium text-[15px]">
                            {{ __("Kw") }}
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <input
                                v-model="filterValues.filter.columns.minkw"
                                type="text"
                                placeholder="Min"
                                class="border border-gray-200 text-gray-900 text-sm rounded-md focus:outline-none focus:ring-0 focus:border-gray-300 block w-full py-2 px-2.5 placeholder-gray-400 peer transition hover:bg-gray-50 focus:bg-gray-50 disabled:bg-slate-100"
                            />

                            <input
                                v-model="filterValues.filter.columns.maxkw"
                                type="text"
                                placeholder="Max"
                                class="border border-gray-200 text-gray-900 text-sm rounded-md focus:outline-none focus:ring-0 focus:border-gray-300 block w-full py-2 px-2.5 placeholder-gray-400 peer transition hover:bg-gray-50 focus:bg-gray-50 disabled:bg-slate-100"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 p-2 items-center px-4">
                        <div class="font-medium text-[15px]">
                            {{ __("Hp") }}
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <input
                                v-model="filterValues.filter.columns.minhp"
                                type="text"
                                placeholder="Min"
                                class="border border-gray-200 text-gray-900 text-sm rounded-md focus:outline-none focus:ring-0 focus:border-gray-300 block w-full py-2 px-2.5 placeholder-gray-400 peer transition hover:bg-gray-50 focus:bg-gray-50 disabled:bg-slate-100"
                            />

                            <input
                                v-model="filterValues.filter.columns.maxhp"
                                type="text"
                                placeholder="Max"
                                class="border border-gray-200 text-gray-900 text-sm rounded-md focus:outline-none focus:ring-0 focus:border-gray-300 block w-full py-2 px-2.5 placeholder-gray-400 peer transition hover:bg-gray-50 focus:bg-gray-50 disabled:bg-slate-100"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 p-2 items-center px-4">
                        <div class="font-medium text-[15px]">
                            {{ __("Make") }}
                        </div>

                        <input
                            v-model="filterValues.filter.columns.make"
                            type="text"
                            :placeholder="__('Make')"
                            class="border border-gray-200 text-gray-900 text-sm rounded-md focus:outline-none focus:ring-0 focus:border-gray-300 block w-full py-2 px-2.5 placeholder-gray-400 peer transition hover:bg-gray-50 focus:bg-gray-50 disabled:bg-slate-100"
                        />
                    </div>

                    <div class="grid grid-cols-2 p-2 items-center px-4">
                        <div class="font-medium text-[15px]">
                            {{ __("Model") }}
                        </div>

                        <input
                            v-model="filterValues.filter.columns.model"
                            type="text"
                            :placeholder="__('Model')"
                            class="border border-gray-200 text-gray-900 text-sm rounded-md focus:outline-none focus:ring-0 focus:border-gray-300 block w-full py-2 px-2.5 placeholder-gray-400 peer transition hover:bg-gray-50 focus:bg-gray-50 disabled:bg-slate-100"
                        />
                    </div>

                    <div class="grid grid-cols-2 p-2 items-center px-4">
                        <div class="font-medium text-[15px]">
                            {{ __("Engine") }}
                        </div>

                        <input
                            v-model="filterValues.filter.columns.engine"
                            type="text"
                            :placeholder="__('Engine')"
                            class="border border-gray-200 text-gray-900 text-sm rounded-md focus:outline-none focus:ring-0 focus:border-gray-300 block w-full py-2 px-2.5 placeholder-gray-400 peer transition hover:bg-gray-50 focus:bg-gray-50 disabled:bg-slate-100"
                        />
                    </div>
                </AdvancedFilters>
            </template>
        </Table>

        <Modal
            :show="showDuplicateModal"
            max-width="md"
            @close="closeDuplicateModal"
        >
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{ __("Duplicate") }}
            </div>

            <div class="p-3.5 sm:gap-y-2">
                <label for="duplication">
                    {{ __("Number of duplications") }}
                </label>
                <Input
                    v-model="formDuplications.duplications"
                    :name="'duplications'"
                    type="number"
                    :placeholder="__('Number of duplications')"
                    class="mb-3.5"
                />

                <label>
                    {{ __("Duplicate") }}
                </label>

                <div class="grid grid-cols-2 mt-3 gap-2.5 items-center">
                    <Toggle
                        v-model="formDuplications.first_registration_date"
                        :label="__('First registration date')"
                    />

                    <Toggle
                        v-model="formDuplications.kilometers"
                        :label="__('Kilometers')"
                    />

                    <Toggle
                        v-model="formDuplications.specific_exterior_color"
                        :label="__('Color')"
                    />

                    <Toggle
                        v-model="formDuplications.sales_price_total"
                        :label="__('Sales price total')"
                    />

                    <Toggle
                        v-model="formDuplications.option"
                        :label="__('Options')"
                    />
                </div>
            </div>

            <ModalSaveButtons
                :processing="formDuplications.processing"
                :save-text="__('Duplicate')"
                @cancel="closeDuplicateModal"
                @save="duplicate"
            />
        </Modal>

        <Modal :show="showTimelineModal" @close="closeTimelineModal">
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{ __("Timeline Vehicle #") + timelineVehicleId }} ?
            </div>

            <ol class="relative border-s border-gray-200 m-5">
                <li
                    v-for="(vehicleTimelineItem, index) in vehicleTimelineItems"
                    :key="index"
                    class="mb-10 ms-4"
                >
                    <div
                        class="absolute w-3 h-3 bg-gray-200 rounded-full mt-1.5 -start-1.5 border border-white"
                    />
                    <div
                        class="mb-1 text-sm leading-none font-light text-gray-500"
                    >
                        {{ dateTimeToLocaleString(vehicleTimelineItem.date) }}
                    </div>
                    <Link
                        class="font-semibold text-slate-800"
                        :href="
                            route(
                                vehicleTimelineItem.route,
                                vehicleTimelineItem.id
                            )
                        "
                    >
                        {{ vehicleTimelineItem.title }}
                    </Link>
                </li>
            </ol>

            <div class="col-span-2 flex justify-end gap-3 mt-2 pt-1 pb-3 px-4">
                <button
                    class="bg-[#F0F0F0] px-12 py-2 rounded hover:opacity-80 active:scale-95 transition"
                    @click="closeTimelineModal"
                >
                    {{ __("Close") }}
                </button>
            </div>
        </Modal>

        <Modal :show="deleteModalShown" @close="closeDeleteModal">
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{ __("Delete Vehicle #") + deleteForm?.id }} ?
            </div>

            <ModalSaveButtons
                :processing="deleteForm.processing"
                :save-text="__('Delete')"
                @cancel="closeDeleteModal"
                @save="handleDelete"
            />
        </Modal>
    </AppLayout>
</template>
