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
        viewBox="0 0 256 256"
    >
        <path
            id="XMLID_11_"
            d="M57.9,41.9c11.3,0,20.5-9.2,20.5-20.5c0-11.3-9.2-20.5-20.5-20.5c-11.3,0-20.5,9.2-20.5,20.5
	C37.4,32.7,46.6,41.9,57.9,41.9"
        />
        <path
            id="XMLID_10_"
            d="M203.4,41.6c11.5,0,20.8-9.3,20.8-20.8c0-11.5-9.3-20.8-20.8-20.8c-11.5,0-20.8,9.3-20.8,20.8
	C182.6,32.3,191.9,41.6,203.4,41.6"
        />
        <path
            id="XMLID_7_"
            d="M50.3,158.5c-26.9,0-48.7,21.8-48.7,48.7c0,26.9,21.8,48.8,48.7,48.8c26.9,0,48.8-21.8,48.8-48.8
	C99.1,180.3,77.2,158.5,50.3,158.5 M56.1,238.2v7.8H44.4v-7.8c-12.4-1.4-20.4-13.3-20.4-21h12.4c0,3.5,2.7,8.1,8,9.2v-14.6
	c-2.7-0.5-4.2-0.9-7.1-1.7c-7.3-2-12.4-8.2-12.4-16.1c0-7.8,7.6-16.6,19.4-18.1v-7.3h11.7v7.3c12.5,1.6,20.1,10.8,20.1,20l-12.2,0
	c0-4-3.4-7.7-8-8.1v13.7c3.4,0.7,3.4,0.7,6.5,1.5c11.6,3.2,14.2,10.7,14.2,17.1C76.8,229.8,69,236.5,56.1,238.2"
        />
        <path
            id="XMLID_4_"
            d="M44.4,199.3c-5.2-0.9-6.6-3.2-6.6-5.6c0-2.8,3.5-5.5,6.6-5.9V199.3z M56.2,226.3V214c5.1,0.7,8.4,3,8.4,6.1
	C64.6,222.9,61.6,226.1,56.2,226.3"
        />
        <path
            id="XMLID_3_"
            d="M31.2,77.4h8.1l-9.3,34.7h56l-9.3-34.7h8.1l9.6,34.7h18L100.6,70c-3.1-10.2-13.2-21-26.4-21H41.5
	c-13.2,0-23.3,10.8-26.4,21L3.6,112.1h18L31.2,77.4z"
        />
        <path
            id="XMLID_2_"
            d="M225.6,48.8c10.2,0,18.5,8.3,18.5,18.5l0,56.8H256v23.3l-24.1,0v96.4c0,6.7-5.5,12.2-12.3,12.2
	c-6.7,0-12.2-5.5-12.2-12.2v-96.4h-7.8v96.4c0,6.7-5.5,12.2-12.2,12.2c-6.7,0-12.2-5.5-12.2-12.2v-96.4L1.6,147.4v-23.4h173.6V69.4
	l-38.8,46c-8.2,9.4-22.7-2.4-14.5-12.4l40.2-48.4c2.6-2.6,4.9-5.8,14.5-5.8H225.6z"
        />
    </svg>
</template>
