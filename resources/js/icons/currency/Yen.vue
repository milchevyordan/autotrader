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

        <path
            d="M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
            stroke="currentColor"
            :stroke-width="stroke"
        />
        <path
            d="M9 7L11.8832 11.3248V11.3248C11.9388 11.4081 12.0612 11.4081 12.1168 11.3248V11.3248L15 7"
            stroke="currentColor"
            :stroke-width="stroke"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
        <path
            d="M12 11.5V17"
            stroke="currentColor"
            :stroke-width="stroke"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
        <path
            d="M9 12H15"
            stroke="currentColor"
            :stroke-width="stroke"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
        <path
            d="M9 15H15"
            stroke="currentColor"
            :stroke-width="stroke"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
    </svg>
</template>
