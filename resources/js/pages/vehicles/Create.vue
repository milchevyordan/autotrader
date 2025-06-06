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
import BasicInformation from "@/components/vehicle/create-edit/BasicInformation.vue";
import BPM from "@/components/vehicle/create-edit/BPM.vue";
import Damages from "@/components/vehicle/create-edit/Damages.vue";
import Dates from "@/components/vehicle/create-edit/Dates.vue";
import Vehicle from "@/components/vehicle/create-edit/Vehicle.vue";
import { Multiselect } from "@/data-table/types";
import { VehicleStatus } from "@/enums/VehicleStatus";
import { VehicleType } from "@/enums/VehicleType";
import AppLayout from "@/layouts/AppLayout.vue";
import { vehicleFormRules } from "@/rules/vehicle-form-rules";
import {
    Bpm,
    Company,
    Engine,
    Make,
    Role,
    Setting,
    User,
    Variant,
    VehicleGroup,
    VehicleModel,
} from "@/types";
import { VehicleForm } from "@/types";
import { validate } from "@/validations";

const props = defineProps<{
    make: Multiselect<Make>;
    engine?: Multiselect<Engine>;
    variant?: Multiselect<Variant>;
    vehicleModel?: Multiselect<VehicleModel>;
    vehicleGroup: Multiselect<VehicleGroup>;
    vehicleDefaults: null | Setting;
    userCompany: null | Company;
    bpmValues?: Bpm;
    suppliers?: Multiselect<User>;
    supplierCompanies: Multiselect<Company>;
    mainCompanyRoles?: Multiselect<Role>;
    mainCompanyUsers: Multiselect<User>;
}>();

const modelDefaults: VehicleForm = {
    id: null!,
    owner_id: usePage().props.auth.user.id,
    make_id: null!,
    vehicle_model_id: null!,
    variant_id: null!,
    vehicle_model_free_text: null!,
    variant_free_text: null!,
    engine_id: null!,
    engine_free_text: null!,
    vehicle_group_id: null!,
    type: VehicleType.Passenger_vehicle,
    body: null!,
    fuel: null!,
    vehicle_status: VehicleStatus.Used,
    stock: null!,
    current_registration: null!,
    coc: null!,
    interior_material: null!,
    transmission: null!,
    transmission_free_text: null!,
    specific_exterior_color: null!,
    factory_name_color: null!,
    specific_interior_color: null!,
    factory_name_interior: null!,
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
    hp: null!,
    kilometers: null!,
    currency_exchange_rate: null!,
    kw: null!,
    advert_link: null!,
    vehicle_reference: null!,
    co2_wltp: null!,
    co2_nedc: null!,
    vat_percentage: usePage().props?.auth.company.vat_percentage,
    is_ready_to_be_sold: false,
    purchase_repaired_damage: false,
    is_vat: true,
    is_locked: false,
    intermediate: false,
    original_currency: null!,
    selling_price_supplier: null!,
    sell_price_currency_euro: null!,
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
    nl_registration_number: null!,
    supplier_company_id: null!,
    supplier_id: null!,
    supplier_reference_number: null!,
    supplier_given_damages: null!,
    gross_bpm_new: null!,
    rest_bpm_as_per_table: null!,
    calculation_bpm_in_so: null!,
    bpm_declared: null!,
    gross_bpm_recalculated_based_on_declaration: null!,
    gross_bpm_on_registration: null!,
    rest_bpm_to_date: null!,
    invoice_bpm: null!,
    bpm_post_change_amount: null!,
    vin: null!,
    option: null!,
    color_type: null!,
    damage_description: null!,
    expected_date_of_availability_from_supplier: {
        from: null!,
        to: null!,
    },
    expected_leadtime_for_delivery_from: null!,
    expected_leadtime_for_delivery_to: null!,
    first_registration_date: null!,
    first_registration_nl: null!,
    registration_nl: null!,
    registration_date_approval: null!,
    last_name_registration: null!,
    first_name_registration_nl: null!,
    registration_valid_until: null!,
    highlight_1: null!,
    highlight_2: null!,
    highlight_3: null!,
    highlight_4: null!,
    highlight_5: null!,
    highlight_6: null!,

    internalImages: [],
    externalImages: [],
    internalFiles: [],
    externalFiles: [],

    internal_remark_user_ids: [],
    internal_remark_role_ids: [],
    internal_remark: null!,
};

const form = useForm<VehicleForm>(modelDefaults);

const resetMakeRelatedFields = (fields: string[]) => {
    for (const field of fields) {
        form[field] = null!;
    }
};

const save = () => {
    validate(form, vehicleFormRules);

    return new Promise<void>((resolve, reject) => {
        form.post(route("vehicles.store"), {
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
    <Head :title="__('Vehicle')" />

    <AppLayout>
        <Header :text="__('Vehicle')" />

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
                                v-model="form.internalImages"
                                :images="[]"
                                text-classes="py-14"
                                :text="__('Internal Images')"
                            />

                            <InputImage
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
                    :owner-props="{
                        mainCompanyUsers: mainCompanyUsers,
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

                <BPM :form="form" />

                <InternalRemarks
                    :form="form"
                    :main-company-roles="mainCompanyRoles"
                    :main-company-users="mainCompanyUsers"
                />

                <Dates :form="form" />

                <FactoryOptionsHighlights :form="form" />

                <Options :form="form" />

                <Damages :form="form" />
            </div>
        </div>

        <ResetSaveButtons
            :processing="form.processing"
            @reset="form.reset()"
            @save="save"
        />
    </AppLayout>
</template>
