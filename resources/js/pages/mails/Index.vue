<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3";
import { ref } from "vue";

import Header from "@/components/Header.vue";
import InputFile from "@/components/html/InputFile.vue";
import EmailInboxIcon from "@/icons/EmailInbox.vue";
import EmailOutboxIcon from "@/icons/EmailOutbox.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { DatabaseFile, Mail } from "@/types";
import { dateTimeToLocaleString } from "@/utils";

const props = defineProps<{
    inBox: Mail[];
    outBox: Mail[];
}>();

const activeTab = ref<string>("inbox");
const inboxPage = ref(1);
const outboxPage = ref(1);

const setActiveMailTab = (tab: string) => {
    activeTab.value = tab;
    selectedEmail.value = null!;
    switch (tab) {
        case "inbox":
            shownEmails.value = props.inBox;
            outboxPage.value = 1;
            break;

        case "outbox":
            shownEmails.value = props.outBox;
            inboxPage.value = 1;
            break;

        default:
            shownEmails.value = null!;
            break;
    }
};

const selectedEmail = ref<Mail>(null!);
const shownEmails = ref<Mail[]>(props.inBox);

const selectEmail = (email: Mail) => {
    selectedEmail.value = email;
};

const scroll = async (event: any) => {
    if (
        event.target.scrollHeight - event.target.scrollTop !==
        event.target.clientHeight
    ) {
        return;
    }

    let targetProps: "inBox" | "outBox" = "inBox";
    const data: { inbox_page?: number; outbox_page?: number } = {};

    switch (activeTab.value) {
        case "inbox":
            inboxPage.value += 1;
            data.inbox_page = inboxPage.value;
            break;

        case "outbox":
            outboxPage.value += 1;
            targetProps = "outBox";
            data.outbox_page = outboxPage.value;
            break;

        default:
            break;
    }

    await new Promise<void>((resolve, reject) => {
        router.reload({
            data: data,
            onSuccess: () => {
                shownEmails.value.push(...props[targetProps]);
                resolve();
            },
            onError: reject,
        });
    });
};
</script>

<template>
    <Head :title="__('Mail')" />

    <AppLayout>
        <Header :text="__('Mails')" />

        <div class="flex justify-center">
            <div class="w-full max-w-[1721px]">
                <div
                    class="rounded-lg border border-[#E9E7E7] shadow-sm bg-white relative py-4 sm:py-6 px-4 mt-4"
                >
                    <div class="font-semibold text-xl sm:text-2xl mb-4">
                        {{ __("Emails") }}
                    </div>

                    <div class="tabs-container my-2 shadow-md">
                        <div class="border-b border-gray-200">
                            <ul
                                class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500"
                            >
                                <li
                                    class="hover:cursor-pointer hover:border-blue-600 hover:border-b-2"
                                    :class="{
                                        'text-blue-600 border-blue-600 border-b-2':
                                            activeTab === 'inbox',
                                    }"
                                >
                                    <span
                                        class="tab-button inline-flex items-center justify-center p-4 border-b-2 border-transparent rounded-t-lg"
                                        @click="setActiveMailTab('inbox')"
                                    >
                                        <EmailInboxIcon />
                                        <span class="px-1">{{
                                            __("Inbox")
                                        }}</span>
                                    </span>
                                </li>
                                <li
                                    class="hover:cursor-pointer hover:border-blue-600 hover:border-b-2"
                                    :class="{
                                        'text-blue-600 border-blue-600 border-b-2':
                                            activeTab === 'outbox',
                                    }"
                                >
                                    <span
                                        class="tab-button inline-flex items-center justify-center p-4 border-b-2 border-transparent rounded-t-lg"
                                        @click="setActiveMailTab('outbox')"
                                    >
                                        <EmailOutboxIcon />
                                        <span class="px-1">{{
                                            __("Outbox")
                                        }}</span>
                                    </span>
                                </li>
                                <li
                                    v-if="
                                        selectedEmail?.attached_file_unique_name
                                    "
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
                    </div>

                    <Transition>
                        <div
                            class="flex flex-col sm:flex-row my-2 max-h-[80vh]"
                        >
                            <div
                                class="w-full sm:w-1/4 pr-4 overflow-y-auto"
                                @scroll="scroll"
                            >
                                <ul class="rounded-lg space-y-2">
                                    <li
                                        v-for="email in shownEmails"
                                        :key="email.id"
                                        class="cursor-pointer p-4 border border-gray-200 rounded relative transition-all duration-150 flex justify-between items-center"
                                        :class="[
                                            {
                                                'bg-gray-50 shadow-lg':
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
                                            <div class="text-sm text-gray-500 flex flex-col text-w">
                                                <span v-if="activeTab != 'outbox'">From: {{ email.creator.email }}</span>
                                                <span>To: {{ email.receivable?.email }}</span>
                                            </div>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{
                                                dateTimeToLocaleString(
                                                    email.created_at
                                                )
                                            }}
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div
                                class="w-full sm:w-3/4 rounded-lg bg-gray-50 mt-4 sm:mt-0 overflow-y-auto"
                            >
                                <div
                                    v-if="selectedEmail"
                                    v-html="selectedEmail?.content.content"
                                />
                                <div
                                    v-else
                                    class="text-[#909090] text-center py-4"
                                >
                                    Please select email to display
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
