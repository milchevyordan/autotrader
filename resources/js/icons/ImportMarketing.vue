<script setup lang="ts">
withDefaults(
    defineProps<{
        classes?: string;
        stroke?: string;
        color?: string;
    }>(),
    {
        classes: "w-10 h-10 flex-shrink-0",
        stroke: "1.5",
        color: "#000000",
    }
);
</script>

<template>
    <svg
        :fill="color"
        :class="classes"
        :stroke="stroke"
        viewBox="0 0 64 64"
    >
        <path
            d="M59.8,30.5C59.8,30.5,59.8,30.5,59.8,30.5C59.8,30.5,59.8,30.5,59.8,30.5L59.8,30.5c-0.4-5.9-2.6-11.2-6.1-15.6l7.6-7.6    l-4.7-4.7l-7.6,7.6c-4.8-3.8-11-6.1-17.6-6.1c-0.3,0-0.6,0-0.9,0v0c0,0,0,0,0,0c0,0,0,0,0,0l0,0C20.5,4.4,11.8,9.9,7,18    c-2.5,4.2-4,9.2-4,14.5C3,48.3,15.7,61,31.5,61c6.9,0,13.2-2.4,18.1-6.5c6.3-5.2,10.4-13.1,10.4-22C59.9,31.8,59.9,31.2,59.8,30.5    L59.8,30.5z M56.7,5.4l1.9,1.9L45.1,20.8l2,2h-5.9v-5.9l2,2L56.7,5.4z M29.6,6.2c0,0.2,0,0.3,0,0.5c0,1.1-0.1,2.7-0.4,4    c-0.1,0.7-0.3,1.3-0.6,1.9c-0.2,0.5-0.5,0.9-0.8,1.1c-1.1,0.8-2,1.8-2.7,2.8c-0.7,1-1.1,2.1-1.1,3.2c0,0.7,0.2,1.4,0.5,2    c0.4,0.8,0.7,1.8,0.7,2.7c0,1.1-0.3,2.1-1,2.9c-0.7,0.8-1.7,2-2.5,3.2c-0.4,0.6-0.8,1.2-1,1.9c-0.3,0.6-0.5,1.2-0.5,1.9    c0,0.4,0.1,0.8,0.3,1.2c0.2,0.4,0.5,0.9,0.6,1.2c0.2,0.4,0.2,0.7,0.2,0.8c0,0-0.1,0-0.2,0l0,0c0,0-0.2,0-0.5-0.1    c-0.4-0.2-1-0.5-1.6-1.1c-0.9-0.8-1.9-1.8-2.6-2.9c-0.7-1.1-1.2-2.2-1.2-3c0-0.4,0-0.8,0-1.2c0-1.4,0-2.7-0.3-3.8    c-0.2-0.5-0.4-1.1-0.9-1.5c-0.5-0.4-1.1-0.6-1.8-0.6c0,0-0.1,0-0.3-0.1c-0.3-0.2-0.7-0.6-1.1-1.1c-0.6-0.8-1.1-1.9-1.5-2.7    c-0.2-0.4-0.3-0.7-0.4-1C13.3,11.6,20.9,6.7,29.6,6.2z M31.5,59c-7.3,0-13.9-3-18.7-7.7C8,46.5,5,39.9,5,32.5    c0-4.2,1-8.2,2.8-11.7c0.2,0.4,0.4,0.8,0.6,1.2c0.4,0.8,1,1.6,1.6,2.3c0.3,0.3,0.6,0.6,1,0.9c0.4,0.2,0.8,0.4,1.3,0.4    c0.3,0,0.4,0.1,0.5,0.1c0.1,0.1,0.1,0.1,0.2,0.3c0.1,0.3,0.2,0.7,0.3,1.3c0.1,0.6,0.1,1.3,0.1,2.1c0,0.4,0,0.8,0,1.2    c0,1.1,0.4,2.2,0.9,3.2c0.8,1.5,2.1,2.9,3.3,4c0.6,0.5,1.2,1,1.8,1.3c0.6,0.3,1.2,0.6,1.8,0.6l0.2,0c0.5,0,1.1-0.2,1.5-0.6    c0.2-0.2,0.3-0.4,0.4-0.7c0.1-0.2,0.1-0.5,0.1-0.7c0-0.6-0.2-1.1-0.4-1.6c-0.2-0.5-0.5-1-0.7-1.4c0,0,0-0.1,0-0.2    c0-0.2,0.1-0.6,0.3-1.1c0.6-1.4,2.2-3.4,3.2-4.6c1-1.2,1.5-2.7,1.5-4.2c0-1.3-0.3-2.6-1-3.7c-0.2-0.3-0.3-0.6-0.3-1    c0-0.6,0.2-1.3,0.8-2.1c0.5-0.8,1.3-1.6,2.2-2.3c0.7-0.5,1.1-1.1,1.5-1.9c0.5-1.1,0.8-2.3,0.9-3.5c0.2-1.2,0.2-2.4,0.2-3.2    c0-0.2,0-0.4,0-0.6c6,0,11.6,2.1,16,5.6l-4.4,4.4l-4-4v2.4v10.4H52l-4-4l4.4-4.4c2.7,3.5,4.6,7.8,5.3,12.4    c-0.2-0.1-0.4-0.2-0.6-0.3c-0.7-0.3-1.4-0.6-2.1-0.9c-0.7-0.2-1.5-0.4-2.2-0.4c-0.3,0-0.7,0-1,0.1c-0.3,0.1-0.7,0.3-0.9,0.6    c-0.5,0.6-1.3,1.3-2.1,1.7c-0.8,0.5-1.7,0.8-2.3,0.8c-0.2,0-0.4,0-0.6-0.1c-0.4-0.1-0.7-0.1-1.1-0.1c-1.2,0-2.6,0.4-3.8,1    c-0.6,0.3-1.1,0.7-1.6,1.2c-0.4,0.5-0.8,1.1-0.8,1.8c0,0.3,0,0.5,0.1,0.8c0.2,0.4,0.2,1,0.2,1.6c0,0.7-0.1,1.5-0.4,2.1    c-0.2,0.6-0.6,1-0.7,1c-0.3,0.1-0.8,0.3-1.4,0.6c-0.9,0.4-2,0.8-3,1.4c-0.5,0.3-0.9,0.7-1.3,1.1c-0.4,0.4-0.7,1-0.9,1.6    C31,44.5,31,44.8,31,45c0,0.6,0.2,1.2,0.6,1.7c0.5,0.7,1.3,1.2,2.2,1.6c0.9,0.3,2,0.5,3.1,0.5c0.9,0,1.9-0.1,2.9-0.4    c0.7-0.2,1.2-0.3,1.7-0.3c1,0,1.6,0.3,2.1,0.8c0.5,0.5,0.8,1.2,0.9,1.9c0.2,0.7,0.6,1.3,1,1.8c0.5,0.6,1.1,1,1.6,1.3    C42.7,57.1,37.3,59,31.5,59z M48.7,52.6c-0.3-0.1-0.6-0.3-0.9-0.5c-0.3-0.2-0.7-0.5-0.9-0.8c-0.3-0.3-0.4-0.6-0.5-0.9    c-0.2-0.9-0.6-1.9-1.4-2.8c-0.8-0.8-2-1.4-3.5-1.4c-0.7,0-1.4,0.1-2.2,0.3c-0.8,0.2-1.6,0.3-2.3,0.3c-1.2,0-2.3-0.3-3-0.7    c-0.3-0.2-0.6-0.4-0.7-0.6C33,45.3,33,45.2,33,45l0-0.2c0.1-0.3,0.3-0.6,0.7-1c0.6-0.5,1.5-1,2.4-1.4c0.9-0.4,1.8-0.7,2.5-1    c0.9-0.4,1.4-1.3,1.7-2.1c0.3-0.9,0.5-1.9,0.5-2.9c0-0.8-0.1-1.6-0.4-2.3l0-0.1c0-0.1,0-0.2,0.3-0.5c0.3-0.4,1-0.8,1.8-1.1    c0.8-0.3,1.6-0.5,2.2-0.5c0.2,0,0.5,0,0.6,0.1c0.4,0.1,0.7,0.2,1.1,0.2c1.2,0,2.3-0.5,3.3-1c1-0.6,1.9-1.4,2.6-2.1l0.1,0    c0.1,0,0.2,0,0.3,0c0.4,0,1,0.1,1.6,0.3c0.9,0.3,2,0.8,2.8,1.2c0.3,0.2,0.6,0.3,0.9,0.5c0,0.5,0.1,0.9,0.1,1.4    C57.9,40.5,54.4,47.7,48.7,52.6z"
        />
    </svg>
</template>
