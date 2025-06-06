<script setup lang="ts">
import IconArrowLeft from "@/icons/ArrowLeft.vue";

defineProps<{
    text: string;
}>();

const handleBackButton = () => {
    window.history.back();
};
</script>

<template>
    <div
        class="w-full flex items-center my-5 py-4 sm:py-6 pl-4 bg-gradient-to-r from-gray-100 via-gray-200 to-gray-300 font-medium sm:text-lg rounded-lg border border-gray-300 shadow-xl mt-14 md:mt-0 sticky top-0 z-20"
    >
        <button
            class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-r from-blue-400 to-blue-500 hover:from-blue-500 hover:to-blue-600 text-white border border-transparent transition-all duration-300 ease-in-out"
            @click="handleBackButton"
        >
            <IconArrowLeft class="w-5 h-5" />
        </button>
        <span class="ml-4 text-gray-800 font-semibold">{{ text }}</span>
    </div>
</template>
