<script setup lang="ts">
defineProps<{
    text: string;
    number: string | number;
    description?: string;
}>();
</script>

<template>
    <div class="relative flex w-full bg-white p-2 rounded-lg shadow-sm hover:bg-gray-50 transition active:scale-[.97]">
        <div class="w-16 h-16 md:h-20 md:w-20 bg-[#CCE9F9] text-[#008FE3] rounded-md element-center">
            <slot />
        </div>

        <div class="ml-3">
            <div class="mt-1 mb-2 md:mb-2.5 text-sm md:text-base text-[#676666]">
                {{ text }}
            </div>

            <div class="md:text-xl font-medium">
                {{ number }}
            </div>
        </div>
    </div>
</template>
