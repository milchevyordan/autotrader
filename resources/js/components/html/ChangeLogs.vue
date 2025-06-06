<script setup lang="ts">
import Accordion from "@/components/Accordion.vue";
import { ChangeLogsChange, ChangeLog } from "@/types";
import { dateTimeToLocaleString } from "@/utils";

defineProps<{
    changeLogs?: ChangeLog[];
}>();
</script>

<template>
    <div
        class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
    >
        <Accordion>
            <template #head>
                <div class="font-semibold text-xl sm:text-2xl mb-4">
                    {{ __("Change Logs") }}
                </div>
            </template>

            <div v-if="changeLogs" class="overflow-y-auto max-h-96">
                <div
                    v-for="(changeLog, index) in changeLogs"
                    :key="index"
                    :class="[
                        'grid lg:grid-cols-2 gap-4 mb-4 pb-4',
                        {
                            'border-b border-[#E9E7E7]':
                                index !== changeLogs.length - 1,
                        },
                    ]"
                >
                    <div
                        class="lg:border-r border-[#E9E7E7] lg:pr-8 sm:gap-y-2 items-center flex flex-col"
                    >
                        <div class="element-center">
                            <span class="mr-2 text-[#C7C7CC]">
                                {{ __("Creator") }}
                            </span>
                            <span
                                class="text-[#6D6D73] bg-[#F2F2F7] cursor-default rounded px-3 py-1 font-light m-1"
                            >
                                {{ changeLog.creator.full_name }}
                            </span>
                        </div>
                        <div class="element-center">
                            <span class="mr-2 text-[#C7C7CC]">
                                {{ __("Date") }}
                            </span>
                            <span
                                class="text-[#6D6D73] bg-[#F2F2F7] cursor-default rounded px-3 py-1 font-light m-1"
                            >
                                {{
                                    dateTimeToLocaleString(changeLog.created_at)
                                }}
                            </span>
                        </div>
                    </div>

                    <div class="lg:pl-4 items-center flex flex-row">
                        <ul class="flex flex-wrap">
                            <li
                                v-for="(changeItem, indexChange) in JSON.parse(
                                    changeLog.change
                                )"
                                :key="indexChange"
                            >
                                <ul class="flex flex-wrap">
                                    <li
                                        v-for="(item, indexItem) in changeItem as ChangeLogsChange[]"
                                        :key="indexItem"
                                        class="border border-gray-200 px-3 py-1 rounded-lg space-x-1 m-1"
                                        :class="{
                                            'bg-red-100':
                                                item?.old && !item?.new,
                                        }"
                                    >
                                        <span
                                            v-if="String(indexChange) != 'main'"
                                            >{{ indexChange }} |</span
                                        >
                                        <span>{{ indexItem }} |</span>
                                        <span
                                            v-if="item?.old"
                                            class="text-gray-500"
                                        >
                                            {{ item?.old }}
                                        </span>
                                        <span v-if="item?.old && item?.new"
                                            >&rarr;</span
                                        >
                                        <span v-if="item?.new">{{
                                            item?.new
                                        }}</span>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </Accordion>
    </div>
</template>
