<script setup lang="ts">
import { Head, useForm, Link, usePage } from "@inertiajs/vue3";
import { onMounted, watch } from "vue";
import { ref } from "vue";

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
import Table from "@/data-table/Table.vue";
import { DataTable, Multiselect } from "@/data-table/types";
import IconMinus from "@/icons/Minus.vue";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import IconPlus from "@/icons/Plus.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { serviceOrdersFormRules } from "@/rules/service-orders-form-rules";
import {
    Company,
    ServiceOrderForm,
    User,
    ServiceLevel,
    OrderItem,
    AdditionalService,
    Role,
    ServiceVehicle,
    ServiceLevelDefaults,
} from "@/types";
import { dateTimeToLocaleString } from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    dataTable: DataTable<ServiceVehicle>;
    queryVehicleId: number;
    companies: Multiselect<Company>;
    customers?: Multiselect<User>;
    serviceLevels: Multiselect<ServiceLevel>;
    serviceLevelDefaults?: ServiceLevelDefaults;
    items?: OrderItem[];
    levelServices?: AdditionalService[];
    mainCompanyRoles?: Multiselect<Role>;
    mainCompanyUsers: Multiselect<User>;
    resetServiceLevels?: boolean;
}>();

const form = useForm<ServiceOrderForm>({
    id: null!,
    owner_id: usePage().props.auth.user.id,
    service_vehicle_id: null!,
    service_level_id: null!,
    customer_company_id: null!,
    customer_id: null!,
    seller_id: null!,
    status: null!,
    type_of_service: null!,
    payment_condition: null!,
    payment_condition_free_text: null!,

    vehicleDocuments: [],
    files: [],
    images: [],

    items: [],
    additional_services: [],
    internal_remark_user_ids: [],
    internal_remark_role_ids: [],
    internal_remark: null!,
});

const tableData = ref<DataTable<ServiceVehicle>>(props.dataTable);

watch(
    () => props.dataTable,
    (newValue) => {
        tableData.value.data = newValue.data;
        tableData.value.paginator = newValue.paginator;
    }
);

onMounted(() => {
    if (props.queryVehicleId) {
        const selectedVehicle = tableData.value.data.find(
            (serviceVehicle: ServiceVehicle) =>
                serviceVehicle.id == Number(props.queryVehicleId)
        );
        if (selectedVehicle) {
            addVehicle(selectedVehicle);
        }
    }
});

const addVehicle = (item: ServiceVehicle) => {
    const clonedItem = JSON.parse(JSON.stringify(item)); // Ensure it's serializable
    form.service_vehicle_id = clonedItem.id;
    tableData.value.data = [clonedItem] as ServiceVehicle[];
};

const removeVehicle = () => {
    form.service_vehicle_id = null!;
    tableData.value.data = props.dataTable.data as ServiceVehicle[];
    tableData.value.paginator = props.dataTable.paginator;
};

const save = () => {
    validate(form, serviceOrdersFormRules);

    return new Promise<void>((resolve, reject) => {
        form.post(route("service-orders.store"), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                form.reset();
                resolve();
            },
            onError: () => {
                reject(new Error("Error, during update"));
            },
        });
    });
};
</script>

<template>
    <Head :title="__('Service Order')" />

    <AppLayout>
        <Header :text="__('Service Order')" />

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
                    :advanced-filters="true"
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

                    <template #cell(action)="{ value, item }">
                        <div class="flex gap-1.5">
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
                            <Link
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
                    :service-level-defaults="serviceLevelDefaults"
                    :form="form"
                    :companies="companies"
                    :customers="customers"
                    :owner-props="{
                        mainCompanyUsers: mainCompanyUsers,
                    }"
                    :service-levels="serviceLevels"
                    :reset-service-levels="resetServiceLevels"
                />

                <ItemsComponent
                    :form="form"
                    :items="items"
                    :hide-total="true"
                />

                <AdditionalServices
                    :form="form"
                    :level-services="levelServices"
                    :hide-total="true"
                />

                <InternalRemarks
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
                            :images="[]"
                            text-classes="py-14"
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
                                :files="[]"
                                :text="__('Vehicle documents')"
                                text-classes="py-14"
                            />

                            <InputFile
                                id="files-upload"
                                v-model="form.files"
                                :files="[]"
                                :text="__('Additional documents')"
                                text-classes="py-14"
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
