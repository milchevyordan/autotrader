<script setup lang="ts">
import { Ref } from "vue";
import { computed } from "vue";
import { inject } from "vue";

import RadioButton from "./RadioButton.vue";
import { GlobalInputErrors } from "@/types";

const emit = defineEmits(["change"]);

const props = withDefaults(
    defineProps<{
        name: string;
        classes?: string;
        disabled?: boolean;
        leftButtonLabel?: string;
        rightButtonLabel?: string;
    }>(),
    {
        classes: "flex sm:justify-end mb-3.5 sm:mb-0",
        leftButtonLabel: "Yes",
        rightButtonLabel: "No",
    }
);

const model = defineModel<boolean>({ default: false });

const handleModelChange = (value: boolean) => {
    model.value = value;

    emit("change", {
        name: props.name,
        value: value,
    });
};

const inputErrors = inject<Ref<GlobalInputErrors>>("globalInputErrors");

const errorMessage = computed(() => {
    const errorMessages = inputErrors?.value?.errorMessages ?? {};
    const propName = props.name;

    return propName ? errorMessages[propName] || null : null;
});
</script>

<template>
    <div>
        <div :class="classes">
            <div>
                <RadioButton
                    :id="`yes_` + name"
                    :label="leftButtonLabel"
                    :name="name"
                    :disabled="disabled"
                    classes="peer-checked:bg-[#008FE3] peer-checked:text-white peer-checked:border-blue-200 border border-r-0 rounded-l-md bg-white"
                    :checked="model == true"
                    @click="handleModelChange(true)"
                />
            </div>

            <div>
                <RadioButton
                    :id="`no_` + name"
                    :label="rightButtonLabel"
                    :name="name"
                    :disabled="disabled"
                    classes="peer-checked:bg-[#008FE3] peer-checked:text-white peer-checked:border-blue-200 border border-l-0 rounded-r-md bg-white"
                    :checked="model == false"
                    @click="handleModelChange(false)"
                />
            </div>
        </div>
        <div
            v-if="errorMessage"
            class="block text-red-500 text-sm mt-0.5 ml-0.5 text-right"
        >
            {{ __(errorMessage) }}
        </div>
    </div>
</template>
