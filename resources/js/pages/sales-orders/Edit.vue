<script setup lang="ts">
import { Head, router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";

import Accordion from "@/components/Accordion.vue";
import AdditionalInformationAndConditions from "@/components/AdditionalInformationAndConditions.vue";
import Header from "@/components/Header.vue";
import AdditionalServices from "@/components/html/AdditionalServices.vue";
import Communication from "@/components/html/Communication.vue";
import InputFile from "@/components/html/InputFile.vue";
import InternalRemarks from "@/components/html/InternalRemarks.vue";
import ItemsComponent from "@/components/html/Items.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import Section from "@/components/html/Section.vue";
import VehicleInformationModal from "@/components/html/VehicleInformationModal.vue";
import VehicleLinks from "@/components/html/VehicleLinks.vue";
import WeekRangePicker from "@/components/html/WeekRangePicker.vue";
import ConnectedModules from "@/components/sales-orders/ConnectedModules.vue";
import CreateModules from "@/components/sales-orders/CreateModules.vue";
import GeneralInformation from "@/components/sales-orders/GeneralInformation.vue";
import Stepper from "@/components/sales-orders/Stepper.vue";
import SummaryFinancialInformation from "@/components/sales-orders/SummaryFinancialInformation.vue";
import VehicleDamages from "@/components/VehicleDamages.vue";
import Table from "@/data-table/Table.vue";
import { DataTable, Multiselect } from "@/data-table/types";
import { ExteriorColour } from "@/enums/ExteriorColour";
import { FuelType } from "@/enums/FuelType";
import { SalesOrderStatus } from "@/enums/SalesOrderStatus";
import { setFlashMessages } from "@/globals";
import IconMinus from "@/icons/Minus.vue";
import IconPlus from "@/icons/Plus.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { $can } from "@/plugins/permissions";
import { salesOrdersFormRules } from "@/rules/sales-orders-form-rules";
import {
    Company,
    OrderItem,
    AdditionalService,
    SalesOrderForm,
    ServiceLevel,
    Role,
    User,
    Vehicle,
    SalesOrderFiles,
    Ownership,
    ServiceLevelDefaults,
    UpdateStatusForm,
    WorkflowProcess,
} from "@/types";
import {
    addWeeksToWeekYear,
    changeStatus,
    dateTimeToLocaleString,
    dateToLocaleString,
    findEnumKeyByValue,
    getImage,
    handleDeliveryWeekChange,
    replaceEnumUnderscores,
    resetOwnerId,
    withFlash,
} from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    salesOrder: SalesOrderForm;
    acceptedOwnership: Ownership;
    pendingOwnerships: Ownership[];
    selectedVehicles: any;
    files: SalesOrderFiles;
    companies: Multiselect<Company>;
    customers: Multiselect<User>;
    dataTable: DataTable<Vehicle>;
    mainCompanyRoles?: Multiselect<Role>;
    mainCompanyUsers: Multiselect<User>;
    serviceLevels: Multiselect<ServiceLevel>;
    serviceLevelDefaults?: ServiceLevelDefaults;
    items?: OrderItem[];
    levelServices?: AdditionalService[];
    vehicleInformation?: Vehicle;
    previewPdfUrl?: string;
    canCreateDownPaymentInvoice: boolean;
    resetServiceLevels?: boolean;
    workflowProcesses?: Multiselect<WorkflowProcess>;
}>();

const vehicles = ref<Vehicle[]>(props.salesOrder.vehicles);

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
    return (
        props.salesOrder.status !== SalesOrderStatus.Concept &&
        !$can("super-edit")
    );
});

