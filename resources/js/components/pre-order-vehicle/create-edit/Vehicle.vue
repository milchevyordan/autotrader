<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { reactive } from "vue";

import Input from "@/components/html/Input.vue";
import Select from "@/components/Select.vue";
import { ColorType } from "@/enums/ColorType";
import { ExteriorColour } from "@/enums/ExteriorColour";
import { FuelType } from "@/enums/FuelType";
import { InteriorColour } from "@/enums/InteriorColour";
import { InteriorMaterial } from "@/enums/InteriorMaterial";
import { Transmission } from "@/enums/Transmission";
import { VehicleBody } from "@/enums/VehicleBody";
import { VehicleType } from "@/enums/VehicleType";
import { PreOrderVehicleForm, SelectInput, VehicleOptionsData } from "@/types";

const emit = defineEmits(["resetMakeRelatedFields"]);

defineProps<{
    vehicleOptionsData: VehicleOptionsData;
    form: PreOrderVehicleForm;
}>();

const reset = reactive<{
    model: boolean;
    variant: boolean;
    engine: boolean;
    fuel: boolean;
}>({
    model: false,
    variant: false,
    engine: false,
    fuel: false,
});

const handleValueUpdated = async (input: SelectInput) => {
    switch (input.name) {
        case "make_id":
            await new Promise<void>((resolve, reject) => {
                router.reload({
                    data: {
                        make_id: input.value,
                        fuel: null,
                        engine_id: null,
                    },
                    only: ["vehicleModel", "engine", "variant"],
                    onSuccess: () => {
                        emit("resetMakeRelatedFields", [
                            "fuel",
                            "engine_id",
                            "variant_id",
                            "vehicle_model_id",
                        ]);

                        reset.model = true;
                        reset.variant = true;
                        reset.engine = true;
                        reset.fuel = true;

                        resolve();
                    },
                    onError: () => reject(),
                });
            });

            break;

        case "fuel":
            await new Promise((resolve, reject) => {
                router.reload({
                    data: { fuel: input.value },
                    only: ["engine"],
                    onSuccess: resolve,
                    onError: reject,
                });
            });

            if (reset.fuel || input.value === null) {
                reset.fuel = false;
            }

            reset.engine = true;

            emit("resetMakeRelatedFields", ["engine_id"]);

            break;

        default:
            reset.model = false;
            reset.variant = false;
            reset.engine = false;
            reset.fuel = false;
    }
};
</script>

