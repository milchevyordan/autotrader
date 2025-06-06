<script setup lang="ts">
import {camelToTitleCase} from "@/utils";

const props = defineProps<{
    tab: string;
    method: string;
}>();

const emit = defineEmits(["click"]);

const toggleTab = () => {
    emit("click", props.tab);
};
</script>

<template>
    <li
        class="hover:cursor-pointer hover:border-blue-600 hover:border-b-2"
        :class="{
            'text-blue-600 border-blue-600 border-b-2': tab == method,
        }"
    >
        <span
            class="tab-button inline-flex items-center justify-center p-4 border-b-2 border-transparent rounded-t-lg"
            @click="toggleTab"
        >
            <span class="px-1">{{ camelToTitleCase(tab) }}</span>
        </span>
    </li>
</template>
