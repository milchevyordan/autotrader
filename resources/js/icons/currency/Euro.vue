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
        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M14.25 7.756a4.5 4.5 0 100 8.488M7.5 10.5h5.25m-5.25 3h5.25M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
        />
    </svg>
</template>
