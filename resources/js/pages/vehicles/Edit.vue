<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";

import Accordion from "@/components/Accordion.vue";
import Header from "@/components/Header.vue";
import Calculation from "@/components/html/Calculation.vue";
import ChangeLogs from "@/components/html/ChangeLogs.vue";
import ConnectedModules from "@/components/html/ConnectedModules.vue";
import FactoryOptionsHighlights from "@/components/html/FactoryOptionsHighlights.vue";
import InputFile from "@/components/html/InputFile.vue";
import InputImage from "@/components/html/InputImage.vue";
import InternalRemarks from "@/components/html/InternalRemarks.vue";
import Options from "@/components/html/Options.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import Section from "@/components/html/Section.vue";
import BasicInformation from "@/components/vehicle/create-edit/BasicInformation.vue";
import BPM from "@/components/vehicle/create-edit/BPM.vue";
import CreateModules from "@/components/vehicle/create-edit/CreateModules.vue";
import Damages from "@/components/vehicle/create-edit/Damages.vue";
import Dates from "@/components/vehicle/create-edit/Dates.vue";
import Vehicle from "@/components/vehicle/create-edit/Vehicle.vue";
import { Multiselect } from "@/data-table/types";
import AppLayout from "@/layouts/AppLayout.vue";
import { vehicleFormRules } from "@/rules/vehicle-form-rules";
import {
    Engine,
    Make,
    Variant,
    VehicleForm,
    VehicleGroup,
    VehicleModel,
    Setting,
    Bpm,
    Company,
    Role,
    User,
    PurchaseOrder,
    VehicleImages,
    VehicleFiles,
    Ownership,
    WorkflowProcess,
} from "@/types";
import { resetOwnerId, withFlash } from "@/utils.js";
import { validate } from "@/validations";

const props = defineProps<{
    workflowProcesses?: Multiselect<WorkflowProcess>;
    acceptedOwnership: Ownership;
    pendingOwnerships: Ownership[];
    make: Multiselect<Make>;
    vehicleModel: Multiselect<VehicleModel>;
    variant: Multiselect<Variant>;
    engine: Multiselect<Engine>;
    images: VehicleImages;
    files: VehicleFiles;
    vehicleGroup: Multiselect<VehicleGroup>;
    vehicleDefaults: Setting | null;
    vehicle: VehicleForm;
    bpmValues?: Bpm;
    suppliers: Multiselect<User>;
    supplierCompanies: Multiselect<Company>;
    mainCompanyRoles?: Multiselect<Role>;
    mainCompanyUsers: Multiselect<User>;
    transferVehicleToken?: string;
}>();

