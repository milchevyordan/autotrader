<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";

import Accordion from "@/components/Accordion.vue";
import Header from "@/components/Header.vue";
import Calculation from "@/components/html/Calculation.vue";
import ConnectedModules from "@/components/html/ConnectedModules.vue";
import FactoryOptionsHighlights from "@/components/html/FactoryOptionsHighlights.vue";
import InputFile from "@/components/html/InputFile.vue";
import InputImage from "@/components/html/InputImage.vue";
import InternalRemarks from "@/components/html/InternalRemarks.vue";
import Options from "@/components/html/Options.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import Section from "@/components/html/Section.vue";
import BasicInformation from "@/components/pre-order-vehicle/create-edit/BasicInformation.vue";
import CreateModules from "@/components/pre-order-vehicle/create-edit/CreateModules.vue";
import Dates from "@/components/pre-order-vehicle/create-edit/Dates.vue";
import Vehicle from "@/components/pre-order-vehicle/create-edit/Vehicle.vue";
import { Multiselect } from "@/data-table/types";
import AppLayout from "@/layouts/AppLayout.vue";
import { preOrderVehicleFormRules } from "@/rules/pre-order-vehicle-form-rules";
import {
    Engine,
    Make,
    Variant,
    VehicleModel,
    Setting,
    Bpm,
    Company,
    Role,
    User,
    PreOrderVehicleForm,
    VehicleImages,
    VehicleFiles,
} from "@/types";
import { withFlash } from "@/utils.js";
import { validate } from "@/validations";

const props = defineProps<{
    make: Multiselect<Make>;
    vehicleModel: Multiselect<VehicleModel>;
    variant: Multiselect<Variant>;
    engine: Multiselect<Engine>;
    images: VehicleImages;
    files: VehicleFiles;
    vehicleDefaults: Setting | null;
    preOrderVehicle: PreOrderVehicleForm;
    bpmValues?: Bpm;
    suppliers: Multiselect<User>;
    supplierCompanies: Multiselect<Company>;
    mainCompanyRoles?: Multiselect<Role>;
    mainCompanyUsers?: Multiselect<User>;
}>();

