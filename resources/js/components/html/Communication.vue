<script setup lang="ts">
import { ref } from "vue";

import InputFile from "@/components/html/InputFile.vue";
import { DatabaseFile, Mail } from "@/types";
import { dateTimeToLocaleString } from "@/utils";

const props = defineProps<{
    mails: Mail[];
    files: DatabaseFile[];
}>();

const selectedEmail = ref<Mail>(props.mails[0] ?? null!);

const selectEmail = (email: Mail) => {
    selectedEmail.value = email;
};
</script>

<template>
    <div
        class="rounded-lg border border-[#E9E7E7] shadow-sm bg-white relative py-4 sm:py-6 px-4 mt-4"
    >
        <div class="font-semibold text-xl sm:text-2xl mb-4">
            {{ __("Communication") }}
        </div>

        <div v-if="mails.length">
            <ul
                class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500"
            >
                <li
                    v-if="selectedEmail?.attached_file_unique_name"
                    class="ml-auto"
                >
                    <InputFile
                        :files="[
                            {
                                original_name: selectedEmail?.attached_file_unique_name,
                                path: selectedEmail?.attached_file_unique_name,
                            } as DatabaseFile,
                        ]"
                        :is-uploadable="false"
                    />
                </li>
            </ul>
        </div>

        <Transition>
            <div
                v-if="mails.length"
                class="flex flex-col sm:flex-row mt-2 mb-4 max-h-[80vh]"
            >
                <div class="sm:w-1/4 pr-4 overflow-y-auto">
                    <ul class="rounded-lg space-y-2">
                        <li
                            v-for="email in mails"
                            :key="email.id"
                            class="cursor-pointer p-4 border border-gray-200 rounded relative transition-all duration-150 flex justify-between items-center shadow-lg"
                            :class="[
                                {
                                    'bg-gray-50':
                                        selectedEmail &&
                                        selectedEmail.id === email.id,
                                },
                            ]"
                            @click="selectEmail(email)"
                        >
                            <div class="flex-1">
                                <div class="font-bold">
                                    {{ email.subject }}
                                </div>
                                <div
                                    class="flex flex-col text-sm text-gray-500"
                                >
                                    <span
                                        >{{ __("From") }}:
                                        {{ email.creator.email }}</span
                                    >
                                    <span
                                        >{{ __("To") }}:
                                        {{ email.receivable.email }}</span
                                    >
                                    <span
                                        >{{ __("Date") }}:
                                        {{
                                            dateTimeToLocaleString(
                                                email.created_at
                                            )
                                        }}</span
                                    >
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                <div
                    class="sm:w-3/4 rounded-lg bg-gray-50 mt-4 sm:mt-0 overflow-y-auto"
                >
                    <div
                        v-if="selectedEmail"
                        v-html="selectedEmail?.content.content"
                    />
                    <div v-else class="text-[#909090] text-center py-4">
                        {{ __("Please select email to display") }}
                    </div>
                </div>
            </div>
        </Transition>

        <div class="flex flex-col gap-5 gap-y-0">
            <div class="flex items-center justify-center w-full">
                <label
                    class="w-full border bg-slate-200 opacity-50 border-dashed rounded-lg"
                >
                    <p class="text-sm text-[#008FE3] text-center py-5">
                        {{ __("All generated files") }}
                    </p>
                </label>
            </div>

            <InputFile :files="files" :is-uploadable="false" />
        </div>
    </div>
</template>
