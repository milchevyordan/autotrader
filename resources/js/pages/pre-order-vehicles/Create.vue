<script setup lang="ts">
import { Head, useForm, usePage } from "@inertiajs/vue3";

import Accordion from "@/components/Accordion.vue";
import Header from "@/components/Header.vue";
import Calculation from "@/components/html/Calculation.vue";
import FactoryOptionsHighlights from "@/components/html/FactoryOptionsHighlights.vue";
import InputFile from "@/components/html/InputFile.vue";
import InputImage from "@/components/html/InputImage.vue";
import InternalRemarks from "@/components/html/InternalRemarks.vue";
import Options from "@/components/html/Options.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import Section from "@/components/html/Section.vue";
import BasicInformation from "@/components/pre-order-vehicle/create-edit/BasicInformation.vue";
import Dates from "@/components/pre-order-vehicle/create-edit/Dates.vue";
import Vehicle from "@/components/pre-order-vehicle/create-edit/Vehicle.vue";
import { Multiselect } from "@/data-table/types";
import { VehicleType } from "@/enums/VehicleType";
import AppLayout from "@/layouts/AppLayout.vue";
import { preOrderVehicleFormRules } from "@/rules/pre-order-vehicle-form-rules";
import {
    Bpm,
    Company,
    Engine,
    Make,
    PreOrderVehicleForm,
    Setting,
    Role,
    User,
    Variant,
    VehicleModel,
} from "@/types";
import { validate } from "@/validations";

const props = defineProps<{
    make: Multiselect<Make>;
    engine?: Multiselect<Engine>;
    variant?: Multiselect<Variant>;
    vehicleModel?: Multiselect<VehicleModel>;
    vehicleDefaults: null | Setting;
    userCompany: null | Company;
    bpmValues?: Bpm;
    suppliers?: Multiselect<User>;
    supplierCompanies: Multiselect<Company>;
    mainCompanyRoles?: Multiselect<Role>;
    mainCompanyUsers?: Multiselect<User>;
}>();

const modelDefaults: PreOrderVehicleForm = {
    id: null!,
    make_id: null!,
    variant_id: null!,
    vehicle_model_free_text: null!,
    variant_free_text: null!,
    engine_id: null!,
    engine_free_text: null!,
    type: VehicleType.Passenger_vehicle,
    vehicle_model: null!,
    vehicle_model_id: null!,
    supplier_company_id: null!,
    supplier_id: null!,
    body: null!,
    fuel: null!,
    vehicle_status: null!,
    current_registration: null!,
    interior_material: null!,
    transmission: null!,
    specific_exterior_color: null!,
    specific_interior_color: null!,
    panorama: null!,
    headlights: null!,
    digital_cockpit: null!,
    cruise_control: null!,
    keyless_entry: null!,
    air_conditioning: null!,
    pdc: null!,
    second_wheels: null!,
    camera: null!,
    tow_bar: null!,
    seat_heating: null!,
    seat_massage: null!,
    optics: null!,
    tinted_windows: null!,
    sports_package: null!,
    color_type: null!,
    warranty: null!,
    navigation: null!,
    sports_seat: null!,
    seats_electrically_adjustable: null!,
    app_connect: null!,
    warranty_free_text: null!,
    navigation_free_text: null!,
    app_connect_free_text: null!,
    panorama_free_text: null!,
    headlights_free_text: null!,
    digital_cockpit_free_text: null!,
    cruise_control_free_text: null!,
    keyless_entry_free_text: null!,
    air_conditioning_free_text: null!,
    pdc_free_text: null!,
    second_wheels_free_text: null!,
    camera_free_text: null!,
    tow_bar_free_text: null!,
    sports_seat_free_text: null!,
    seats_electrically_adjustable_free_text: null!,
    seat_heating_free_text: null!,
    seat_massage_free_text: null!,
    optics_free_text: null!,
    tinted_windows_free_text: null!,
    sports_package_free_text: null!,
    highlight_1: null!,
    highlight_2: null!,
    highlight_3: null!,
    highlight_4: null!,
    highlight_5: null!,
    highlight_6: null!,
    komm_number: null!,
    factory_name_color: null!,
    factory_name_interior: null!,
    transmission_free_text: null!,
    vehicle_reference: null!,
    configuration_number: null!,
    kilometers: null!,
    production_weeks: null!,
    expected_delivery_weeks: null!,
    expected_leadtime_for_delivery_from: null!,
    expected_leadtime_for_delivery_to: null!,
    registration_weeks_from: null!,
    registration_weeks_to: null!,
    currency_exchange_rate: null!,
    option: null!,
    is_vat: true,
    is_locked: false,
    intermediate: false,
    original_currency: null!,
    selling_price_supplier: null!,
    sell_price_currency_euro: null!,
    vat_percentage: usePage().props?.auth.company.vat_percentage,
    net_purchase_price: null!,
    fee_intermediate: null!,
    total_purchase_price: null!,
    costs_of_damages: props.vehicleDefaults?.costs_of_damages,
    transport_inbound: props.vehicleDefaults?.transport_inbound,
    transport_outbound: props.vehicleDefaults?.transport_outbound,
    costs_of_taxation: props.vehicleDefaults?.costs_of_taxation,
    recycling_fee: props.vehicleDefaults?.recycling_fee,
    sales_margin: props.vehicleDefaults?.sales_margin,
    total_costs_with_fee: null!,
    sales_price_net: null!,
    vat: null!,
    sales_price_incl_vat_or_margin: null!,
    rest_bpm_indication: null!,
    leges_vat: props.vehicleDefaults?.leges_vat,
    sales_price_total: null!,
    gross_bpm: null!,
    bpm_percent: null!,
    bpm: null!,

    internalImages: [],
    externalImages: [],
    internalFiles: [],
    externalFiles: [],

    internal_remark_user_ids: [],
    internal_remark_role_ids: [],
    internal_remark: null!,
};

const form = useForm<PreOrderVehicleForm>(modelDefaults);

const resetMakeRelatedFields = (fields: string[]) => {
    for (const field of fields) {
        form[field] = null!;
    }
};

const save = () => {
    validate(form, preOrderVehicleFormRules);

    return new Promise<void>((resolve, reject) => {
        form.post(route("pre-order-vehicles.store"), {
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
    <Head :title="__('Pre Order Vehicle Create')" />

    <AppLayout>
        <Header :text="__('Pre Order Vehicle Create')" />

        <div class="flex justify-center">
            <div class="w-full max-w-[1721px]">
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
                                :images="[]"
                                text-classes="py-14"
                                :text="__('Internal Images')"
                            />

                            <InputImage
                                id="external-images"
                                v-model="form.externalImages"
                                :images="[]"
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
                                :files="[]"
                                :text="__('Internal Files')"
                                text-classes="py-14"
                            />

                            <InputFile
                                id="external-files"
                                v-model="form.externalFiles"
                                :files="[]"
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

                <InternalRemarks
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
