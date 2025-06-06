<script setup lang="ts">
import { Ref } from "vue";
import { computed } from "vue";
import { inject } from "vue";

import RadioButton from "@/components/html/RadioButton.vue";
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
        classes: "flex sm:justify-end mb-3.5 sm:mb-0 text-[#676666]",
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
        <div v-if="!disabled" :class="props.classes">
            <div>
                <RadioButton
                    :id="`yes_${props.name}`"
                    :label="props.leftButtonLabel"
                    :name="`${props.name}`"
                    :classes="`peer-checked:bg-[#008FE3] peer-checked:text-white peer-checked:border-blue-200 border border-r-0 rounded-l-md bg-white`"
                    :checked="model === true"
                    @change="() => handleModelChange(true)"
                />
            </div>

            <div>
                <RadioButton
                    :id="`no_${props.name}`"
                    :label="props.rightButtonLabel"
                    :name="`${props.name}`"
                    :classes="`peer-checked:bg-[#008FE3] peer-checked:text-white peer-checked:border-blue-200 border border-l-0 rounded-r-md bg-white`"
                    :checked="model === false"
                    @change="() => handleModelChange(false)"
                />
            </div>
        </div>
        <div v-else :class="props.classes">
            <span
                class="bg-[#008FE3] text-white border-blue-200 border rounded-md px-2 py-1.5 inline-block"
            >
                {{ model ? leftButtonLabel : rightButtonLabel }}
            </span>
        </div>
        <div
            v-if="errorMessage"
            class="block text-red-500 text-sm mt-0.5 ml-0.5 text-right"
        >
            {{ __(errorMessage) }}
        </div>
    </div>
</template>
