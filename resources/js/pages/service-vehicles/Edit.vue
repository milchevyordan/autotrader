<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";

import Header from "@/components/Header.vue";
import ConnectedModules from "@/components/html/ConnectedModules.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import CreateModules from "@/components/service-vehicles/CreateModules.vue";
import Vehicle from "@/components/service-vehicles/Vehicle.vue";
import { Multiselect } from "@/data-table/types";
import AppLayout from "@/layouts/AppLayout.vue";
import { serviceVehicleFormRules } from "@/rules/service-vehicle-form-rules";
import {
    Make,
    Ownership,
    ServiceVehicleForm,
    User,
    Variant,
    VehicleModel,
} from "@/types";
import { validate } from "@/validations";

const props = defineProps<{
    serviceVehicle: ServiceVehicleForm;
    makes: Multiselect<Make>;
    vehicleModels?: Multiselect<VehicleModel>;
    variants?: Multiselect<Variant>;
    acceptedOwnership: Ownership;
    pendingOwnerships: Ownership[];
    mainCompanyUsers: Multiselect<User>;
}>();

const form = useForm<ServiceVehicleForm>({
    _method: "put",
    id: props.serviceVehicle.id,
    owner_id: props.acceptedOwnership?.user_id,
    vehicle_type: props.serviceVehicle.vehicle_type,
    current_registration: props.serviceVehicle.current_registration,
    co2_type: props.serviceVehicle.co2_type,
    co2_value: props.serviceVehicle.co2_value,
    kilometers: props.serviceVehicle.kilometers,
    vin: props.serviceVehicle.vin,
    first_registration_date: props.serviceVehicle.first_registration_date,
    make_id: props.serviceVehicle.make_id,
    vehicle_model_id: props.serviceVehicle.vehicle_model_id,
    variant_id: props.serviceVehicle.variant_id,
});

const save = () => {
    validate(form, serviceVehicleFormRules);

    form.put(route("service-vehicles.update", props.serviceVehicle.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {},
        onError: () => {
            form.reset();
        },
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
                        pendingOwnerships: pendingOwnerships,
                    }"
                />

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
                    <ConnectedModules
                        :service-order="serviceVehicle.service_order"
                        :transport-orders="serviceVehicle.transport_orders"
                        :workflow="serviceVehicle.workflow"
                        :work-order="serviceVehicle.work_order"
                        :documents="serviceVehicle.documents"
                    />
                    <CreateModules
                        :service-vehicle-id="serviceVehicle.id"
                        :service-order="serviceVehicle.service_order"
                        :transport-orders="serviceVehicle.transport_orders"
                        :work-order="serviceVehicle.work_order"
                        :documents="serviceVehicle.documents"
                    />
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