<template>
    <label for="type">
        {{ __("Vehicle type") }}
        <span class="text-red-500"> *</span>
    </label>
    <Select
        v-model="form.type"
        :name="'type'"
        :options="VehicleType"
        :placeholder="__('Vehicle type')"
        class="w-full mb-3.5 sm:mb-0 col-span-2"
    />

    <label for="make_id">
        {{ __("Make/Brand") }}
        <span class="text-red-500"> *</span>
    </label>
    <Select
        v-model="form.make_id"
        :name="'make_id'"
        :options="vehicleOptionsData.make"
        :placeholder="__('Brand')"
        class="w-full mb-3.5 sm:mb-0 col-span-2"
        @select="handleValueUpdated"
    />

    <label for="vehicle_model_id">
        {{ __("Model") }}
    </label>
    <div class="flex col-span-2 gap-2">
        <Select
            v-model="form.vehicle_model_id"
            :name="'vehicle_model_id'"
            :options="vehicleOptionsData.vehicleModel"
            :reset="reset.model"
            :placeholder="__('Model')"
            class="w-full mb-3.5 sm:mb-0 col-span-2"
            @select="handleValueUpdated"
        />

        <Input
            v-model="form.vehicle_model_free_text"
            :name="'model_free_text'"
            type="text"
            :placeholder="__('Model Free Text')"
            class="mb-3.5 sm:mb-0"
        />
    </div>

    <label for="currency_exchange_rate">
        {{ __("Currency exchange rate") }}
    </label>
    <Input
        v-model="form.currency_exchange_rate"
        :name="'currency_exchange_rate'"
        type="number"
        :placeholder="__('Currency exchange rate')"
        class="mb-3.5 sm:mb-0 col-span-2"
    />

    <label for="variant_id">
        {{ __("Variant") }}
    </label>
    <div class="flex col-span-2 gap-2">
        <Select
            v-model="form.variant_id"
            :name="'variant_id'"
            :options="vehicleOptionsData.variant"
            :reset="reset.variant"
            :placeholder="__('Variant')"
            class="w-full mb-3.5 sm:mb-0 col-span-2"
            @select="handleValueUpdated"
        />

        <Input
            v-model="form.variant_free_text"
            :name="'variant_free_text'"
            type="text"
            :placeholder="__('Variant Free Text')"
            class="mb-3.5 sm:mb-0"
        />
    </div>

    <label for="fuel">
        {{ __("Fuel type") }}
        <span class="text-red-500"> *</span>
    </label>
    <Select
        :key="form.fuel"
        v-model="form.fuel"
        :name="'fuel'"
        :options="FuelType"
        :reset="reset.fuel"
        :placeholder="__('Fuel type')"
        class="w-full mb-3.5 sm:mb-0 col-span-2"
        @select="handleValueUpdated"
    />

    <label for="engine_id">
        {{ __("Engine") }}
        <span class="text-red-500"> *</span>
    </label>
    <div class="flex col-span-2 gap-2">
        <Select
            :key="form.engine_id"
            v-model="form.engine_id"
            :name="'engine_id'"
            :options="vehicleOptionsData.engine"
            :reset="reset.engine"
            :placeholder="__('Engine')"
            class="w-full mb-3.5 sm:mb-0"
            @select="handleValueUpdated"
        />

        <Input
            v-model="form.engine_free_text"
            :name="'engine_free_text'"
            type="text"
            :placeholder="__('Engine Free Text')"
            class="mb-3.5 sm:mb-0"
        />
    </div>

    <label for="transmission">
        {{ __("Transmission") }}
        <span class="text-red-500"> *</span>
    </label>
    <div class="flex col-span-2 gap-2">
        <Select
            v-model="form.transmission"
            :name="'transmission'"
            :options="Transmission"
            :placeholder="__('Transmission')"
            class="w-full mb-3.5 sm:mb-0"
        />

        <Input
            v-model="form.transmission_free_text"
            :name="'transmission_free_text'"
            type="text"
            :placeholder="__('Transmission details')"
            class="mb-3.5 sm:mb-0"
        />
    </div>

    <label for="body">{{ __("Body style") }}</label>
    <Select
        v-model="form.body"
        :name="'body'"
        :options="VehicleBody"
        :placeholder="__('Body style')"
        class="w-full mb-3.5 sm:mb-0 col-span-2"
    />

    <label for="kilometers">
        {{ __("Kilometers") }}
    </label>
    <Input
        v-model="form.kilometers"
        :name="'kilometers'"
        type="number"
        step="1"
        :placeholder="__('Kilometers')"
        class="mb-3.5 sm:mb-0 col-span-2"
    />

    <label for="specific_exterior_color">
        {{ __("Color") }}
    </label>
    <Select
        v-model="form.specific_exterior_color"
        :name="'specific_exterior_color'"
        :options="ExteriorColour"
        :placeholder="__('Color')"
        :capitalize="true"
        class="w-full mb-3.5 sm:mb-0 col-span-2"
    />

    <label for="color_type">
        {{ __("Color Type") }}
    </label>
    <div class="flex col-span-2 gap-2">
        <Select
            v-model="form.color_type"
            :name="'color_type'"
            :options="ColorType"
            :placeholder="__('Color Type')"
            class="w-full mb-3.5 sm:mb-0"
        />
        <Input
            v-model="form.factory_name_color"
            :name="'factory_name_color'"
            type="text"
            :placeholder="__('Factory name color')"
            class="mb-3.5 sm:mb-0"
        />
    </div>

    <label for="specific_interior_color">
        {{ __("Interior Color") }}
    </label>
    <div class="flex col-span-2 gap-2">
        <Select
            v-model="form.specific_interior_color"
            :name="'specific_interior_color'"
            :options="InteriorColour"
            :placeholder="__('Color')"
            :capitalize="true"
            class="w-full mb-3.5 sm:mb-0 col-span-2"
        />
        <Input
            v-model="form.factory_name_interior"
            :name="'factory_name_interior'"
            type="text"
            :placeholder="__('Factory name interior')"
            class="mb-3.5 sm:mb-0"
        />
    </div>

    <label for="interior_material">
        {{ __("Interior Material") }}
    </label>
    <Select
        v-model="form.interior_material"
        :name="'interior_material'"
        :options="InteriorMaterial"
        :placeholder="__('Interior Material')"
        class="w-full mb-3.5 sm:mb-0 col-span-2"
    />

    <label for="komm_number">
        {{ __("Komm Number") }}
    </label>
    <Input
        v-model="form.komm_number"
        :name="'komm_number'"
        type="text"
        :placeholder="__('Komm Number')"
        class="mb-3.5 sm:mb-0 col-span-2"
    />

    <label for="configuration_number">
        {{ __("Configuration Number") }}
    </label>
    <Input
        v-model="form.configuration_number"
        :name="'configuration_number'"
        type="text"
        :placeholder="__('Configuration Number')"
        class="mb-3.5 sm:mb-0 col-span-2"
    />
</template>