const form = useForm<VehicleForm>({
    _method: "put",
    id: props.vehicle.id,
    owner_id: props.acceptedOwnership?.user_id,
    make_id: props.vehicle.make_id,
    variant_id: props.vehicle.variant_id,
    vehicle_model_free_text: props.vehicle.vehicle_model_free_text,
    variant_free_text: props.vehicle.variant_free_text,
    engine_id: props.vehicle.engine_id,
    engine_free_text: props.vehicle.engine_free_text,
    vehicle_group_id: props.vehicle.vehicle_group_id,
    purchaseOrder: props.vehicle.purchaseOrders as PurchaseOrder,
    type: props.vehicle.type,
    body: props.vehicle.body,
    fuel: props.vehicle.fuel,
    vehicle_status: props.vehicle.vehicle_status,
    stock: props.vehicle.stock,
    current_registration: props.vehicle.current_registration,
    coc: props.vehicle.coc,
    interior_material: props.vehicle.interior_material,
    transmission: props.vehicle.transmission,
    transmission_free_text: props.vehicle.transmission_free_text,
    panorama: props.vehicle.panorama,
    headlights: props.vehicle.headlights,
    digital_cockpit: props.vehicle.digital_cockpit,
    cruise_control: props.vehicle.cruise_control,
    keyless_entry: props.vehicle.keyless_entry,
    air_conditioning: props.vehicle.air_conditioning,
    pdc: props.vehicle.pdc,
    second_wheels: props.vehicle.second_wheels,
    camera: props.vehicle.camera,
    tow_bar: props.vehicle.tow_bar,
    seat_heating: props.vehicle.seat_heating,
    seat_massage: props.vehicle.seat_massage,
    optics: props.vehicle.optics,
    tinted_windows: props.vehicle.tinted_windows,
    sports_package: props.vehicle.sports_package,
    warranty: props.vehicle.warranty,
    navigation: props.vehicle.navigation,
    sports_seat: props.vehicle.sports_seat,
    seats_electrically_adjustable: props.vehicle.seats_electrically_adjustable,
    app_connect: props.vehicle.app_connect,
    warranty_free_text: props.vehicle.warranty_free_text,
    navigation_free_text: props.vehicle.navigation_free_text,
    app_connect_free_text: props.vehicle.app_connect_free_text,
    panorama_free_text: props.vehicle.panorama_free_text,
    headlights_free_text: props.vehicle.headlights_free_text,
    digital_cockpit_free_text: props.vehicle.digital_cockpit_free_text,
    cruise_control_free_text: props.vehicle.cruise_control_free_text,
    keyless_entry_free_text: props.vehicle.keyless_entry_free_text,
    air_conditioning_free_text: props.vehicle.air_conditioning_free_text,
    pdc_free_text: props.vehicle.pdc_free_text,
    second_wheels_free_text: props.vehicle.second_wheels_free_text,
    camera_free_text: props.vehicle.camera_free_text,
    tow_bar_free_text: props.vehicle.tow_bar_free_text,
    sports_seat_free_text: props.vehicle.sports_seat_free_text,
    seats_electrically_adjustable_free_text:
        props.vehicle.seats_electrically_adjustable_free_text,
    seat_heating_free_text: props.vehicle.seat_heating_free_text,
    seat_massage_free_text: props.vehicle.seat_massage_free_text,
    optics_free_text: props.vehicle.optics_free_text,
    tinted_windows_free_text: props.vehicle.tinted_windows_free_text,
    sports_package_free_text: props.vehicle.sports_package_free_text,
    highlight_1: props.vehicle.highlight_1,
    highlight_2: props.vehicle.highlight_2,
    highlight_3: props.vehicle.highlight_3,
    highlight_4: props.vehicle.highlight_4,
    highlight_5: props.vehicle.highlight_5,
    highlight_6: props.vehicle.highlight_6,
    color_type: props.vehicle.color_type,
    damage: props.vehicle.damage,
    hp: props.vehicle.hp,
    kilometers: props.vehicle.kilometers,
    kw: props.vehicle.kw,
    co2_wltp: props.vehicle.co2_wltp,
    co2_nedc: props.vehicle.co2_nedc,
    is_ready_to_be_sold: props.vehicle.is_ready_to_be_sold,
    purchase_repaired_damage: props.vehicle.purchase_repaired_damage,
    currency_exchange_rate: props.vehicle.calculation.currency_exchange_rate,
    is_vat: props.vehicle.calculation.is_vat,
    is_locked: props.vehicle.calculation.is_locked,
    intermediate: props.vehicle.calculation.intermediate,
    original_currency: props.vehicle.calculation.original_currency,
    selling_price_supplier: props.vehicle.calculation.selling_price_supplier,
    sell_price_currency_euro:
        props.vehicle.calculation.sell_price_currency_euro,
    vat_percentage: props.vehicle.calculation.vat_percentage,
    net_purchase_price: props.vehicle.calculation.net_purchase_price,
    fee_intermediate: props.vehicle.calculation.fee_intermediate,
    total_purchase_price: props.vehicle.calculation.total_purchase_price,
    costs_of_damages: props.vehicle.calculation.costs_of_damages,
    transport_inbound: props.vehicle.calculation.transport_inbound,
    transport_outbound: props.vehicle.calculation.transport_outbound,
    costs_of_taxation: props.vehicle.calculation.costs_of_taxation,
    recycling_fee: props.vehicle.calculation.recycling_fee,
    sales_margin: props.vehicle.calculation.sales_margin,
    purchase_cost_items_services:
        props.vehicle.calculation.purchase_cost_items_services,
    total_costs_with_fee: props.vehicle.calculation.total_costs_with_fee,
    sale_price_net_including_services_and_products:
        props.vehicle.calculation
            .sale_price_net_including_services_and_products,
    sale_price_services_and_products:
        props.vehicle.calculation.sale_price_services_and_products,
    discount: props.vehicle.calculation.discount,
    sales_price_net: props.vehicle.calculation.sales_price_net,
    vat: props.vehicle.calculation.vat,
    sales_price_incl_vat_or_margin:
        props.vehicle.calculation.sales_price_incl_vat_or_margin,
    rest_bpm_indication: props.vehicle.calculation.rest_bpm_indication,
    leges_vat: props.vehicle.calculation.leges_vat,
    sales_price_total: props.vehicle.calculation.sales_price_total,
    gross_bpm: props.vehicle.calculation.gross_bpm,
    bpm_percent: props.vehicle.calculation.bpm_percent,
    bpm: props.vehicle.calculation.bpm,
    advert_link: props.vehicle.advert_link,
    vehicle_reference: props.vehicle.vehicle_reference,
    nl_registration_number: props.vehicle.nl_registration_number,
    supplier_company_id: props.vehicle.supplier_company_id,
    supplier_id: props.vehicle.supplier_id,
    supplier_reference_number: props.vehicle.supplier_reference_number,
    supplier_given_damages: props.vehicle.supplier_given_damages,
    gross_bpm_new: props.vehicle.gross_bpm_new,
    rest_bpm_as_per_table: props.vehicle.rest_bpm_as_per_table,
    calculation_bpm_in_so: props.vehicle.calculation_bpm_in_so,
    bpm_declared: props.vehicle.bpm_declared,
    gross_bpm_recalculated_based_on_declaration:
        props.vehicle.gross_bpm_recalculated_based_on_declaration,
    gross_bpm_on_registration: props.vehicle.gross_bpm_on_registration,
    rest_bpm_to_date: props.vehicle.rest_bpm_to_date,
    invoice_bpm: props.vehicle.invoice_bpm,
    bpm_post_change_amount: props.vehicle.bpm_post_change_amount,
    depreciation_percentage: props.vehicle.depreciation_percentage,
    vin: props.vehicle.vin,
    option: props.vehicle.option,
    damage_description: props.vehicle.damage_description,
    expected_date_of_availability_from_supplier:
        props.vehicle.expected_date_of_availability_from_supplier,
    expected_leadtime_for_delivery_from:
        props.vehicle.expected_leadtime_for_delivery_from,
    expected_leadtime_for_delivery_to:
        props.vehicle.expected_leadtime_for_delivery_to,
    first_registration_date: props.vehicle.first_registration_date,
    first_registration_nl: props.vehicle.first_registration_nl,
    registration_nl: props.vehicle.registration_nl,
    registration_date_approval: props.vehicle.registration_date_approval,
    last_name_registration: props.vehicle.last_name_registration,
    first_name_registration_nl: props.vehicle.first_name_registration_nl,
    registration_valid_until: props.vehicle.registration_valid_until,

    internalImages: [],
    externalImages: [],
    internalFiles: [],
    externalFiles: [],

    specific_exterior_color: props.vehicle.specific_exterior_color,
    factory_name_color: props.vehicle.factory_name_color,
    specific_interior_color: props.vehicle.specific_interior_color,
    factory_name_interior: props.vehicle.factory_name_interior,
    vehicle_model_id: props.vehicle.vehicle_model_id,

    internal_remark_user_ids: [],
    internal_remark_role_ids: [],
    internal_remark: null!,
});

