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
        fill="currentColor"
        viewBox="0 0 24 24"
        :class="classes"
    >
        <g stroke-width="0" />
        <g
            stroke-linecap="round"
            stroke-linejoin="round"
        />
        <g>
            <path
                d="M7 3L5 3L5 9H7L7 3ZM19 3L17 3V13H19V3ZM3 13H5L5 21H7L7 13H9V11H3L3 13ZM15 7H13V3L11 3V7L9 7L9 9L15 9V7ZM11 21H13V11H11L11 21ZM15 15V17L17 17V21H19V17H21V15L15 15Z"
            />
        </g>
    </svg>
</template>