const form = useForm<SalesOrderForm>({
    _method: "put",
    id: props.salesOrder.id,
    owner_id: props.acceptedOwnership?.user_id,
    status: props.salesOrder.status,
    customer_company_id: props.salesOrder.customer_company_id,
    customer_id: props.salesOrder.customer_id,
    reference: props.salesOrder.reference,
    seller_id: props.salesOrder.seller_id,
    currency: props.salesOrder.currency,
    type_of_sale: props.salesOrder.type_of_sale,
    down_payment: props.salesOrder.down_payment,
    down_payment_amount: props.salesOrder.down_payment_amount,
    transport_included: props.salesOrder.transport_included,
    vat_deposit: props.salesOrder.vat_deposit,
    vat_percentage: props.salesOrder.vat_percentage,
    total_vehicles_purchase_price:
        props.salesOrder.total_vehicles_purchase_price,
    total_costs: props.salesOrder.total_costs,
    total_sales_price_service_items:
        props.salesOrder.total_sales_price_service_items,
    total_sales_margin: props.salesOrder.total_sales_margin,
    service_level_id: props.salesOrder.service_level_id,
    total_sales_price: props.salesOrder.total_sales_price,
    total_fee_intermediate_supplier:
        props.salesOrder.total_fee_intermediate_supplier,
    total_sales_price_exclude_vat:
        props.salesOrder.total_sales_price_exclude_vat,
    total_sales_excl_vat_with_items:
        props.salesOrder.total_sales_excl_vat_with_items,
    total_registration_fees: props.salesOrder.total_registration_fees,
    total_vat: props.salesOrder.total_vat,
    total_bpm: props.salesOrder.total_bpm,
    total_sales_price_include_vat:
        props.salesOrder.total_sales_price_include_vat,
    currency_rate: props.salesOrder.currency_rate,
    delivery_week: props.salesOrder.delivery_week,
    payment_condition: props.salesOrder.payment_condition,
    payment_condition_free_text: props.salesOrder.payment_condition_free_text,
    additional_info_conditions: props.salesOrder.additional_info_conditions,
    discount: props.salesOrder.discount,
    discount_in_output: props.salesOrder.discount_in_output,
    damage: props.salesOrder.damage,
    is_brutto: props.salesOrder.is_brutto,
    calculation_on_sales_order: props.salesOrder.calculation_on_sales_order,

    viesFiles: [],
    creditCheckFiles: [],
    contractFiles: [],
    contractSignedFiles: [],
    files: [],

    vehicles: props.selectedVehicles ?? [],
    vehicleIds: (Object.keys(props.selectedVehicles) ?? []).map(Number),

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

const vehiclesCount = computed(() => form.vehicleIds?.length ?? 0);

const updateStatusForm = useForm<UpdateStatusForm>({
    _method: "patch",
    id: props.salesOrder.id,
    status: null!,
    route: "sales-orders",
});

const handleContractSignedUpload = async () => {
    await save();

    await changeStatus(
        updateStatusForm,
        SalesOrderStatus.Uploaded_signed_contract
    );
};

const saveAndSubmit = async () => {
    await save();

    if (form.vehicleIds.length == 0) {
        setFlashMessages({
            error: "Not selected any vehicles, need to select at least one Vehicle",
        });
    }

    await changeStatus(updateStatusForm, SalesOrderStatus.Submitted);
};

const save = async (only?: Array<string>) => {
    validate(form, salesOrdersFormRules);

    return new Promise<void>((resolve, reject) => {
        form.post(route("sales-orders.update", props.salesOrder.id), {
            preserveScroll: true,
            preserveState: true,
            forceFormData: true, // preserves all form data
            only: withFlash(only),
            onSuccess: () => {
                form.reset(
                    "internal_remark",
                    "viesFiles",
                    "creditCheckFiles",
                    "contractFiles",
                    "contractSignedFiles",
                    "files"
                );

                resetOwnerId(form);

                resolve();
            },
            onError: () => {
                reject(new Error("Error, during update"));
            },
        });
    });
};

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
</script>

<template>
    <Head :title="__('Sales order')" />

    <AppLayout>
        <Header :text="__('Sales order')" />

        <Stepper
            :sales-order="salesOrder"
            :selected-vehicle-ids="form.vehicleIds"
            :vehicles="vehicles"
            :form-is-dirty="form.isDirty"
            :companies="companies"
            :files="files"
            :can-create-down-payment-invoice="canCreateDownPaymentInvoice"
            @save-and-submit="saveAndSubmit"
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
                                v-if="
                                    salesOrder.status ==
                                    SalesOrderStatus.Concept
                                "
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
                            <div v-else class="flex-col min-w-[270px]">
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
                                    :name="`vehicles[${item.id}][delivery_week]`"
                                    :placeholder="__('Delivery Week')"
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
                <GeneralInformation
                    :form="form"
                    :service-level-defaults="serviceLevelDefaults"
                    :form-disabled="formDisabled"
                    :owner-props="{
                        mainCompanyUsers: mainCompanyUsers,
                        pendingOwnerships: pendingOwnerships,
                    }"
                    :sales-order="salesOrder"
                    :companies="companies"
                    :customers="customers"
                    :service-levels="serviceLevels"
                    :reset-service-levels="resetServiceLevels"
                    :preview-pdf-url="previewPdfUrl"
                />

                <ItemsComponent
                    :form="form"
                    :vehicles-count="vehiclesCount"
                    :items="items"
                    :form-disabled="formDisabled"
                />

                <AdditionalServices
                    :form="form"
                    :vehicles-count="vehiclesCount"
                    :level-services="levelServices"
                    :form-disabled="formDisabled"
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
                            :files="files.viesFiles"
                            :delete-disabled="formDisabled"
                            :text="__('Upload VIES files')"
                            text-classes="py-14"
                        />

                        <InputFile
                            id="credit-check-supplier"
                            v-model="form.creditCheckFiles"
                            :files="files.creditCheckFiles"
                            :delete-disabled="formDisabled"
                            :text="__('Upload credit check buyer')"
                            text-classes="py-14"
                        />

                        <InputFile
                            id="order-contract-signed"
                            v-model="form.contractSignedFiles"
                            :files="files.contractSignedFiles"
                            :disabled="
                                props.salesOrder.status !=
                                SalesOrderStatus.Sent_to_buyer
                            "
                            :delete-disabled="formDisabled"
                            :text="__('Upload contract signed')"
                            text-classes="py-14"
                            @change="handleContractSignedUpload"
                        />

                        <InputFile
                            id="order-files"
                            v-model="form.files"
                            :files="files.files"
                            :delete-disabled="formDisabled"
                            :text="__('Sales order files')"
                            text-classes="py-14"
                        />
                    </div>
                </div>

                <SummaryFinancialInformation
                    :vehicles="vehicles"
                    :vehicles-count="vehiclesCount"
                    :form="form"
                    :disabled="formDisabled"
                />

                <AdditionalInformationAndConditions :form="form" />

                <Section classes="p-4 pb-0 mt-4 relative">
                    <div class="font-semibold text-xl sm:text-2xl mb-4">
                        {{ __("Damages") }}
                    </div>

                    <VehicleDamages
                        v-for="vehicle in vehicles"
                        :key="vehicle.id"
                        :vehicle="vehicle"
                    />
                </Section>

                <InternalRemarks
                    :internal-remarks="salesOrder.internal_remarks"
                    :form="form"
                    :main-company-roles="mainCompanyRoles"
                    :main-company-users="mainCompanyUsers"
                />

                <Communication
                    :mails="salesOrder.mails"
                    :files="files.generatedPdf"
                />

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
                    <ConnectedModules :sales-order="salesOrder" />

                    <CreateModules :sales-order="salesOrder" />
                </div>
            </div>
        </div>

        <VehicleInformationModal
            :vehicle="vehicleInformation"
            :show-vehicle-information-modal="showVehicleInformationModal"
            @close-modal="closeVehicleInformationModal"
        />

        <ResetSaveButtons
            :processing="form.processing || updateStatusForm.processing"
            @reset="form.reset()"
            @save="save"
        />
    </AppLayout>
</template>
