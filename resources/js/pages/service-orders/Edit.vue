<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";

import Accordion from "@/components/Accordion.vue";
import Header from "@/components/Header.vue";
import AdditionalServices from "@/components/html/AdditionalServices.vue";
import InputFile from "@/components/html/InputFile.vue";
import InputImage from "@/components/html/InputImage.vue";
import InternalRemarks from "@/components/html/InternalRemarks.vue";
import ItemsComponent from "@/components/html/Items.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import Section from "@/components/html/Section.vue";
import GeneralInformation from "@/components/service-orders/GeneralInformation.vue";
import Stepper from "@/components/service-orders/Stepper.vue";
import Table from "@/data-table/Table.vue";
import { DataTable, Multiselect } from "@/data-table/types";
import { ServiceOrderStatus } from "@/enums/ServiceOrderStatus";
import IconMinus from "@/icons/Minus.vue";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import IconPlus from "@/icons/Plus.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { $can } from "@/plugins/permissions";
import { serviceOrdersFormRules } from "@/rules/service-orders-form-rules";
import {
    Company,
    DatabaseFile,
    ServiceOrder,
    ServiceOrderForm,
    Role,
    User,
    ServiceLevel,
    AdditionalService,
    OrderItem,
    ServiceVehicle,
    ServiceLevelDefaults,
    Ownership,
} from "@/types";
import { withFlash, dateTimeToLocaleString, resetOwnerId } from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    serviceOrder: ServiceOrder;
    dataTable: DataTable<ServiceVehicle>;
    files: {
        vehicleDocuments: DatabaseFile[];
        files: DatabaseFile[];
    };
    companies: Multiselect<Company>;
    customers: Multiselect<User>;
    mainCompanyRoles?: Multiselect<Role>;
    mainCompanyUsers: Multiselect<User>;
    serviceLevels: Multiselect<ServiceLevel>;
    serviceLevelDefaults?: ServiceLevelDefaults;
    items?: OrderItem[];
    levelServices?: AdditionalService[];
    acceptedOwnership: Ownership;
    pendingOwnerships: Ownership[];
    resetServiceLevels?: boolean;
}>();

const form = useForm<ServiceOrderForm>({
    _method: "put",
    id: props.serviceOrder.id,
    owner_id: props.acceptedOwnership?.user_id,
    service_vehicle_id: props.serviceOrder.service_vehicle_id,
    service_level_id: props.serviceOrder.service_level_id,
    customer_company_id: props.serviceOrder.customer_company_id,
    customer_id: props.serviceOrder.customer_id,
    seller_id: props.serviceOrder.seller_id,
    status: props.serviceOrder.status,
    type_of_service: props.serviceOrder.type_of_service,
    payment_condition: props.serviceOrder.payment_condition,
    payment_condition_free_text: props.serviceOrder.payment_condition_free_text,

    images: [],
    vehicleDocuments: [],
    files: [],

    items:
        props.items?.map((item: OrderItem) => ({
            id: item.id,
            shortcode: item.shortcode,
            type: item.type,
            purchase_price: item.purchase_price,
            description: item.description,
            sale_price: item.sale_price || "0",
            in_output: !!item.in_output, //do not simplify this expression
            item: item.item,
            shouldBeAdded: true,
        })) ?? [],

    additional_services: props.levelServices ?? [],
    internal_remark_user_ids: [],
    internal_remark_role_ids: [],
    internal_remark: null!,
});

const formDisabled = computed(() => {
    return (
        props.serviceOrder.status !== ServiceOrderStatus.Concept &&
        !$can("super-edit")
    );
});

const tableData = ref<DataTable<ServiceVehicle>>(props.dataTable);

watch(
    () => props.dataTable,
    (newValue) => {
        tableData.value.data = props.dataTable.data;
        tableData.value.paginator = newValue.paginator;
    }
);

const save = async (only?: Array<string>) => {
    validate(form, serviceOrdersFormRules);

    return new Promise<void>((resolve, reject) => {
        form.post(
            route("service-orders.update", props.serviceOrder.id as number),
            {
                preserveScroll: true,
                preserveState: true,
                forceFormData: true, // preserves all form data
                only: withFlash(only),
                onSuccess: () => {
                    form.reset(
                        "internal_remark",
                        "images",
                        "vehicleDocuments",
                        "files"
                    );

                    resetOwnerId(form);

                    resolve();
                },
                onError: () => {
                    reject(new Error("Error, during update"));
                },
            }
        );
    });
};

const addVehicle = async (item: ServiceVehicle) => {
    form.service_vehicle_id = item.id;
    tableData.value.data = [item] as ServiceVehicle[];

    await save();
};

const removeVehicle = async () => {
    form.service_vehicle_id = null!;

    await save();
};
</script>

