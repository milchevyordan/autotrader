<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import DatePicker from "@vuepic/vue-datepicker";
import {computed, inject, onMounted, Ref, watch} from "vue";

import "@vuepic/vue-datepicker/dist/main.css";

import {GlobalInputErrors} from "@/types";

const props = withDefaults(
    defineProps<{
        name: string;
        time?: boolean;
        min?: string;
        max?: string;
        id?: string;
        classes?: string;
        enableTimePicker?: boolean;
        errorMessage?: string;
    }>(),
    {
        enableTimePicker: true,
    }
);

// Define model that will hold the JSON object with "from" and "to" dates
const model = defineModel<string>();

const emit = defineEmits(["change"]);

// Inject input errors
const inputErrors = inject<Ref<GlobalInputErrors>>("globalInputErrors")!;

// Compute the error message for display
const errorMessage = computed(
    () => inputErrors.value?.errorMessages?.[props.name] || null
);

// Initialize input errors on component mount
onMounted(() => {
    inputErrors.value = {
        errorMessages: usePage().props?.errors,
        failedKeys: [],
    };
});

const minDate = computed(() => props.min ? new Date(props.min) : null!);
const maxDate = computed(() => props.max ? new Date(props.max) : null!);

// Watch for changes in page errors and update input errors accordingly
watch(
    () => usePage().props?.errors,
    (newErrors) => {
        inputErrors.value.errorMessages = newErrors;
    }
);

const modelType = computed(() => {
    return props.enableTimePicker ? 'yyyy-MM-dd hh:mm' : 'yyyy-MM-dd';
})

const format = computed(() => {
    return props.enableTimePicker ? 'dd.MM.yyyy hh:mm' : 'dd.MM.yyyy';
})
</script>

<template>
    <div class="w-full border-none p-0 my-1 relative min-w-[140px]">
        <DatePicker
            v-model="model"
            v-bind="$attrs"
            :name="name"
            :class="
                errorMessage
                    ? `bg-red-50 border-red-500 focus:bg-red-50 focus:border-red-500 hover:bg-red-50 placeholder-red-400 ${classes}`
                    : `border-gray-300 ${classes}`
            "
            class="text-gray-900 text-sm rounded-md block w-full placeholder-gray-400 peer transition focus:bg-gray-50 disabled:bg-slate-100 mb-2"
            teleport="body"
            :min-date="minDate"
            :max-date="maxDate"
            :model-type="modelType"
            :format="format"
            :auto-apply="true"
            :placeholder="__('Select Date')"
            @update:model-value="emit('change')"
        />

        <div
            v-if="errorMessage"
            class="text-red-500 text-sm mt-0.5 ml-0.5"
        >
            {{ __(errorMessage) }}
        </div>
    </div>
</template>
