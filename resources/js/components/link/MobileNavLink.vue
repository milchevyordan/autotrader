<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps<{
    href: string;
    active?: boolean;
}>();

const classes = computed(() =>
    props.active
        ? "inline-flex flex-col items-center justify-center px-5 text-[#008FE3]"
        : "inline-flex flex-col items-center justify-center px-5 text-gray-500"
);
</script>

<template>
    <Link
        :href="href"
        :class="classes"
    >
        <slot />
    </Link>
</template>
