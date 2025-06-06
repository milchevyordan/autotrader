<script setup lang="ts">
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import { computed, onMounted, ref } from "vue";

import Accordion from "@/components/Accordion.vue";
import AdditionalInformationAndConditions from "@/components/AdditionalInformationAndConditions.vue";
import Header from "@/components/Header.vue";
import AdditionalServices from "@/components/html/AdditionalServices.vue";
import InternalRemarks from "@/components/html/InternalRemarks.vue";
import ItemsComponent from "@/components/html/Items.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import Section from "@/components/html/Section.vue";
import VehicleInformationModal from "@/components/html/VehicleInformationModal.vue";
import VehicleLinks from "@/components/html/VehicleLinks.vue";
import WeekRangePicker from "@/components/html/WeekRangePicker.vue";
import EmailText from "@/components/quotes/EmailText.vue";
import GeneralInformation from "@/components/quotes/GeneralInformation.vue";
import SummaryFinancialInformation from "@/components/quotes/SummaryFinancialInformation.vue";
import VehicleDamages from "@/components/VehicleDamages.vue";
import Table from "@/data-table/Table.vue";
import { DataTable, Multiselect } from "@/data-table/types";
import { ExteriorColour } from "@/enums/ExteriorColour";
import { FuelType } from "@/enums/FuelType";
import IconMinus from "@/icons/Minus.vue";
import IconPlus from "@/icons/Plus.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { quoteFormRules } from "@/rules/quote-form-rules";
import {
    Vehicle,
    QuoteForm,
    Company,
    User,
    ServiceLevel,
    OrderItem,
    AdditionalService,
    Role,
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

const props = defineProps<{
    dataTable: DataTable<Vehicle>;
    queryVehicleId: number;
    companies: Multiselect<Company>;
    customers?: Multiselect<User>;
    serviceLevels: Multiselect<ServiceLevel>;
    mainCompanyRoles?: Multiselect<Role>;
    mainCompanyUsers: Multiselect<User>;
    serviceLevelDefaults?: ServiceLevelDefaults;
    items?: OrderItem[];
    levelServices?: AdditionalService[];
    vehicleInformation?: Vehicle;
    workflowProcesses?: Multiselect<WorkflowProcess>;
    resetServiceLevels?: boolean;
}>();

const form = useForm<QuoteForm>({
    id: null!,
    owner_id: usePage().props.auth.user.id,
    seller_id: null!,
    service_level_id: null!,
    name: null!,
    delivery_week: {
        from: null!,
        to: null!,
    },
    payment_condition: null!,
    payment_condition_free_text: null!,
    discount: null!,
    discount_in_output: false,
    transport_included: false,
    type_of_sale: null!,
    damage: null!,
    down_payment: false,
    down_payment_amount: null!,
    vat_percentage: usePage().props?.auth.company.vat_percentage,
    vat_deposit: false,
    email_text: null!,
    additional_info_conditions: null!,
    total_purchase_price: null!,
    total_fee_intermediate_supplier: null!,
    total_sales_price_exclude_vat: null!,
    total_vat: null!,
    total_sales_price_include_vat: null!,
    total_bpm: null!,
    total_quote_price: null!,
    total_quote_price_exclude_vat: null!,
    total_registration_fees: null!,

    vehicles: [],
    vehicleIds: [],

    items: [],
    additional_services: [],
    is_brutto: true,
    calculation_on_quote: false,

    internal_remark_user_ids: [],
    internal_remark_role_ids: [],
    internal_remark: null!,
});

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

onMounted(() => {
    if (props.queryVehicleId) {
        const selectedVehicle = props.dataTable.data.find(
            (vehicle: Vehicle) => vehicle.id == Number(props.queryVehicleId)
        );
        if (selectedVehicle) {
            addVehicle(selectedVehicle);
        }
    }
});

const selectedVehicles = ref<Vehicle[]>([]);

const vehiclesCount = computed(() => form.vehicleIds?.length ?? 0);

const addVehicle = (item: Vehicle) => {
    if (!form.vehicleIds.length && !form.name) {
        form.name = `${item.make?.name ?? ""} ${
            item.vehicle_model?.name ?? ""
        }`;
    }

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
    selectedVehicles.value.push(item);
    form.vehicleIds.push(item.id);

    handleDeliveryWeekChange(form);
};

const removeVehicle = (id: number) => {
    const formIndex = form.vehicleIds.indexOf(id);
    const vehicleIndex = selectedVehicles.value.findIndex(
        (selectedVehicle) => selectedVehicle.id === id
    );

    selectedVehicles.value.splice(vehicleIndex, 1);

    if (form.vehicles.hasOwnProperty(id)) {
        delete form.vehicles[id];
    }

    if (formIndex !== -1) {
        form.vehicleIds.splice(formIndex, 1);
    }

    if (!form.vehicleIds.length && form.name) {
        form.reset("name");
    }

    handleDeliveryWeekChange(form);
};

const save = () => {
    validate(form, quoteFormRules);

    form.post(route("quotes.store"), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head :title="__('Quote')" />

    <AppLayout>
        <Header :text="__('Quote')" />

        <GeneralInformation
            :service-level-defaults="serviceLevelDefaults"
            :service-levels="serviceLevels"
            :form="form"
            :customers="customers"
            :companies="companies"
            :owner-props="{
                mainCompanyUsers: mainCompanyUsers,
            }"
            :preview-pdf-disabled="true"
            :reset-service-levels="resetServiceLevels"
        />

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
                <ItemsComponent
                    :form="form"
                    :vehicles-count="vehiclesCount"
                    :items="items"
                />

                <AdditionalServices
                    :form="form"
                    :vehicles-count="vehiclesCount"
                    :level-services="levelServices"
                />

                <SummaryFinancialInformation
                    :form="form"
                    :vehicles="selectedVehicles"
                    :vehicles-count="vehiclesCount"
                    :disabled="false"
                />

                <EmailText :form="form" />

                <AdditionalInformationAndConditions :form="form" />

                <Section classes="p-4 pb-0 mt-4 relative">
                    <div class="font-semibold text-xl sm:text-2xl mb-4">
                        {{ __("Damages") }}
                    </div>

                    <VehicleDamages
                        v-for="vehicle in selectedVehicles"
                        :key="vehicle.id"
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
