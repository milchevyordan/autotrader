<script setup lang="ts">
defineProps<{
    classes?: string;
}>();
</script>

<template>
    <div
        class="bg-white border border-[#E9E7E7] rounded-lg shadow my-4 p-3.5 h-fit"
    >
        <slot />
    </div>
</template>
