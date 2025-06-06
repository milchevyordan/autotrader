<script setup lang="ts">
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";

import Accordion from "@/components/Accordion.vue";
import AdditionalInformationAndConditions from "@/components/AdditionalInformationAndConditions.vue";
import Header from "@/components/Header.vue";
import AdditionalServices from "@/components/html/AdditionalServices.vue";
import InputFile from "@/components/html/InputFile.vue";
import InternalRemarks from "@/components/html/InternalRemarks.vue";
import ItemsComponent from "@/components/html/Items.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import Section from "@/components/html/Section.vue";
import VehicleInformationModal from "@/components/html/VehicleInformationModal.vue";
import VehicleLinks from "@/components/html/VehicleLinks.vue";
import WeekRangePicker from "@/components/html/WeekRangePicker.vue";
import GeneralInformation from "@/components/sales-orders/GeneralInformation.vue";
import SummaryFinancialInformation from "@/components/sales-orders/SummaryFinancialInformation.vue";
import VehicleDamages from "@/components/VehicleDamages.vue";
import Table from "@/data-table/Table.vue";
import { DataTable, Multiselect } from "@/data-table/types";
import { ExteriorColour } from "@/enums/ExteriorColour";
import { FuelType } from "@/enums/FuelType";
import IconMinus from "@/icons/Minus.vue";
import IconPlus from "@/icons/Plus.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { salesOrdersFormRules } from "@/rules/sales-orders-form-rules";
import {
    Company,
    Vehicle,
    Role,
    SalesOrderForm,
    User,
    ServiceLevel,
    OrderItem,
    AdditionalService,
    ServiceLevelDefaults,
    WorkflowProcess,
} from "@/types";
import {
    addWeeksToWeekYear,
    dateTimeToLocaleString,
    dateToLocaleString,
    findEnumKeyByValue,
    getImage,
    handleDeliveryWeekChange,
    replaceEnumUnderscores,
} from "@/utils";
import { validate } from "@/validations";

defineProps<{
    companies: Multiselect<Company>;
    customers?: Multiselect<User>;
    dataTable: DataTable<Vehicle>;
    items?: OrderItem[];
    levelServices?: AdditionalService[];
    mainCompanyRoles?: Multiselect<Role>;
    mainCompanyUsers: Multiselect<User>;
    serviceLevels: Multiselect<ServiceLevel>;
    serviceLevelDefaults?: ServiceLevelDefaults;
    vehicleInformation?: Vehicle;
    workflowProcesses?: Multiselect<WorkflowProcess>;
    resetServiceLevels?: boolean;
}>();

const vehicles = ref<Vehicle[]>([]);

const showVehicleInformationModal = ref(false);

const openVehicleInformationModal = async (id: number) => {
    await new Promise((resolve, reject) => {
        router.reload({
            data: { vehicle_id: id },
            only: ["vehicleInformation"],
            onSuccess: resolve,
            onError: reject,
        });
    });

    showVehicleInformationModal.value = true;
};

const closeVehicleInformationModal = () => {
    showVehicleInformationModal.value = false;
};

const form = useForm<SalesOrderForm>({
    id: null!,
    owner_id: usePage().props.auth.user.id,
    status: null!,
    customer_company_id: null,
    customer_id: null!,
    reference: null!,
    seller_id: null!,
    currency: usePage().props?.auth.company.default_currency,
    type_of_sale: null!,
    transport_included: false,
    vat_percentage: usePage().props?.auth.company.vat_percentage,
    vat_deposit: false,
    down_payment: false,
    down_payment_amount: null!,
    service_level_id: null!,
    total_sales_price: null!,
    total_fee_intermediate_supplier: null!,
    total_sales_price_exclude_vat: null!,
    total_sales_excl_vat_with_items: null!,
    total_registration_fees: null!,
    total_vat: null!,
    total_bpm: null!,
    total_sales_price_include_vat: null!,
    currency_rate: 1,
    delivery_week: {
        from: null!,
        to: null!,
    },
    damage: null!,
    payment_condition: null!,
    payment_condition_free_text: null!,
    additional_info_conditions: null!,
    discount: null!,
    discount_in_output: false,
    is_brutto: true,
    calculation_on_sales_order: false,

    viesFiles: [],
    creditCheckFiles: [],
    contractSignedFiles: [],
    files: [],

    vehicles: [],
    vehicleIds: [],

    items: [],
    additional_services: [],
    internal_remark_user_ids: [],
    internal_remark_role_ids: [],
    internal_remark: null!,
});

