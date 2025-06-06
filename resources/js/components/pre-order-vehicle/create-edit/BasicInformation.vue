<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { ref } from "vue";

import Accordion from "@/components/Accordion.vue";
import Input from "@/components/html/Input.vue";
import Section from "@/components/html/Section.vue";
import Select from "@/components/Select.vue";
import { Multiselect } from "@/data-table/types";
import { Country } from "@/enums/Country";
import { VehicleStatus } from "@/enums/VehicleStatus";
import { Company, PreOrderVehicleForm, SelectInput, User } from "@/types";

const props = defineProps<{
    form: PreOrderVehicleForm;
    supplierCompanies: Multiselect<Company>;
    suppliers?: Multiselect<User>;
}>();

const reset = ref<{
    supplier: boolean;
    vehicleStatusId: boolean;
}>({
    supplier: false,
    vehicleStatusId: false,
});

const handleValueUpdated = async (value: SelectInput): Promise<void> => {
    switch (value.name) {
        case "supplier_company_id":
            await new Promise((resolve, reject) => {
                router.reload({
                    data: { supplier_company_id: value.value },
                    only: ["suppliers"],
                    onSuccess: resolve,
                    onError: reject,
                });
            });

            reset.value.supplier = true;
            break;

        case "current_registration":
            if (!value.value) {
                props.form.vehicle_status =
                    VehicleStatus.New_without_registration;
            } else {
                if (
                    props.form.vehicle_status ==
                    VehicleStatus.New_without_registration
                ) {
                    reset.value.vehicleStatusId = true;
                }
            }
            break;

        default:
            reset.value.supplier = false;
            break;
    }
};
</script>

<template>
    <Section classes="p-4 mt-4">
        <Accordion>
            <template #head>
                <div class="font-semibold text-xl sm:text-2xl mb-4">
                    {{ __("Basic Information") }}: {{ form.id }}
                </div>
            </template>

            <div class="grid lg:grid-cols-1 xl:grid-cols-2 gap-4">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:border-r border-[#E9E7E7] xl:pr-8 sm:gap-y-2 items-center"
                >
                    <label for="vehicle_status">
                        {{ __("Vehicle status at purchase") }}
                    </label>
                    <Select
                        :key="form.vehicle_status"
                        v-model="form.vehicle_status"
                        :name="'vehicle_status'"
                        :options="VehicleStatus"
                        :reset="reset.vehicleStatusId"
                        :placeholder="__('Vehicle status at purchase')"
                        class="w-full mb-3.5 sm:mb-0"
                    />

                    <label for="vehicle_reference">
                        {{ __("Vehicle reference (custom)") }}
                    </label>
                    <Input
                        v-model="form.vehicle_reference"
                        :name="'vehicle_reference'"
                        type="text"
                        :placeholder="__('Vehicle reference (custom)')"
                        class="mb-3.5 sm:mb-0"
                    />
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:pl-4 sm:gap-y-2 items-center"
                >
                    <label for="supplier_company_id">
                        {{ __("Supplier") }}
                    </label>
                    <Select
                        v-model="form.supplier_company_id"
                        :name="'supplier_company_id'"
                        :options="supplierCompanies"
                        :placeholder="__('Supplier')"
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleValueUpdated"
                        @remove="reset.supplier = true"
                    />

                    <label for="supplier_id">
                        {{ __("Supplier contact") }}
                    </label>
                    <Select
                        v-model="form.supplier_id"
                        :name="'supplier_id'"
                        :options="suppliers"
                        :reset="reset.supplier"
                        :placeholder="__('Contact person supplier')"
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleValueUpdated"
                    />

                    <label for="current_registration">
                        {{ __("Current registration") }}
                    </label>
                    <Select
                        v-model="form.current_registration"
                        :name="'current_registration'"
                        :options="Country"
                        :placeholder="__('Vehicle registration')"
                        :disabled="
                            form.vehicle_status ==
                            VehicleStatus.New_without_registration
                        "
                        :reset="
                            form.vehicle_status ==
                            VehicleStatus.New_without_registration
                        "
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleValueUpdated"
                    />
                </div>
            </div>
        </Accordion>
    </Section>
</template>
