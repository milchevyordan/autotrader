<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import { computed, inject, onMounted, Ref, ref, watch } from "vue";

import { __ } from "@/translations";
import { GlobalInputErrors } from "@/types";

type Booleanish = boolean | "true" | "false";

const props = defineProps<{
    name: string;
    type: string;
    id?: string;
    classes?: string;
    min?: string;
    max?: string;
    step?: string;
    placeholder?: string;
    modelValue?: unknown | string | number;
    errorMessage?: string;
    disabled?: Booleanish | undefined;
    autocomplete?: string;
}>();

const emit = defineEmits(["update:modelValue", "change", "blur", "focus"]);
const inputErrors = inject<Ref<GlobalInputErrors>>("globalInputErrors")!; // By adding the exclamation mark (!) after inject<Ref<GlobalInputErrors>>("globalInputErrors"), you're asserting that the injected value will always be defined and not undefined.

const errorMessage = computed(() => {
    const errorMessages = inputErrors.value?.errorMessages || {};
    const propName = props.name;
    return propName ? errorMessages[propName] || null : null;
});

onMounted(() => {
    inputErrors.value = {
        errorMessages: usePage().props?.errors,
        failedKeys: [],
    };

    if (input.value?.hasAttribute("autofocus")) {
        input.value?.focus();
    }
});

watch(
    () => usePage().props?.errors,
    (newErrors) => {
        inputErrors.value.errorMessages = newErrors;
    }
);

const input = ref<HTMLInputElement | null>(null);

const inputValue = computed<string | number | undefined | unknown>({
    get: () => props.modelValue,
    set: (value) => {
        emit("update:modelValue", value);
    },
});

const handleKeyup = (event: KeyboardEvent) => {
    inputValue.value = (event.target as HTMLInputElement).value;
};

defineExpose({ focus: () => input.value?.focus() });

const handleChange = () => {
    emit("change", { name: props.name, value: inputValue.value });
};

const handleBlur = () => {
    emit("blur");
};

const handleFocus = () => {
    emit("focus");
};
</script>

<template>
    <div class="w-full">
        <input
            :id="id ?? name"
            :name="name"
            :type="type"
            :placeholder="placeholder"
            :value="inputValue"
            :disabled="Boolean(disabled)"
            :autocomplete="autocomplete === undefined ? 'off' : autocomplete"
            :max="max"
            :min="min"
            :step="step"
            :class="
                errorMessage
                    ? `bg-red-50 border-red-500 focus:bg-red-50 focus:border-red-500 hover:bg-red-50 placeholder-red-400 ${classes}`
                    : `border-gray-300 ${classes}`
            "
            class="border border-gray-200 text-gray-900 text-sm rounded-md focus:outline-none focus:ring-0 focus:border-gray-300 block w-full py-2 px-2.5 placeholder-gray-400 peer transition hover:bg-gray-50 focus:bg-gray-50 disabled:bg-slate-100"
            @input="
                $emit(
                    'update:modelValue',
                    ($event.target as HTMLInputElement).value
                )
            "
            @keyup="handleKeyup"
            @blur="handleBlur"
            @focus="handleFocus"
            @change="handleChange"
        >

        <div
            v-if="errorMessage"
            class="text-red-500 text-sm mt-0.5 ml-0.5"
        >
            {{ __(errorMessage) }}
        </div>
    </div>
</template>
