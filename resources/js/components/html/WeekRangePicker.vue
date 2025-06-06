<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import DatePicker from "@vuepic/vue-datepicker";
import {computed, inject, onMounted, Ref, watch} from "vue";

import "@vuepic/vue-datepicker/dist/main.css";

import {GlobalInputErrors, WeekPicker} from "@/types";

// Define props for the component
const props = defineProps<{
    name: string;
    id?: string;
    classes?: string;
    errorMessage?: string;
}>();

// Define model that will hold the JSON object with "from" and "to" dates
const model = defineModel<WeekPicker>();

const emit = defineEmits(["change"]);

const setDefaultStructure = () => {
    if (model.value == null) {
        model.value = { from: null!, to: null! };
    }
}

watch(
    () => model,
    () => {
        setDefaultStructure();
    },
    { immediate: true, deep: true }
);

const minDate = computed(() => model.value?.from ? new Date(model?.value.from[0]) : null!);
const maxDate = computed(() => model.value?.to ? new Date(model.value.to[0]) : null!);

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

// Watch for changes in page errors and update input errors accordingly
watch(
    () => usePage().props?.errors,
    (newErrors) => {
        inputErrors.value.errorMessages = newErrors;
    }
);
</script>

<template>
    <div
        v-if="model"
        class="w-full border-none p-0 my-1 relative min-w-[140px]"
    >
        <!-- Date Picker for "from" date -->
        <div class="flex gap-x-2">
            <DatePicker
                v-model="model.from"
                v-bind="$attrs"
                :class="
                    errorMessage
                        ? `bg-red-50 border-red-500 focus:bg-red-50 focus:border-red-500 hover:bg-red-50 placeholder-red-400 ${classes}`
                        : `border-gray-300 ${classes}`
                "
                class="text-gray-900 text-sm rounded-md block w-full placeholder-gray-400 peer transition focus:bg-gray-50 disabled:bg-slate-100 mb-2"
                teleport="body"
                :max-date="maxDate"
                :placeholder="__('From')"
                week-picker
                :auto-apply="true"
                @update:model-value="emit('change')"
            />

            <!-- Date Picker for "to" date -->
            <DatePicker
                v-model="model.to"
                v-bind="$attrs"
                :class="
                    errorMessage
                        ? `bg-red-50 border-red-500 focus:bg-red-50 focus:border-red-500 hover:bg-red-50 placeholder-red-400 ${classes}`
                        : `border-gray-300 ${classes}`
                "
                class="text-gray-900 text-sm rounded-md block w-full placeholder-gray-400 peer transition focus:bg-gray-50 disabled:bg-slate-100"
                teleport="body"
                :min-date="minDate"
                :placeholder="__('To')"
                week-picker
                :auto-apply="true"
                @update:model-value="emit('change')"
            />
        </div>

        <!-- Error Message Display -->
        <div
            v-if="errorMessage"
            class="text-red-500 text-sm mt-0.5 ml-0.5"
        >
            {{ __(errorMessage) }}
        </div>
    </div>
</template>
