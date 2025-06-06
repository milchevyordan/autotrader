<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps<{
    href: string;
    active?: boolean;
    notificationsCount?: number | string;
}>();

const classes = computed(() =>
    props.active
        ? "p-2 rounded-md bg-[#008FE3] text-white transition duration-150 ease-in-out relative"
        : "p-2 rounded-md text-slate-600 hover:bg-[#008FE3] hover:text-white transition duration-150 ease-in-out relative"
);
</script>

<template>
    <Link
        :href="href"
        :class="classes"
    >
        <slot />
        <div
            v-if="notificationsCount && notificationsCount != '0'"
            class="absolute top-0 right-0 -mt-2 -mr-2 bg-red-500 text-white rounded-full px-2"
        >
            {{ notificationsCount }}
        </div>
    </Link>
</template>