const vehiclesCount = computed(() => form.vehicleIds?.length ?? 0);

const addVehicle = (item: Vehicle) => {
    const resultObject = {
        [item.id]: {
            vehicle_id: item.id,
            delivery_week: {
                from: addWeeksToWeekYear(
                    item.expected_date_of_availability_from_supplier?.from,
                    item.expected_leadtime_for_delivery_from
                ),
                to: addWeeksToWeekYear(
                    item.expected_date_of_availability_from_supplier?.to,
                    item.expected_leadtime_for_delivery_to
                ),
            },
        },
    };

    form.vehicles = { ...form.vehicles, ...resultObject };
    vehicles.value.push(item);
    form.vehicleIds.push(item.id);

    handleDeliveryWeekChange(form);
};

const removeVehicle = (id: number) => {
    const formIndex = form.vehicleIds.indexOf(id);
    const vehicleIndex = vehicles.value.findIndex((obj) => obj.id === id);

    vehicles.value.splice(vehicleIndex, 1);

    if (form.vehicles.hasOwnProperty(id)) {
        delete form.vehicles[id];
    }

    if (formIndex !== -1) {
        form.vehicleIds.splice(formIndex, 1);
    }

    handleDeliveryWeekChange(form);
};

const save = () => {
    validate(form, salesOrdersFormRules);

    return new Promise<void>((resolve, reject) => {
        form.post(route("sales-orders.store"), {
            preserveScroll: true,
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
    <Head :title="__('Sales order')" />

    <AppLayout>
        <Header :text="__('Sales order')" />

        <div
            class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
        >
            <Accordion>
                <template #head>
                    <div class="font-semibold text-xl sm:text-2xl mb-4">
                        {{ __("Vehicles") }}
                    </div>
                </template>

                <Table
                    :data-table="dataTable"
                    :per-page-options="[5, 10, 15, 20, 50]"
                    :global-search="true"
                    :advanced-filters="true"
                    :selected-row-indexes="form.vehicleIds"
                    :selected-row-column="'id'"
                >
                    <template #cell(id)="{ value, item }">
                        <div
                            class="cursor-pointer"
                            @click="openVehicleInformationModal(value)"
                        >
                            {{ value }}
                        </div>
                    </template>

                    <template #cell(image_path)="{ value, item }">
                        <img
                            :src="getImage(item.image_path)"
                            alt="vehicle image"
                            class="object-contain w-auto mb-3 lg:mb-0 min-w-24 rounded"
                        />
                    </template>

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

                    <template #cell(first_registration_date)="{ value, item }">
                        <div class="flex gap-1.5">
                            {{ dateToLocaleString(value) }}
                        </div>
                    </template>

                    <template #cell(specific_exterior_color)="{ value, item }">
                        {{ findEnumKeyByValue(ExteriorColour, value) }}
                    </template>

                    <template
                        #cell(expected_date_of_availability_from_supplier)="{
                            value,
                            item,
                        }"
                    >
                        <div class="flex gap-1.5 min-w-[270px]">
                            <WeekRangePicker
                                :model-value="value"
                                name="expected_date_of_availability_from_supplier"
                                disabled
                            />
                        </div>
                    </template>

                    <template
                        #cell(expected_leadtime_for_delivery)="{ value, item }"
                    >
                        <div
                            v-if="
                                item.expected_leadtime_for_delivery_from ||
                                item.expected_leadtime_for_delivery_to
                            "
                            class="flex gap-1.5"
                        >
                            <span
                                v-if="item.expected_leadtime_for_delivery_from"
                            >
                                {{ __("From") }}
                                {{ item.expected_leadtime_for_delivery_from }}
                            </span>
                            <span v-if="item.expected_leadtime_for_delivery_to">
                                {{ __("to") }}
                                {{ item.expected_leadtime_for_delivery_to }}
                            </span>
                            {{ __("weeks") }}
                        </div>
                    </template>

                    <template #cell(fuel)="{ value, item }">
                        <div class="flex gap-1.5">
                            {{
                                replaceEnumUnderscores(
                                    findEnumKeyByValue(FuelType, value)
                                )
                            }}
                        </div>
                    </template>

                    <template #cell(action)="{ value, item }">
                        <div class="flex gap-1.5">
                            <div
                                v-if="form.vehicleIds.includes(item.id)"
                                class="flex flex-col gap-1.5 min-w-[270px]"
                            >
                                <div class="flex gap-1.5">
                                    <button
                                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                        @click="removeVehicle(item.id)"
                                    >
                                        <IconMinus
                                            classes="w-4 h-4 text-slate-600"
                                        />
                                    </button>

                                    <VehicleLinks
                                        :item="item"
                                        :workflow-processes="workflowProcesses"
                                    />
                                </div>

                                <WeekRangePicker
                                    v-if="form.vehicles[item.id]"
                                    v-model="
                                        form.vehicles[item.id].delivery_week
                                    "
                                    :placeholder="__('Delivery Week')"
                                    :name="`vehicles[${item.id}][delivery_week]`"
                                    @change="handleDeliveryWeekChange(form)"
                                />
                            </div>
                            <div v-else class="flex gap-1.5">
                                <button
                                    class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                    @click="addVehicle(item)"
                                >
                                    <IconPlus
                                        classes="w-4 h-4 text-slate-600"
                                    />
                                </button>

                                <VehicleLinks
                                    :item="item"
                                    :workflow-processes="workflowProcesses"
                                />
                            </div>
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
                    :owner-props="{
                        mainCompanyUsers: mainCompanyUsers,
                    }"
                    :companies="companies"
                    :customers="customers"
                    :service-levels="serviceLevels"
                    :preview-pdf-disabled="true"
                    :reset-service-levels="resetServiceLevels"
                />

                <ItemsComponent
                    :vehicles-count="vehiclesCount"
                    :form="form"
                    :items="items"
                />

                <AdditionalServices
                    :vehicles-count="vehiclesCount"
                    :form="form"
                    :level-services="levelServices"
                />

                <div
                    class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
                >
                    <div class="font-semibold text-xl sm:text-2xl mb-4">
                        {{ __("Document") }}
                    </div>

                    <div
                        class="grid sm:grid-cols-2 xl:grid-cols-4 gap-5 gap-y-0"
                    >
                        <InputFile
                            id="upload-vies"
                            v-model="form.viesFiles"
                            :files="[]"
                            :text="__('Upload VIES files')"
                            text-classes="py-14"
                        />

                        <InputFile
                            id="credit-check-supplier"
                            v-model="form.creditCheckFiles"
                            :files="[]"
                            :text="__('Upload credit check buyer')"
                            text-classes="py-14"
                        />

                        <InputFile
                            id="order-contract-signed"
                            :files="[]"
                            :disabled="true"
                            :text="__('Upload Sales order contract signed')"
                            text-classes="py-14"
                        />

                        <InputFile
                            id="order-files"
                            v-model="form.files"
                            :files="[]"
                            :text="__('Sales order files')"
                            text-classes="py-14"
                        />
                    </div>
                </div>

                <SummaryFinancialInformation
                    :vehicles="vehicles"
                    :vehicles-count="vehiclesCount"
                    :form="form"
                />

                <AdditionalInformationAndConditions :form="form" />

                <Section classes="p-4 pb-0 mt-4 relative">
                    <div class="font-semibold text-xl sm:text-2xl mb-4">
                        {{ __("Damages") }}
                    </div>

                    <VehicleDamages
                        v-for="vehicle in vehicles"
                        :vehicle="vehicle"
                    />
                </Section>

                <InternalRemarks
                    :form="form"
                    :main-company-roles="mainCompanyRoles"
                    :main-company-users="mainCompanyUsers"
                />
            </div>
        </div>

        <VehicleInformationModal
            :vehicle="vehicleInformation"
            :show-vehicle-information-modal="showVehicleInformationModal"
            @close-modal="closeVehicleInformationModal"
        />

        <ResetSaveButtons
            :processing="form.processing"
            @reset="form.reset()"
            @save="save"
        />
    </AppLayout>
</template>
