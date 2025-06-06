<script setup lang="ts">
import { component as ViewerComponent } from "v-viewer";
import { ref } from "vue";

import "viewerjs/dist/viewer.css";

import Section from "@/components/html/Section.vue";
import DownloadIcon from "@/icons/Download.vue";
import Eye from "@/icons/Eye.vue";
import { __ } from "@/translations";
import { DatabaseImage } from "@/types";
import { downLoadMultipleFiles } from "@/utils";

defineProps<{
    images?: Array<DatabaseImage>;
    title: string;
}>();

const showButton = ref<Record<number, boolean>>([]);

let $viewer: Viewer;

const inited = (viewer: Viewer) => {
    $viewer = viewer;
};

const show = (index: number) => {
    $viewer.view(index);
    $viewer.show();
};
</script>

<template>
    <Section classes="mt-4">
        <div class="font-semibold text-xl sm:text-2xl p-4">
            {{ title }}
        </div>

        <div
            v-if="images?.length"
            class="download-all border-b-2 py-3 flex items-center justify-center gap-2 cursor-pointer hover:bg-gray-50 active:scale-[.98] transition"
            @click="downLoadMultipleFiles(images, title)"
        >
            <span class="text-xs font-semibold text-gray-700">
                {{ __("Download all") }}
            </span>
            <DownloadIcon
                class="w-4 h-4 text-gray-500 group-hover:text-gray-700 transition"
            />
        </div>

        <ViewerComponent @inited="inited">
            <div
                class="information-container flex flex-wrap justify-around p-4 gap-4 rounded-md bg-white"
            >
                <div
                    v-for="(image, index) in images"
                    :key="index"
                    class="relative"
                    @mouseenter="showButton[index] = true"
                    @mouseleave="showButton[index] = false"
                >
                    <div class="relative">
                        <img
                            :src="'/storage/' + image.path"
                            alt="vehicle image"
                            class="object-cover w-full rounded-b-lg mb-3 lg:mb-0 lg:w-48 h-[173px] rounded"
                        />
                    </div>

                    <button
                        v-show="showButton[index]"
                        class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-blue-400 w-9 h-9 bg-blue-100 rounded-full border border-blue-200 flex items-center justify-center hover:bg-blue-200 transition duration-300"
                        :title="__('Look Images')"
                        @click="show(index)"
                    >
                        <Eye classes="w-5 h-5" />
                    </button>
                </div>
            </div>
        </ViewerComponent>
    </Section>
</template>
