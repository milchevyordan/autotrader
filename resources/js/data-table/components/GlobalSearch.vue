<script setup lang="ts">
import { useForm, router } from "@inertiajs/vue3";
import { ref } from "vue";

import Magnifying from "@/icons/Magnifying.vue";
import { debounce } from "@/utils";

const props = defineProps<{
    propName: string;
}>();

const filterGlobalValue = new URLSearchParams(window.location.search).get(
    "filter[global]"
);

const globalSearchForm = useForm({
    filter: {
        global: filterGlobalValue,
        timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone,
    },
});

const inputValue = ref<null | string>(globalSearchForm.filter.global);

const globalSearch = debounce(() => {
    const thisRoute = route();
    const params = { ...thisRoute.params, page: 1 };
    globalSearchForm.filter.global = inputValue.value;

    router.visit(route(thisRoute.current() as string, params), {
        method: "get",
        data: {
            filter: {
                global: globalSearchForm.filter.global,
                timeZone: globalSearchForm.filter.timeZone,
            },
        },
        preserveState: true,
        preserveScroll: true,
        only: [props.propName],
    });
}, 1200);
</script>

<template>
    <div class="md:w-80 lg:w-96 relative">
        <div
            class="absolute inset-y-0 left-0 flex items-center pl-3 z-10 cursor-pointer"
            @click="globalSearch"
        >
            <Magnifying stroke="2" :classes="'w-5 h-5 text-gray-400'" />
        </div>

        <div class="relative">
            <input
                v-model="inputValue"
                class="border border-gray-200 text-gray-900 pl-10 pr-4 text-sm rounded-md focus:outline-none focus:ring-0 focus:border-gray-300 block w-full p-2.5 placeholder-gray-400 peer transition hover:bg-gray-50 focus:bg-gray-50 z-0"
                :placeholder="__('Search') + '...'"
                @input="globalSearch"
            />
        </div>
    </div>
</template>
