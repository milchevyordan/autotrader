<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { ref } from "vue";

import Accordion from "@/components/Accordion.vue";
import Input from "@/components/html/Input.vue";
import RadioButtonToggle from "@/components/html/RadioButtonToggle.vue";
import Section from "@/components/html/Section.vue";
import OwnerSelect from "@/components/OwnerSelect.vue";
import Select from "@/components/Select.vue";
import { Country } from "@/enums/Country";
import { VehicleStatus } from "@/enums/VehicleStatus";
import { VehicleStock } from "@/enums/VehicleStock";
import {
    Company,
    Multiselect,
    OwnerProps,
    SelectInput,
    User,
    VehicleForm,
} from "@/types";
import { formatPriceOnBlur, formatPriceOnFocus } from "@/utils";

const props = defineProps<{
    form: VehicleForm;
    supplierCompanies: Multiselect<Company>;
    suppliers?: Multiselect<User>;
    ownerProps: OwnerProps;
}>();

const reset = ref<{
    supplier: boolean;
    vehicle_status: boolean;
}>({
    supplier: false,
    vehicle_status: false,
});

const handleValueUpdated = async (input: SelectInput): Promise<void> => {
    switch (input.name) {
        case "supplier_company_id":
            await new Promise((resolve, reject) => {
                router.reload({
                    data: { supplier_company_id: input.value },
                    only: ["suppliers"],
                    onSuccess: resolve,
                    onError: reject,
                });
            });

            reset.value.supplier = true;
            break;

        case "current_registration":
            if (!input.value) {
                props.form.vehicle_status =
                    VehicleStatus.New_without_registration;
            } else {
                if (
                    props.form.vehicle_status ==
                    VehicleStatus.New_without_registration
                ) {
                    reset.value.vehicle_status = true;
                }
            }
            break;

        default:
            reset.value.supplier = false;
            reset.value.vehicle_status = false;
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
                    <label for="is_ready_to_be_sold">
                        {{ __("Ready to be sold") }}
                    </label>
                    <RadioButtonToggle
                        v-model="form.is_ready_to_be_sold"
                        name="is_ready_to_be_sold"
                    />

                    <label for="purchase_repaired_damage">
                        {{ __("Purchase repaired damage") }}
                    </label>
                    <RadioButtonToggle
                        v-model="form.purchase_repaired_damage"
                        name="purchase_repaired_damage"
                    />

                    <label for="vehicle_status">
                        {{ __("Vehicle status at purchase") }}
                    </label>
                    <Select
                        v-model="form.vehicle_status"
                        :name="'vehicle_status'"
                        :options="VehicleStatus"
                        :reset="reset.vehicle_status"
                        :placeholder="__('Vehicle status at purchase')"
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleValueUpdated"
                    />

                    <label for="stock">
                        {{ __("Stock") }}
                    </label>
                    <Select
                        :model-value="form.stock"
                        :name="'stock'"
                        :options="VehicleStock"
                        :placeholder="__('Stock status')"
                        disabled
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleValueUpdated"
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

                    <label for="vin">VIN</label>
                    <Input
                        v-model="form.vin"
                        :name="'vin'"
                        type="text"
                        placeholder="VIN"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="advert_link">
                        {{ __("Link to advert/Seller Link") }}
                    </label>
                    <Input
                        v-model="form.advert_link"
                        :name="'advert_link'"
                        type="text"
                        :placeholder="__('Link to advert/Seller Link')"
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

                    <label for="supplier_reference_number">
                        {{ __("Source Supplier reference number") }}
                    </label>
                    <Input
                        v-model="form.supplier_reference_number"
                        :name="'supplier_reference_number'"
                        type="text"
                        :placeholder="__('Source Supplier reference number')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="supplier_given_damages">
                        {{ __("Supplier given damages price") }}
                    </label>
                    <Input
                        v-model="form.supplier_given_damages"
                        :name="'supplier_given_damages'"
                        type="text"
                        :placeholder="__('Supplier given damages price')"
                        class="mb-3.5 sm:mb-0"
                        @focus="
                            formatPriceOnFocus(form, 'supplier_given_damages')
                        "
                        @blur="
                            formatPriceOnBlur(form, 'supplier_given_damages')
                        "
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
