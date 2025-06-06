<script setup lang="ts">
import { Head, router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";

import Accordion from "@/components/Accordion.vue";
import AdditionalInformationAndConditions from "@/components/AdditionalInformationAndConditions.vue";
import Header from "@/components/Header.vue";
import AdditionalServices from "@/components/html/AdditionalServices.vue";
import Communication from "@/components/html/Communication.vue";
import InternalRemarks from "@/components/html/InternalRemarks.vue";
import ItemsComponent from "@/components/html/Items.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import Section from "@/components/html/Section.vue";
import VehicleInformationModal from "@/components/html/VehicleInformationModal.vue";
import VehicleLinks from "@/components/html/VehicleLinks.vue";
import WeekRangePicker from "@/components/html/WeekRangePicker.vue";
import ConnectedModules from "@/components/quotes/ConnectedModules.vue";
import EmailText from "@/components/quotes/EmailText.vue";
import GeneralInformation from "@/components/quotes/GeneralInformation.vue";
import QuoteInvitation from "@/components/quotes/Invitation.vue";
import Stepper from "@/components/quotes/Stepper.vue";
import SummaryFinancialInformation from "@/components/quotes/SummaryFinancialInformation.vue";
import VehicleDamages from "@/components/VehicleDamages.vue";
import Table from "@/data-table/Table.vue";
import { DataTable, Multiselect } from "@/data-table/types";
import { ExteriorColour } from "@/enums/ExteriorColour";
import { FuelType } from "@/enums/FuelType";
import { QuoteStatus } from "@/enums/QuoteStatus";
import IconMinus from "@/icons/Minus.vue";
import IconPlus from "@/icons/Plus.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { $can } from "@/plugins/permissions";
import { quoteFormRules } from "@/rules/quote-form-rules";
import {
    Vehicle,
    QuoteForm,
    Company,
    User,
    Quote,
    UserGroup,
    ServiceLevel,
    OrderItem,
    AdditionalService,
    Ownership,
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
    resetOwnerId,
} from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    quote: Quote;
    selectedVehicles: any;
    dataTable: DataTable<Vehicle>;
    companies: Multiselect<Company>;
    mainCompanyRoles?: Multiselect<Role>;
    mainCompanyUsers: Multiselect<User>;
    acceptedOwnership: Ownership;
    pendingOwnerships: Ownership[];
    customers: Multiselect<User>;
    allCustomers: Multiselect<User>;
    userGroups: Multiselect<UserGroup>;
    serviceLevels: Multiselect<ServiceLevel>;
    serviceLevelDefaults?: ServiceLevelDefaults;
    levelServices?: AdditionalService[];
    items?: OrderItem[];
    vehicleInformation?: Vehicle;
    previewPdfUrl?: string;
    workflowProcesses?: Multiselect<WorkflowProcess>;
    resetServiceLevels?: boolean;
}>();

