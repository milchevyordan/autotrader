<script setup lang="ts">
import { InertiaForm } from "@inertiajs/vue3";
import { ref } from "vue";

import Accordion from "@/components/Accordion.vue";
import Input from "@/components/html/Input.vue";
import PaymentCondition from "@/components/html/PaymentCondition.vue";
import RadioButtonToggle from "@/components/html/RadioButtonToggle.vue";
import Select from "@/components/Select.vue";
import SelectMultiple from "@/components/SelectMultiple.vue";
import { Multiselect } from "@/data-table/types";
import { Damage } from "@/enums/Damage";
import { ImportOrOriginType } from "@/enums/ImportOrOriginType";
import { ServiceLevelType } from "@/enums/ServiceLevelType";
import { Company, SelectInput, ServiceLevel, ServiceLevelForm } from "@/types";
import {
    findEnumKeyByValue,
    formatPriceOnBlur,
    formatPriceOnFocus,
} from "@/utils";

const props = defineProps<{
    form: InertiaForm<ServiceLevelForm>;
    serviceLevel?: ServiceLevel;
    formDisabled?: boolean;
    crmCompanies?: Multiselect<Company>;
}>();

const reset = ref<{
    crmCompanies: boolean;
}>({
    crmCompanies: false,
});

const handleValueUpdated = (input: SelectInput): void => {
    switch (input.name) {
        case "type":
            if (
                props.form.type === ServiceLevelType.Client &&
                input.value == ServiceLevelType.System
            ) {
                reset.value.crmCompanies = true;
            }
            break;

        default:
            reset.value.crmCompanies = false;
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
                    {{ __("General Information") }}: {{ form.id }}
                </div>
            </template>

            <template #collapsedContent>
                <div
                    class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-y-3 xl:gap-y-0"
                >
                    <div class="border-r-2 border-[#E9E7E7] border-dashed">
                        <div class="font-medium text-[#676666]">
                            {{ __("Service Level Name") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ form.name }}
                        </div>
                    </div>

                    <div
                        class="md:ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Type") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{
                                findEnumKeyByValue(ServiceLevelType, form.type)
                            }}
                        </div>
                    </div>
                </div>
            </template>

            <div class="grid lg:grid-cols-1 xl:grid-cols-2 gap-4">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:border-r border-[#E9E7E7] xl:pr-8 sm:gap-y-2 items-center"
                >
                    <label for="name">
                        {{ __("Name") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Input
                        v-model="form.name"
                        :name="'name'"
                        :placeholder="__('Add Name')"
                        type="text"
                        class="w-full mb-3.5 sm:mb-0"
                    />

                    <label for="type">
                        {{ __("Type of service level") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        v-model="form.type"
                        :name="'type'"
                        :options="ServiceLevelType"
                        :disabled="formDisabled"
                        :placeholder="__('Type of service level')"
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleValueUpdated"
                    />

                    <label for="companies">
                        {{ __("Companies connected") }}
                        <span
                            v-if="form.type != ServiceLevelType.System"
                            class="text-red-500"
                        >
                            *
                        </span>
                    </label>

                    <SelectMultiple
                        v-model="form.companies"
                        :name="'companies'"
                        :options="crmCompanies"
                        :placeholder="__('Companies connected')"
                        class="w-full mb-3.5 sm:mb-0"
                        :reset="reset.crmCompanies"
                        :disabled="
                            formDisabled || form.type != ServiceLevelType.Client
                        "
                    />
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:pl-4 sm:gap-y-2 items-center"
                >
                    <label for="type_of_sale">
                        {{ __("Type of sale") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        v-model="form.type_of_sale"
                        :name="'type_of_sale'"
                        :options="ImportOrOriginType"
                        :placeholder="__('Type of sale')"
                        class="w-full"
                    />

                    <label for="transport_included">
                        {{ __("Transport included") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <RadioButtonToggle
                        v-model="form.transport_included"
                        :left-button-label="__('Included')"
                        :right-button-label="__('Pick up by buyer')"
                        name="transport_included"
                    />

                    <label for="damage">
                        {{ __("Damage") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        :key="form.damage"
                        v-model="form.damage"
                        :name="'damage'"
                        :options="Damage"
                        :placeholder="__('Damage')"
                        class="w-full"
                    />

                    <PaymentCondition :form="form" />

                    <label for="discount_in_output">
                        {{ __("Discount in output") }}
                    </label>
                    <RadioButtonToggle
                        v-model="form.discount_in_output"
                        name="discount_in_output"
                    />

                    <label for="discount">
                        {{ __("Discount") }}
                    </label>
                    <Input
                        v-model="form.discount"
                        :name="'discount'"
                        type="text"
                        :placeholder="__('Discount')"
                        class="mb-3.5 sm:mb-0"
                        @focus="formatPriceOnFocus(form, 'discount')"
                        @blur="formatPriceOnBlur(form, 'discount')"
                    />
                </div>
            </div>
        </Accordion>
    </div>
</template>
