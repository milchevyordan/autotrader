<script setup lang="ts">
import { Head, useForm, usePage } from "@inertiajs/vue3";

import Header from "@/components/Header.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import Vehicle from "@/components/service-vehicles/Vehicle.vue";
import { Multiselect } from "@/data-table/types";
import { VehicleType } from "@/enums/VehicleType";
import AppLayout from "@/layouts/AppLayout.vue";
import { serviceVehicleFormRules } from "@/rules/service-vehicle-form-rules";
import { Make, ServiceVehicleForm, User, Variant, VehicleModel } from "@/types";
import { validate } from "@/validations";

const props = defineProps<{
    makes: Multiselect<Make>;
    vehicleModels?: Multiselect<VehicleModel>;
    variants?: Multiselect<Variant>;
    mainCompanyUsers: Multiselect<User>;
}>();

const form = useForm<ServiceVehicleForm>({
    id: null!,
    owner_id: usePage().props.auth.user.id,
    vehicle_type: VehicleType.Passenger_vehicle,
    make_id: null!,
    vehicle_model_id: null!,
    variant_id: null!,
    current_registration: null!,
    co2_type: null!,
    co2_value: null!,
    kilometers: null!,
    vin: null!,
    first_registration_date: null!,
});

const save = () => {
    validate(form, serviceVehicleFormRules);

    form.post(route("service-vehicles.store"), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {},
    });
};
</script>

<template>
    <Head :title="__('Service Vehicle')" />

    <AppLayout>
        <Header :text="__('Service Vehicle')" />

        <div class="flex justify-center">
            <div class="w-full max-w-[1721px]">
                <Vehicle
                    :data="props"
                    :form="form"
                    :owner-props="{
                        mainCompanyUsers: mainCompanyUsers,
                    }"
                />
            </div>
        </div>

        <ResetSaveButtons
            :processing="form.processing"
            @reset="form.reset()"
            @save="save"
        />
    </AppLayout>
</template>
