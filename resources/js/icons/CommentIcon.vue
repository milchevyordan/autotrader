<script setup lang="ts">
withDefaults(
    defineProps<{
        classes?: string;
    }>(),
    {
        classes: "mr-1.5 w-3.5 h-3.5",
    }
);
</script>

<template>
    <svg
        :class="classes"
        aria-hidden="true"
        fill="none"
        viewBox="0 0 20 18"
    >
        <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M5 5h5M5 8h2m6-3h2m-5 3h6m2-7H2a1 1 0 0 0-1 1v9a1 1 0 0 0 1 1h3v5l5-5h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1Z"
        />
    </svg>
</template>
