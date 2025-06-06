<script setup lang="ts">
import { ref } from "vue";
import { WorkFilterTab } from "@/types";
import { __ } from "@/translations";

const emit = defineEmits(["switched"]);

const activeTab = ref<WorkFilterTab>("allRecords");

const setActiveTab = (tab: WorkFilterTab) => {
    activeTab.value = tab;

    emit("switched", tab);
};
</script>
<template>
    <div class="tabs-container my-2 shadow-md">
        <div class="border-b border-gray-200">
            <ul
                class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500"
            >
                <li
                    class="hover:cursor-pointer hover:border-blue-600 hover:border-b-2"
                    :class="{
                        'text-blue-600 border-blue-600 border-b-2':
                            activeTab === 'allRecords',
                    }"
                >
                    <div
                        class="tab-button inline-flex items-center justify-center p-4 border-b-2 border-transparent rounded-t-lg"
                        @click="setActiveTab('allRecords')"
                    >
                        <span class="px-1">{{ __("All records") }}</span>
                    </div>
                </li>
                <li
                    class="hover:cursor-pointer hover:border-blue-600 hover:border-b-2"
                    :class="{
                        'text-blue-600 border-blue-600 border-b-2':
                            activeTab === 'redFlags',
                    }"
                >
                    <div
                        class="tab-button inline-flex items-center justify-center p-4 border-b-2 border-transparent rounded-t-lg"
                        @click="setActiveTab('redFlags')"
                    >
                        <!-- <EmailOutboxIcon /> -->
                        <span class="px-1">{{ __("Red flags") }}</span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>