<template>
    <Head :title="__('Service Order')" />

    <AppLayout>
        <Header :text="__('Service Order')" />

        <Stepper :service-order="serviceOrder" :form-is-dirty="form.isDirty" />

        <div
            class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
        >
            <Accordion>
                <template #head>
                    <div class="font-semibold text-xl sm:text-2xl mb-4">
                        {{ __("Service Vehicle") }}
                    </div>
                </template>

                <Table
                    :data-table="tableData"
                    :per-page-options="[5, 10, 15, 20, 50]"
                    :global-search="true"
                    :selected-row-indexes="[form.service_vehicle_id]"
                    :selected-row-column="'id'"
                >
                    <template #cell(created_at)="{ value, item }">
                        <div class="flex gap-1.5">
                            {{ dateTimeToLocaleString(value) }}
                        </div>
                    </template>

                    <template #cell(updated_at)="{ value, item }">
                        <div class="flex gap-1.5">
                            {{ dateTimeToLocaleString(value) }}
                        </div>
                    </template>

                    <template #cell(variant.name)="{ value, item }">
                        {{ item.variant?.name }}
                    </template>

                    <template #cell(action)="{ value, item }">
                        <div class="flex gap-1.5">
                            <div
                                v-if="
                                    serviceOrder.status ==
                                    ServiceOrderStatus.Concept
                                "
                                class="flex gap-1.5"
                            >
                                <div
                                    v-if="form.service_vehicle_id == item.id"
                                    class="flex gap-1.5"
                                >
                                    <button
                                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                        @click="removeVehicle()"
                                    >
                                        <IconMinus
                                            classes="w-4 h-4 text-slate-600"
                                        />
                                    </button>
                                </div>
                                <div v-else>
                                    <button
                                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                        @click="addVehicle(item)"
                                    >
                                        <IconPlus
                                            classes="w-4 h-4 text-slate-600"
                                        />
                                    </button>
                                </div>
                            </div>
                            <Link
                                v-if="
                                    $can('edit-service-vehicle') &&
                                    !item.deleted_at
                                "
                                :href="route('service-vehicles.edit', item.id)"
                                class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                            >
                                <IconPencilSquare
                                    classes="w-4 h-4 text-slate-600"
                                />
                            </Link>
                        </div>
                    </template>
                </Table>
            </Accordion>
        </div>

        <div class="flex justify-center">
            <div class="w-full max-w-[1721px]">
                <GeneralInformation
                    :form="form"
                    :service-level-defaults="serviceLevelDefaults"
                    :form-disabled="formDisabled"
                    :service-order="serviceOrder"
                    :companies="companies"
                    :owner-props="{
                        mainCompanyUsers: mainCompanyUsers,
                        pendingOwnerships: pendingOwnerships,
                    }"
                    :customers="customers"
                    :service-levels="serviceLevels"
                    :reset-service-levels="resetServiceLevels"
                />

                <ItemsComponent
                    :form="form"
                    :items="items"
                    :form-disabled="formDisabled"
                    :hide-total="true"
                />

                <AdditionalServices
                    :form="form"
                    :level-services="levelServices"
                    :form-disabled="formDisabled"
                    :hide-total="true"
                />

                <InternalRemarks
                    :internal-remarks="serviceOrder.internal_remarks"
                    :form="form"
                    :main-company-roles="mainCompanyRoles"
                    :main-company-users="mainCompanyUsers"
                />

                <div class="grid lg:grid-cols-1 xl:grid-cols-2 gap-4 mt-4">
                    <Section classes="p-3.5 h-fit">
                        <div
                            class="font-semibold text-xl sm:text-2xl mb-2 sm:mb-4"
                        >
                            {{ __("Photo") }}
                        </div>

                        <InputImage
                            id="images"
                            v-model="form.images"
                            :images="serviceOrder.images"
                            text-classes="py-14"
                            :delete-disabled="formDisabled"
                        />
                    </Section>

                    <Section classes="p-3.5 h-fit">
                        <div
                            class="font-semibold text-xl sm:text-2xl mb-2 sm:mb-4"
                        >
                            {{ __("Documents") }}
                        </div>

                        <div
                            class="grid sm:grid-cols-1 xl:grid-cols-2 gap-5 gap-y-0"
                        >
                            <InputFile
                                id="vehicle-document-upload"
                                v-model="form.vehicleDocuments"
                                :files="files.vehicleDocuments"
                                :text="__('Vehicle documents')"
                                text-classes="py-14"
                                :delete-disabled="formDisabled"
                            />

                            <InputFile
                                id="files-upload"
                                v-model="form.files"
                                :files="files.files"
                                :text="__('Additional documents')"
                                text-classes="py-14"
                                :delete-disabled="formDisabled"
                            />
                        </div>
                    </Section>
                </div>
            </div>
        </div>

        <ResetSaveButtons
            :processing="form.processing"
            @reset="form.reset()"
            @save="save"
        />
    </AppLayout>
</template>
