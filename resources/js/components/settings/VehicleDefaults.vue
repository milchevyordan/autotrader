<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";

import Input from "@/components/html/Input.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import Section from "@/components/html/Section.vue";
import { SettingForm } from "@/types";
import { formatPriceOnBlur, formatPriceOnFocus } from "@/utils";
import { validate } from "@/validations";
import PrimaryButton from "../html/PrimaryButton.vue";

const props = defineProps<{
    setting: SettingForm;
}>();

const rules = {
    costs_of_damages: {
        required: false,
        type: "price",
        min: 0,
    },
    transport_inbound: {
        required: false,
        type: "price",
        min: 0,
    },
    transport_outbound: {
        required: false,
        type: "price",
        min: 0,
    },
    costs_of_taxation: {
        required: false,
        type: "price",
        min: 0,
    },
    recycling_fee: {
        required: false,
        type: "price",
        min: 0,
    },
    sales_margin: {
        required: false,
        type: "price",
    },
    leges_vat: {
        required: false,
        type: "price",
    },
};

const updateRules = {
    id: {
        required: true,
        type: "number",
    },
    ...rules,
};

const modelDefaults: SettingForm = {
    id: null!,
    costs_of_damages: null!,
    transport_inbound: null!,
    transport_outbound: null!,
    costs_of_taxation: null!,
    recycling_fee: null!,
    sales_margin: null!,
    leges_vat: null!,
};

const storeForm = useForm<SettingForm>({
    ...(props.setting || modelDefaults),
});

const save = () => {
    if (storeForm.id) {
        validate(storeForm, updateRules);

        storeForm.put(route("setting.update", storeForm.id as number), {
            preserveScroll: true,
        });
    } else {
        validate(storeForm, rules);

        storeForm.post(route("setting.store"), {
            preserveScroll: true,
            onSuccess: (page) => {
                if (page.props && page.props.setting) {
                    Object.assign(storeForm, page.props.setting);
                }
            },
        });
    }
};
</script>

<template>
    <Section classes="py-4 my-4">
        <div class="font-semibold text-xl px-5">
            {{ __("Vehicle and Calculation Defaults") }}
        </div>

        <div class="grid grid-cols-2 mt-3 items-center">
            <div class="border-b border-[#E9E7E7] col-span-2" />
            <div class="pl-5 font-medium">
                {{ __("Cost of Damages") }}
            </div>
            <div class="flex items-center justify-end pr-5 my-1">
                <Input
                    v-model="storeForm.costs_of_damages"
                    :name="'costs_of_damages'"
                    type="text"
                    class="mb-3.5 sm:mb-0"
                    :placeholder="__('Cost of Damages')"
                    @blur="formatPriceOnBlur(storeForm, 'costs_of_damages')"
                    @focus="formatPriceOnFocus(storeForm, 'costs_of_damages')"
                />
            </div>

            <div class="border-b border-[#E9E7E7] col-span-2" />
            <div class="pl-5 font-medium">
                {{ __("Transport inbound") }}
            </div>
            <div class="flex items-center justify-end pr-5 my-1">
                <Input
                    v-model="storeForm.transport_inbound"
                    :name="'transport_inbound'"
                    type="text"
                    class="mb-3.5 sm:mb-0"
                    :placeholder="__('Transport inbound')"
                    @blur="formatPriceOnBlur(storeForm, 'transport_inbound')"
                    @focus="formatPriceOnFocus(storeForm, 'transport_inbound')"
                />
            </div>

            <div class="border-b border-[#E9E7E7] col-span-2" />

            <div class="pl-5 font-medium">
                {{ __("Transport outbound") }}
            </div>
            <div class="flex items-center justify-end pr-5 my-1">
                <Input
                    v-model="storeForm.transport_outbound"
                    :name="'transport_outbound'"
                    type="text"
                    class="mb-3.5 sm:mb-0"
                    :placeholder="__('Transport outbound')"
                    @blur="formatPriceOnBlur(storeForm, 'transport_outbound')"
                    @focus="formatPriceOnFocus(storeForm, 'transport_outbound')"
                />
            </div>

            <div class="border-b border-[#E9E7E7] col-span-2" />

            <div class="pl-5 font-medium">
                {{ __("Costs of taxation for BPM") }}
            </div>
            <div class="flex items-center justify-end pr-5 my-1">
                <Input
                    v-model="storeForm.costs_of_taxation"
                    :name="'costs_of_taxation'"
                    type="text"
                    class="mb-3.5 sm:mb-0"
                    :placeholder="__('Costs of taxation for BPM')"
                    @blur="formatPriceOnBlur(storeForm, 'costs_of_taxation')"
                    @focus="formatPriceOnFocus(storeForm, 'costs_of_taxation')"
                />
            </div>

            <div class="border-b border-[#E9E7E7] col-span-2" />

            <div class="pl-5 font-medium">
                {{ __("Recycling fee") }}
            </div>
            <div class="flex items-center justify-end pr-5 my-1">
                <Input
                    v-model="storeForm.recycling_fee"
                    :name="'recycling_fee'"
                    type="text"
                    class="mb-3.5 sm:mb-0"
                    :placeholder="__('Recycling fee')"
                    @blur="formatPriceOnBlur(storeForm, 'recycling_fee')"
                    @focus="formatPriceOnFocus(storeForm, 'recycling_fee')"
                />
            </div>

            <div class="border-b border-[#E9E7E7] col-span-2" />

            <div class="pl-5 font-medium">
                {{ __("Sales Margin") }}
            </div>
            <div class="flex items-center justify-end pr-5 my-1">
                <Input
                    v-model="storeForm.sales_margin"
                    :name="'sales_margin'"
                    type="text"
                    class="mb-3.5 sm:mb-0"
                    :placeholder="__('Sales Margin')"
                    @blur="formatPriceOnBlur(storeForm, 'sales_margin')"
                    @focus="formatPriceOnFocus(storeForm, 'sales_margin')"
                />
            </div>

            <div class="border-b border-[#E9E7E7] col-span-2" />

            <div class="pl-5 font-medium">
                {{ __("Leges (VAT)") }}
            </div>
            <div class="flex items-center justify-end pr-5 my-1">
                <Input
                    v-model="storeForm.leges_vat"
                    :name="'leges_vat'"
                    type="text"
                    class="mb-3.5 sm:mb-0"
                    :placeholder="__('Leges (VAT)')"
                    @blur="formatPriceOnBlur(storeForm, 'leges_vat')"
                    @focus="formatPriceOnFocus(storeForm, 'leges_vat')"
                />
            </div>
        </div>

        <div class="flex justify-end items-center gap-4 mt-4">
            <PrimaryButton :disabled="storeForm.processing" @click="save">
                Save
            </PrimaryButton>
        </div>

        <div class="flex justify-end items-center gap-4 mt-4">
            <Transition enter-from-class="opacity-0" leave-to-class="opacity-0">
                <p
                    v-if="storeForm.recentlySuccessful"
                    class="text-sm text-gray-600"
                >
                    {{ __("Saved.") }}
                </p>
            </Transition>
        </div>
    </Section>
</template>
