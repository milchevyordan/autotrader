<script setup lang="ts">
withDefaults(
    defineProps<{
        classes?: string;
        stroke?: string;
    }>(),
    {
        classes: "w-6 h-6 flex-shrink-0",
        stroke: "1.8",
    }
);
</script>

<template>
    <svg
        viewBox="-2 -2 28 28"
        fill="none"
        :stroke-width="stroke"
        stroke="currentColor"
        :class="classes"
    >
        <path
            d="M16,5V19M20,11l-8,4"
            fill="none"
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            :stroke-width="stroke"
        />
        <polyline
            data-name="primary"
            points="4 9 12 9 4 17 12 17"
            fill="none"
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            :stroke-width="stroke"
        />
        <circle
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            :stroke-width="stroke"
        />
    </svg>
</template>
