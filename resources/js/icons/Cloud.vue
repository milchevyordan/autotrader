<script setup lang="ts">
withDefaults(
    defineProps<{
        classes?: string;
    }>(),
    {
        classes: "w-6 h-6 flex-shrink-0",
    }
);
</script>

<template>
    <svg
        fill="none"
        viewBox="0 0 24 24"
        stroke-width="1.5"
        stroke="currentColor"
        :class="classes"
    >
        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M2.25 15a4.5 4.5 0 004.5 4.5H18a3.75 3.75 0 001.332-7.257 3 3 0 00-3.758-3.848 5.25 5.25 0 00-10.233 2.33A4.502 4.502 0 002.25 15z"
        />
    </svg>
</template>
