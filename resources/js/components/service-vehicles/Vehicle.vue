<script setup lang="ts">
import { InertiaForm, router } from "@inertiajs/vue3";
import { ref } from "vue";

import Accordion from "@/components/Accordion.vue";
import DatePicker from "@/components/html/DatePicker.vue";
import Input from "@/components/html/Input.vue";
import OwnerSelect from "@/components/OwnerSelect.vue";
import Select from "@/components/Select.vue";
import { Multiselect } from "@/data-table/types";
import { Co2Type } from "@/enums/Co2Type";
import { Country } from "@/enums/Country";
import { VehicleType } from "@/enums/VehicleType";
import {
    Make,
    ServiceVehicleForm,
    VehicleModel,
    Variant,
    SelectInput,
    OwnerProps,
} from "@/types";
import { dateToLocaleString, findKeyByValue } from "@/utils.js";

defineProps<{
    data: {
        serviceVehicle?: ServiceVehicleForm;
        makes: Multiselect<Make>;
        vehicleModels?: Multiselect<VehicleModel>;
        variants?: Multiselect<Variant>;
    };
    ownerProps: OwnerProps;
    form: InertiaForm<ServiceVehicleForm>;
    formDisabled?: boolean;
}>();

const reset = ref<{
    model: boolean;
    variant: boolean;
}>({
    model: false,
    variant: false,
});

const handleSelectUpdated = async (input: SelectInput): Promise<void> => {
    switch (input.name) {
        case "make_id":
            await new Promise((resolve, reject) => {
                router.reload({
                    data: { make_id: input.value },
                    only: ["vehicleModels", "variants"],
                    onSuccess: resolve,
                    onError: reject,
                });
            });

            reset.value.model = true;
            reset.value.variant = true;
            break;

        default:
            reset.value.model = false;
            reset.value.variant = false;
            break;
    }
};
</script>

<template>
    <div
        class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
    >
        <Accordion>
            <template #head>
                <div class="font-semibold text-xl sm:text-2xl mb-4">
                    {{ __("Vehicle Entry") }}: {{ form.id }}
                </div>
            </template>

            <template #collapsedContent>
                <div
                    class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-y-3 xl:gap-y-0"
                >
                    <div class="border-r-2 border-[#E9E7E7] border-dashed">
                        <div class="font-medium text-[#676666]">
                            {{ __("Sales order number") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ form.id }}
                        </div>
                    </div>

                    <div
                        class="md:ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Make") }}
                        </div>

                        <div
                            v-if="data?.serviceVehicle"
                            class="font-medium text-lg"
                        >
                            {{
                                findKeyByValue(
                                    data.makes,
                                    data?.serviceVehicle?.make_id
                                )
                            }}
                        </div>
                    </div>

                    <div
                        class="ml-12 md:ml-0 xl:ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Model") }}
                        </div>

                        <div
                            v-if="data?.serviceVehicle"
                            class="font-medium text-lg"
                        >
                            {{
                                findKeyByValue(
                                    data.vehicleModels,
                                    data?.serviceVehicle?.vehicle_model_id
                                )
                            }}
                        </div>
                    </div>

                    <div
                        class="md:ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("First Registration Date") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{
                                dateToLocaleString(
                                    data?.serviceVehicle
                                        ?.first_registration_date
                                )
                            }}
                        </div>
                    </div>
                </div>
            </template>

            <div class="grid lg:grid-cols-1 xl:grid-cols-2 gap-4">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:border-r border-[#E9E7E7] xl:pr-8 sm:gap-y-2 items-center"
                >
                    <label for="vehicle_type">
                        {{ __("Vehicle type") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        v-model="form.vehicle_type"
                        :name="'vehicle_type'"
                        :options="VehicleType"
                        :disabled="formDisabled"
                        :placeholder="__('Vehicle Type')"
                        class="w-full mb-3.5 sm:mb-0"
                    />

                    <label for="make_id">
                        {{ __("Make/Brand") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        v-model="form.make_id"
                        :name="'make_id'"
                        :options="data.makes"
                        :disabled="formDisabled"
                        :placeholder="__('Brand')"
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleSelectUpdated"
                    />

                    <label for="vehicle_model_id">
                        {{ __("Model") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        v-model="form.vehicle_model_id"
                        :name="'vehicle_model_id'"
                        :options="data.vehicleModels"
                        :disabled="formDisabled"
                        :reset="reset.model"
                        :placeholder="__('Model')"
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleSelectUpdated"
                    />

                    <label for="variant_id">
                        {{ __("Variant") }}
                    </label>
                    <Select
                        v-model="form.variant_id"
                        :name="'variant_id'"
                        :options="data.variants"
                        :disabled="formDisabled"
                        :reset="reset.variant"
                        :placeholder="__('Variant')"
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleSelectUpdated"
                    />

                    <label for="current_registration">
                        {{ __("Current registration") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        v-model="form.current_registration"
                        :name="'current_registration'"
                        :options="Country"
                        :disabled="formDisabled"
                        :placeholder="__('Vehicle registration')"
                        class="w-full mb-3.5 sm:mb-0"
                    />

                    <label
                        v-if="form.current_registration === Country.Netherlands"
                        for="nl_registration_number"
                    >
                        {{ __("NL Registration Number") }}
                    </label>
                    <Input
                        v-if="form.current_registration === Country.Netherlands"
                        v-model="form.nl_registration_number"
                        :name="'nl_registration_number'"
                        type="text"
                        :placeholder="__('NL Registration Number')"
                        :disabled="formDisabled"
                        class="mb-3.5 sm:mb-0"
                    />
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:pl-4 sm:gap-y-2 items-center"
                >
                    <OwnerSelect
                        v-if="$can('create-ownership')"
                        v-model="form.owner_id"
                        :users="ownerProps.mainCompanyUsers"
                        :pending-ownerships="ownerProps.pendingOwnerships"
                    />

                    <label for="vin">
                        VIN
                        <span class="text-red-500"> *</span>
                    </label>
                    <Input
                        v-model="form.vin"
                        :name="'vin'"
                        type="text"
                        placeholder="VIN"
                        :disabled="formDisabled"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="first_registration_date">
                        {{ __("First registration date") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <DatePicker
                        v-model="form.first_registration_date"
                        :name="'first_registration_date'"
                        :enable-time-picker="false"
                        :disabled="formDisabled"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="kilometers">
                        {{ __("Kilometers") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Input
                        v-model="form.kilometers"
                        :name="'kilometers'"
                        type="number"
                        step="1"
                        :placeholder="__('Kilometers')"
                        :disabled="formDisabled"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="co2_type">
                        {{ __("CO2 Type") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        v-model="form.co2_type"
                        :name="'co2_type'"
                        :options="Co2Type"
                        :disabled="formDisabled"
                        :placeholder="__('CO2 Type')"
                        class="w-full mb-3.5 sm:mb-0"
                    />

                    <label for="co2_value"
                        >CO2
                        <span class="text-red-500"> *</span>
                    </label>
                    <Input
                        v-model="form.co2_value"
                        :name="'co2_value'"
                        type="text"
                        placeholder="CO2"
                        :disabled="formDisabled"
                        class="mb-3.5 sm:mb-0"
                    />
                </div>
            </div>
        </Accordion>
    </div>
</template>