const form = useForm<PreOrderVehicleForm>({
    _method: "put",
    id: props.preOrderVehicle.id,
    make_id: props.preOrderVehicle.make_id,
    variant_id: props.preOrderVehicle.variant_id,
    vehicle_model_free_text: props.preOrderVehicle.vehicle_model_free_text,
    variant_free_text: props.preOrderVehicle.variant_free_text,
    engine_id: props.preOrderVehicle.engine_id,
    engine_free_text: props.preOrderVehicle.engine_free_text,
    type: props.preOrderVehicle.type,
    vehicle_model: props.preOrderVehicle.vehicle_model,
    vehicle_model_id: props.preOrderVehicle.vehicle_model_id,
    supplier_company_id: props.preOrderVehicle.supplier_company_id,
    supplier_id: props.preOrderVehicle.supplier_id,
    body: props.preOrderVehicle.body,
    fuel: props.preOrderVehicle.fuel,
    vehicle_status: props.preOrderVehicle.vehicle_status,
    current_registration: props.preOrderVehicle.current_registration,
    interior_material: props.preOrderVehicle.interior_material,
    transmission: props.preOrderVehicle.transmission,
    specific_exterior_color: props.preOrderVehicle.specific_exterior_color,
    specific_interior_color: props.preOrderVehicle.specific_interior_color,
    panorama: props.preOrderVehicle.panorama,
    headlights: props.preOrderVehicle.headlights,
    digital_cockpit: props.preOrderVehicle.digital_cockpit,
    cruise_control: props.preOrderVehicle.cruise_control,
    keyless_entry: props.preOrderVehicle.keyless_entry,
    air_conditioning: props.preOrderVehicle.air_conditioning,
    pdc: props.preOrderVehicle.pdc,
    second_wheels: props.preOrderVehicle.second_wheels,
    camera: props.preOrderVehicle.camera,
    tow_bar: props.preOrderVehicle.tow_bar,
    seat_heating: props.preOrderVehicle.seat_heating,
    seat_massage: props.preOrderVehicle.seat_massage,
    optics: props.preOrderVehicle.optics,
    tinted_windows: props.preOrderVehicle.tinted_windows,
    sports_package: props.preOrderVehicle.sports_package,
    color_type: props.preOrderVehicle.color_type,
    warranty: props.preOrderVehicle.warranty,
    navigation: props.preOrderVehicle.navigation,
    sports_seat: props.preOrderVehicle.sports_seat,
    seats_electrically_adjustable:
        props.preOrderVehicle.seats_electrically_adjustable,
    app_connect: props.preOrderVehicle.app_connect,
    warranty_free_text: props.preOrderVehicle.warranty_free_text,
    navigation_free_text: props.preOrderVehicle.navigation_free_text,
    app_connect_free_text: props.preOrderVehicle.app_connect_free_text,
    panorama_free_text: props.preOrderVehicle.panorama_free_text,
    headlights_free_text: props.preOrderVehicle.headlights_free_text,
    digital_cockpit_free_text: props.preOrderVehicle.digital_cockpit_free_text,
    cruise_control_free_text: props.preOrderVehicle.cruise_control_free_text,
    keyless_entry_free_text: props.preOrderVehicle.keyless_entry_free_text,
    air_conditioning_free_text:
        props.preOrderVehicle.air_conditioning_free_text,
    pdc_free_text: props.preOrderVehicle.pdc_free_text,
    second_wheels_free_text: props.preOrderVehicle.second_wheels_free_text,
    camera_free_text: props.preOrderVehicle.camera_free_text,
    tow_bar_free_text: props.preOrderVehicle.tow_bar_free_text,
    sports_seat_free_text: props.preOrderVehicle.sports_seat_free_text,
    seats_electrically_adjustable_free_text:
        props.preOrderVehicle.seats_electrically_adjustable_free_text,
    seat_heating_free_text: props.preOrderVehicle.seat_heating_free_text,
    seat_massage_free_text: props.preOrderVehicle.seat_massage_free_text,
    optics_free_text: props.preOrderVehicle.optics_free_text,
    tinted_windows_free_text: props.preOrderVehicle.tinted_windows_free_text,
    sports_package_free_text: props.preOrderVehicle.sports_package_free_text,
    highlight_1: props.preOrderVehicle.highlight_1,
    highlight_2: props.preOrderVehicle.highlight_2,
    highlight_3: props.preOrderVehicle.highlight_3,
    highlight_4: props.preOrderVehicle.highlight_4,
    highlight_5: props.preOrderVehicle.highlight_5,
    highlight_6: props.preOrderVehicle.highlight_6,
    komm_number: props.preOrderVehicle.komm_number,
    factory_name_color: props.preOrderVehicle.factory_name_color,
    factory_name_interior: props.preOrderVehicle.factory_name_interior,
    transmission_free_text: props.preOrderVehicle.transmission_free_text,
    vehicle_reference: props.preOrderVehicle.vehicle_reference,
    configuration_number: props.preOrderVehicle.configuration_number,
    kilometers: props.preOrderVehicle.kilometers,
    production_weeks: props.preOrderVehicle.production_weeks,
    expected_delivery_weeks: props.preOrderVehicle.expected_delivery_weeks,
    expected_leadtime_for_delivery_from:
        props.preOrderVehicle.expected_leadtime_for_delivery_from,
    expected_leadtime_for_delivery_to:
        props.preOrderVehicle.expected_leadtime_for_delivery_to,
    registration_weeks_from: props.preOrderVehicle.registration_weeks_from,
    registration_weeks_to: props.preOrderVehicle.registration_weeks_to,
    option: props.preOrderVehicle.option,
    currency_exchange_rate:
        props.preOrderVehicle.calculation.currency_exchange_rate,
    is_vat: props.preOrderVehicle.calculation.is_vat,
    is_locked: props.preOrderVehicle.calculation.is_locked,
    intermediate: props.preOrderVehicle.calculation.intermediate,
    original_currency: props.preOrderVehicle.calculation.original_currency,
    selling_price_supplier:
        props.preOrderVehicle.calculation.selling_price_supplier,
    sell_price_currency_euro:
        props.preOrderVehicle.calculation.sell_price_currency_euro,
    vat_percentage: props.preOrderVehicle.calculation.vat_percentage,
    net_purchase_price: props.preOrderVehicle.calculation.net_purchase_price,
    fee_intermediate: props.preOrderVehicle.calculation.fee_intermediate,
    total_purchase_price:
        props.preOrderVehicle.calculation.total_purchase_price,
    costs_of_damages: props.preOrderVehicle.calculation.costs_of_damages,
    transport_inbound: props.preOrderVehicle.calculation.transport_inbound,
    transport_outbound: props.preOrderVehicle.calculation.transport_outbound,
    costs_of_taxation: props.preOrderVehicle.calculation.costs_of_taxation,
    recycling_fee: props.preOrderVehicle.calculation.recycling_fee,
    sales_margin: props.preOrderVehicle.calculation.sales_margin,
    total_costs_with_fee:
        props.preOrderVehicle.calculation.total_costs_with_fee,
    sales_price_net: props.preOrderVehicle.calculation.sales_price_net,
    vat: props.preOrderVehicle.calculation.vat,
    sales_price_incl_vat_or_margin:
        props.preOrderVehicle.calculation.sales_price_incl_vat_or_margin,
    rest_bpm_indication: props.preOrderVehicle.calculation.rest_bpm_indication,
    leges_vat: props.preOrderVehicle.calculation.leges_vat,
    sales_price_total: props.preOrderVehicle.calculation.sales_price_total,
    gross_bpm: props.preOrderVehicle.calculation.gross_bpm,
    bpm_percent: props.preOrderVehicle.calculation.bpm_percent,
    bpm: props.preOrderVehicle.calculation.bpm,

    internalImages: [],
    externalImages: [],
    internalFiles: [],
    externalFiles: [],

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
    validate(form, preOrderVehicleFormRules);

    return new Promise<void>((resolve, reject) => {
        form.post(
            route("pre-order-vehicles.update", props.preOrderVehicle.id),
            {
                preserveScroll: true,
                forceFormData: true, // preserves all form data
                only: withFlash(only),
                onSuccess: () => {
                    form.reset(
                        "internal_remark",
                        "internalImages",
                        "externalImages",
                        "internalFiles",
                        "externalFiles"
                    );

                    resolve();
                },
                onError: () => {
                    reject(new Error("Error, during update"));
                },
            }
        );
    });
};
</script>

<template>
    <Head :title="__('Pre Order Vehicle Edit')" />

    <AppLayout>
        <Header :text="__('Pre Order Vehicle Edit')" />

        <div class="flex justify-center">
            <div class="w-full max-w-[1721px]">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
                    <ConnectedModules
                        :pre-order="preOrderVehicle.pre_order"
                        :documents="preOrderVehicle.documents"
                        :transport-orders="preOrderVehicle.transport_orders"
                    />
                    <CreateModules
                        :pre-order-vehicle-id="preOrderVehicle.id"
                        :pre-order="preOrderVehicle.pre_order"
                        :transport-orders="preOrderVehicle.transport_orders"
                        :documents="preOrderVehicle.documents"
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
                                id="external-images"
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
                                    }"
                                    :form="form"
                                    @reset-make-related-fields="
                                        resetMakeRelatedFields
                                    "
                                />
                            </div>

                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 xl:pl-4 sm:gap-y-2 items-center"
                            >
                                <Calculation
                                    :form="form"
                                    :bpm-values="bpmValues"
                                />
                            </div>
                        </div>
                    </Accordion>
                </Section>

                <InternalRemarks
                    :internal-remarks="preOrderVehicle.internal_remarks"
                    :form="form"
                    :main-company-roles="mainCompanyRoles"
                    :main-company-users="mainCompanyUsers"
                />

                <Dates :form="form" />

                <FactoryOptionsHighlights :form="form" />

                <Options :form="form" />
            </div>
        </div>

        <ResetSaveButtons
            :processing="form.processing"
            @reset="form.reset()"
            @save="save"
        />
    </AppLayout>
</template>
