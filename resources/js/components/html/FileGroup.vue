<script setup lang="ts">
import FileIcon from "@/components/FileIcon.vue";
import Section from "@/components/html/Section.vue";
import DownloadIcon from "@/icons/Download.vue";
import IconFolderArrow from "@/icons/FolderArrow.vue";
import { __ } from "@/translations";
import { DatabaseFile } from "@/types";
import { downLoadFile, downLoadMultipleFiles } from "@/utils";

defineProps<{
    files: DatabaseFile[];
    title: string;
}>();
</script>

<template>
    <Section class="h-fit">
        <div class="p-4 border-b border-[#E9E7E7]">
            {{ title }}
        </div>

        <div
            class="download-all border-b-2 py-3 flex items-center justify-center gap-2 cursor-pointer hover:bg-gray-50 active:scale-[.98] transition"
            @click="downLoadMultipleFiles(files, title)"
        >
            <span class="text-xs font-semibold text-gray-700">
                {{ __("Download all") }}
            </span>
            <DownloadIcon
                class="w-4 h-4 text-gray-500 group-hover:text-gray-700 transition"
            />
        </div>

        <div
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-1 p-3 gap-[9.3px]"
        >
            <div
                v-for="(file, index) in files"
                :key="index"
                class="w-full border border-[#F2F2F7] flex items-center justify-between rounded p-1.5 text-[#6D6D73] cursor-pointer hover:bg-gray-50 active:scale-[.98] transition"
                @click="downLoadFile(file.path)"
            >
                <div class="flex items-center">
                    <div class="file-container">
                        <FileIcon
                            :file="file.original_name"
                            classes="w-5 h-5"
                        />

                        <div
                            class="text-xs text-slate-700 max-w-20 overflow-hidden whitespace-nowrap truncate"
                        >
                            {{ file.original_name }}
                        </div>
                    </div>
                </div>

                <IconFolderArrow classes="w-5 h-5" />
            </div>
        </div>
    </Section>
</template>