const resetMakeRelatedFields = (fields: string[]) => {
    for (const field of fields) {
        form[field] = null!;
    }
};

const save = async (only?: Array<string>) => {
    validate(form, vehicleFormRules);

    return new Promise<void>((resolve, reject) => {
        form.post(route("vehicles.update", props.vehicle.id), {
            preserveScroll: true,
            forceFormData: true,
            only: withFlash(only),
            onSuccess: () => {
                form.reset(
                    "internal_remark",
                    "internalImages",
                    "externalImages",
                    "internalFiles",
                    "externalFiles"
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
</script>

<template>
    <Head :title="__('Vehicle')" />

    <AppLayout>
        <Header :text="__('Vehicle') + ' ' + vehicle.identification_code" />

        <div class="flex justify-center">
            <div class="w-full max-w-[1721px]">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
                    <ConnectedModules
                        :pre-order-collection-first="vehicle.pre_order"
                        :purchase-order="vehicle.purchase_order"
                        :sales-order="vehicle.sales_order"
                        :transport-orders="vehicle.transport_orders"
                        :workflow="vehicle.workflow"
                        :work-order="vehicle.work_order"
                        :documents="vehicle.documents"
                        :quotes="vehicle.quotes"
                    />

                    <CreateModules
                        :vehicle="vehicle"
                        :workflow-processes="workflowProcesses"
                        :purchase-order="vehicle.purchase_order"
                        :sales-order="vehicle.sales_order"
                        :work-order="vehicle.work_order"
                        :transport-orders="vehicle.transport_orders"
                        :documents="vehicle.documents"
                        :internal-files="files.internalFiles"
                        :transfer-vehicle-token="transferVehicleToken"
                    />
                </div>

                <div class="grid lg:grid-cols-1 xl:grid-cols-2 gap-4">
                    <Section classes="p-3.5 h-fit">
                        <div
                            class="font-semibold text-xl sm:text-2xl mb-2 sm:mb-4"
                        >
                            {{ __("Photo") }}
                        </div>

                        <div
                            class="grid sm:grid-cols-1 xl:grid-cols-2 gap-5 gap-y-0"
                        >
                            <InputImage
                                id="internal-images"
                                v-model="form.internalImages"
                                :images="images.internalImages"
                                :delete-disabled="true"
                                text-classes="py-14"
                                :text="__('Internal Images')"
                            />

                            <InputImage
                                id="internal-images"
                                v-model="form.externalImages"
                                :images="images.externalImages"
                                :delete-disabled="true"
                                text-classes="py-14"
                                :text="__('External Images')"
                            />
                        </div>
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
                                id="internal-files"
                                v-model="form.internalFiles"
                                :files="files.internalFiles"
                                :delete-disabled="true"
                                :text="__('Internal Files')"
                                text-classes="py-14"
                            />

                            <InputFile
                                id="external-files"
                                v-model="form.externalFiles"
                                :files="files.externalFiles"
                                :delete-disabled="true"
                                :text="__('External Files')"
                                text-classes="py-14"
                            />
                        </div>
                    </Section>
                </div>

                <BasicInformation
                    :form="form"
                    :supplier-companies="supplierCompanies"
                    :suppliers="suppliers"
                    :owner-props="{
                        mainCompanyUsers: mainCompanyUsers,
                        pendingOwnerships: pendingOwnerships,
                    }"
                />

                <Section classes="p-4 mt-4 relative">
                    <Accordion>
                        <template #head>
                            <div class="font-semibold text-xl sm:text-2xl mb-4">
                                {{ __("Vehicle and Calculation") }}
                            </div>
                        </template>

                        <div class="grid lg:grid-cols-1 xl:grid-cols-2 gap-4">
                            <div
                                class="grid grid-cols-1 sm:grid-cols-3 xl:border-r border-[#E9E7E7] xl:pr-8 sm:gap-y-2 items-center"
                            >
                                <Vehicle
                                    :vehicle-options-data="{
                                        make,
                                        engine,
                                        variant,
                                        vehicleModel,
                                        vehicleGroup,
                                    }"
                                    :form="form"
                                    @reset-make-related-fields="
                                        resetMakeRelatedFields
                                    "
                                />
                            </div>

                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 xl:pl-4 sm:gap-y-2 items-center h-fit"
                            >
                                <Calculation
                                    :form="form"
                                    :bpm-values="bpmValues"
                                />
                            </div>
                        </div>
                    </Accordion>
                </Section>

                <BPM :form="form" />

                <InternalRemarks
                    :internal-remarks="vehicle.internal_remarks"
                    :form="form"
                    :main-company-roles="mainCompanyRoles"
                    :main-company-users="mainCompanyUsers"
                />

                <Dates :form="form" />

                <FactoryOptionsHighlights :form="form" />

                <Options :form="form" />

                <Damages :form="form" />

                <ChangeLogs :change-logs="vehicle.change_logs" />
            </div>
        </div>

        <ResetSaveButtons
            :processing="form.processing"
            @reset="form.reset()"
            @save="save"
        />
    </AppLayout>
</template>
