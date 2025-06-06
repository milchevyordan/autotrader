<script setup lang="ts">
import {usePage} from "@inertiajs/vue3";
import {computed, inject, onMounted, ref, Ref} from "vue";

import {GlobalInputErrors} from "@/types";

const props = withDefaults(
    defineProps<{
        id?: string;
        rows?: string | number;
        name?: string;
        placeholder?: string;
        classes?: string;
        modelValue?: string | number;
        errorMessage?: string;
        disabled?: boolean;
    }>(),
    {
        rows: 3,
    }
);

const emit = defineEmits(["update:modelValue", "change"]);
const inputErrors = inject<Ref<GlobalInputErrors>>("globalInputErrors")!; // By adding the exclamation mark (!) after inject<Ref<GlobalInputErrors>>("globalInputErrors"), you're asserting that the injected value will always be defined and not undefined.

const errorMessage = computed(() => {
    const errorMessages = inputErrors.value?.errorMessages || {};
    const propName = props.name;
    return propName ? errorMessages[propName] || null : null;
});

const input = ref<HTMLInputElement | null>(null);

onMounted(() => {
    inputErrors.value = {
        errorMessages: usePage().props?.errors,
        failedKeys: [],
    };

    if (input.value?.hasAttribute("autofocus")) {
        input.value?.focus();
    }
});

const inputValue = computed<string | number | undefined | unknown>({
    get: () => props.modelValue,
    set: (value) => {
        emit("update:modelValue", value);
    },
});

const handleKeyup = (event: KeyboardEvent) => {
    inputValue.value = (event.target as HTMLInputElement).value;
};

const handleChange = () => {
    emit("change", {name: props.name, value: inputValue.value, key: props.name});
};
</script>

<template>
    <textarea
        :id="id ?? name"
        :rows="rows"
        :name="name"
        :value="modelValue"
        :disabled="disabled"
        :class="
            errorMessage
                ? `bg-red-50 border-red-500 focus:bg-red-50 focus:border-red-500 hover:bg-red-50 placeholder-red-400 ${classes}`
                : `border-gray-300 ${classes}` + (disabled ? ' text-gray-700' : ' text-gray-900')
        "
        class="border text-sm rounded-md hover:bg-gray-50 focus:bg-gray-50 focus:outline-none focus:ring-0 focus:border-gray-300 block p-2.5 placeholder-gray-400 w-full"
        :placeholder="placeholder"
        @keyup="handleKeyup"
        @input="
            $emit(
                'update:modelValue',
                ($event.target as HTMLInputElement).value
            )
        "
        @change="handleChange"
    />

    <div
        v-if="errorMessage"
        class="text-red-500 text-sm mt-0.5 ml-0.5"
    >
        {{ errorMessage }}
    </div>
</template>