const form = useForm<QuoteForm>({
    _method: "put",
    id: props.quote.id,
    owner_id: props.acceptedOwnership?.user_id,
    seller_id: props.quote.seller_id,
    status: props.quote.status,
    name: props.quote.name,
    delivery_week: props.quote.delivery_week,
    payment_condition: props.quote.payment_condition,
    payment_condition_free_text: props.quote.payment_condition_free_text,
    discount: props.quote.discount,
    discount_in_output: props.quote.discount_in_output,
    transport_included: props.quote.transport_included,
    type_of_sale: props.quote.type_of_sale,
    damage: props.quote.damage,
    down_payment: props.quote.down_payment,
    down_payment_amount: props.quote.down_payment_amount,
    vat_percentage: props.quote.vat_percentage,
    vat_deposit: props.quote.vat_deposit,
    additional_info_conditions: props.quote.additional_info_conditions,
    email_text: props.quote.email_text,
    total_vehicles_purchase_price: props.quote.total_vehicles_purchase_price,
    total_costs: props.quote.total_costs,
    total_sales_price_service_items:
        props.quote.total_sales_price_service_items,
    total_sales_margin: props.quote.total_sales_margin,
    total_fee_intermediate_supplier:
        props.quote.total_fee_intermediate_supplier,
    total_sales_price_exclude_vat: props.quote.total_sales_price_exclude_vat,
    service_level_id: props.quote.service_level_id,
    total_sales_price_include_vat: props.quote.total_sales_price_include_vat,
    total_vat: props.quote.total_vat,
    total_bpm: props.quote.total_bpm,
    is_brutto: props.quote.is_brutto,
    calculation_on_quote: props.quote.calculation_on_quote,
    total_quote_price_exclude_vat: props.quote.total_quote_price_exclude_vat,
    total_registration_fees: props.quote.total_registration_fees,
    total_quote_price: props.quote.total_quote_price,
    attached_customers: [],

    vehicles: props.selectedVehicles ?? [],
    vehicleIds: (Object.keys(props.selectedVehicles) ?? []).map(Number),

    items:
        props.items?.map((item: OrderItem) => ({
            id: item.id,
            shortcode: item.shortcode,
            type: item.type,
            purchase_price: item.purchase_price,
            sale_price: item.sale_price || "0",
            in_output: !!item.in_output,
            item: item.item,
            shouldBeAdded: true,
        })) ?? [],
    additional_services: props.levelServices ?? [],

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

const formDisabled = computed(() => {
    return props.quote.status != QuoteStatus.Concept && !$can("super-edit");
});

const previewPdfDisabled = computed(() => {
    return props.quote.status >= QuoteStatus.Sent;
});

const selectedVehicles = ref<Vehicle[]>(props.quote.vehicles);

const vehiclesCount = computed(() => form.vehicleIds?.length ?? 0);

const addVehicle = (item: Vehicle) => {
    if (!vehiclesCount.value && !form.name) {
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

    if (!vehiclesCount.value && form.name) {
        form.reset("name");
    }

    handleDeliveryWeekChange(form);
};

const save = async () => {
    validate(form, quoteFormRules);

    return new Promise<void>((resolve, reject) => {
        form.post(route("quotes.update", props.quote.id), {
            preserveScroll: true,
            preserveState: true,
            forceFormData: true, // preserves all form data
            onSuccess: () => {
                resetOwnerId(form);
                form.reset("internal_remark");

                resolve();
            },
            onError: () => {
                reject(new Error("Error, during update"));
            },
        });
    });
};

const title = computed(() => {
    const counts: Record<string, number> = {};

    props.quote.vehicles.forEach((vehicle) => {
        if (!vehicle.vehicle_model?.name) {
            return;
        }

        const modelName = vehicle.vehicle_model.name;
        if (counts[modelName]) {
            counts[modelName]++;
        } else {
            counts[modelName] = 1;
        }
    });

    const modelCountsString = Object.entries(counts)
        .map(([model, count]) => `${count}x ${model}`)
        .join(", ");

    let titleString = ` ${props.quote.id}`;

    if (props.quote.name) {
        titleString += `, ${props.quote.name}`;
    }

    if (modelCountsString) {
        titleString += `, ${modelCountsString}`;
    }

    if (props.quote.customer?.full_name) {
        titleString += `, ${props.quote.customer.full_name}`;
    }

    return titleString;
});
</script>

<template>
    <Head :title="__('Quote') + title" />

    <AppLayout>
        <Header :text="__('Quote') + title" />

        <Stepper
            :quote="quote"
            :form="form"
            :all-customers="allCustomers"
            :user-groups="userGroups"
            :companies="companies"
            :service-levels="serviceLevels"
            :form-is-dirty="form.isDirty"
            :vehicles-count="vehiclesCount"
            :owner-props="{
                mainCompanyUsers: mainCompanyUsers,
                pendingOwnerships: pendingOwnerships,
            }"
        />

        <GeneralInformation
            :quote="quote"
            :customers="customers"
            :form="form"
            :companies="companies"
            :service-level-defaults="serviceLevelDefaults"
            :preview-pdf-disabled="previewPdfDisabled || !vehiclesCount"
            :service-levels="serviceLevels"
            :form-disabled="formDisabled"
            :preview-pdf-url="previewPdfUrl"
            :owner-props="{
                mainCompanyUsers: mainCompanyUsers,
                pendingOwnerships: pendingOwnerships,
            }"
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
                                v-if="quote.status == QuoteStatus.Concept"
                                class="flex gap-1.5"
                            >
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
                                            :workflow-processes="
                                                workflowProcesses
                                            "
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
                            <div
                                v-else
                                class="flex flex-col gap-1.5 min-w-[270px]"
                            >
                                <div class="flex gap-1.5">
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
                                    :disabled="formDisabled"
                                    @change="handleDeliveryWeekChange(form)"
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
                    :items="items"
                    :vehicles-count="vehiclesCount"
                    :form-disabled="formDisabled"
                />

                <AdditionalServices
                    :form="form"
                    :vehicles-count="vehiclesCount"
                    :level-services="levelServices"
                    :form-disabled="formDisabled"
                />

                <SummaryFinancialInformation
                    :form="form"
                    :vehicles="selectedVehicles"
                    :vehicles-count="vehiclesCount"
                    :disabled="formDisabled"
                />

                <EmailText :form="form" :form-disabled="formDisabled" />

                <AdditionalInformationAndConditions
                    :form="form"
                    :form-disabled="formDisabled"
                />

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
                    :internal-remarks="quote.internal_remarks"
                    :form="form"
                    :main-company-roles="mainCompanyRoles"
                    :main-company-users="mainCompanyUsers"
                />

                <Communication :mails="quote.mails" :files="quote.files" />

                <QuoteInvitation :invitations="quote.quote_invitations" />

                <div
                    v-if="quote.status == QuoteStatus.Created_sales_order"
                    class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8"
                >
                    <ConnectedModules :quote="quote" />
                </div>
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
