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
        viewBox="0 0 64 64"
        fill="none"
        :stroke-width="stroke"
        stroke="currentColor"
        :class="classes"
    >
        <g stroke-width="0"></g>
        <g
            stroke-linecap="round"
            stroke-linejoin="round"
        ></g>
        <g>
            <polygon
                points="49.89 7.74 49.89 43.4 37.94 56.26 12.28 56.26 12.28 7.74 49.89 7.74"
                stroke-linecap="round"
            ></polygon>
            <polyline points="37.92 56.26 37.94 43.4 49.89 43.4"></polyline>
            <line x1="17.58" y1="45.36" x2="32.59" y2="45.36"></line>
            <line x1="17.58" y1="38.84" x2="41.59" y2="38.84"></line>
            <line x1="17.58" y1="32.74" x2="44.59" y2="32.74"></line>
            <polyline points="24.06 20.81 28.63 24.26 36.7 12.93"></polyline>
        </g>
    </svg>
</template>
