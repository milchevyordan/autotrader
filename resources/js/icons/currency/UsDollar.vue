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
        viewBox="0 0 24 24"
        fill="none"
        :stroke-width="stroke"
        stroke="currentColor"
        :class="classes"
    >
        <circle
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="1.5"
        />
        <path
            d="M12 6V18"
            stroke="currentColor"
            stroke-width="1.5"
            stroke-linecap="round"
        />
        <path
            d="M15 9.5C15 8.11929 13.6569 7 12 7C10.3431 7 9 8.11929 9 9.5C9 10.8807 10.3431 12 12 12C13.6569 12 15 13.1193 15 14.5C15 15.8807 13.6569 17 12 17C10.3431 17 9 15.8807 9 14.5"
            stroke="currentColor"
            stroke-width="1.5"
            stroke-linecap="round"
        />
    </svg>
</template>
