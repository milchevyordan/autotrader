<script setup lang="ts">
withDefaults(
    defineProps<{
        classes?: string;
        stroke?: string;
    }>(),
    {
        classes: "w-6 h-6 flex-shrink-0",
        stroke: "1.5",
    }
);
</script>

<template>
    <svg
        fill="none"
        viewBox="0 0 24 24"
        :stroke-width="stroke"
        stroke="currentColor"
        :class="classes"
    >
        <g stroke-width="0" />
        <g
            stroke-linecap="round"
            stroke-linejoin="round"
        />
        <g>
            <path
                d="M5 12H19M5 12L11 6M5 12L11 18"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
            />
        </g>
    </svg>
</template>
