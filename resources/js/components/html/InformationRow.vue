<script setup lang="ts">
defineProps<{
    title: string;
}>();
</script>

<template>
    <div
        class="flex items-center justify-between px-4 py-1.5 border-b border-[#E9E7E70] gap-1"
    >
        <div class="text-[#6D6D73]">
            {{ title }}:
        </div>
        <div>
            <slot />
        </div>
    </div>
</template>
