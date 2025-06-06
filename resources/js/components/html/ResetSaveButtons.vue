<script setup lang="ts">
import Reset from "@/icons/Reset.vue";

const props = defineProps<{
    processing: boolean;
}>();
</script>

<template>
    <div
        class="w-40 rounded-lg shadow-lg fixed bg-white z-50 bottom-20 md:bottom-2.5 right-2 sm:right-5 md:right-4 p-2 element-center gap-2 text-white"
    >
        <button
            :disabled="processing"
            :class="{
                'bg-[#E50000] hover:bg-red-500': !processing,
                'bg-gray-400 cursor-not-allowed': processing,
                'rounded p-2 active:scale-95 transition': true,
            }"
            @click="$emit('reset')"
        >
            <Reset />
        </button>

        <button
            :disabled="processing"
            :class="{
                'bg-[#00A793] hover:bg-emerald-500': !processing,
                'bg-gray-400 cursor-not-allowed': processing,
                'rounded px-5 py-2 font-light active:scale-95 transition': true,
            }"
            @click="$emit('save')"
        >
            {{ __("Save") }}
        </button>
    </div>
</template>
