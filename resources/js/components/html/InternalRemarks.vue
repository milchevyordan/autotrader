<script setup lang="ts">
import { router, useForm } from "@inertiajs/vue3";
import { Ref, ref, watch } from "vue";

import Textarea from "@/components/html/Textarea.vue";
import Modal from "@/components/Modal.vue";
import SelectMultiple from "@/components/SelectMultiple.vue";
import CommentIcon from "@/icons/CommentIcon.vue";
import IconRightArrowAccordion from "@/icons/RightArrowAccordion.vue";
import {
    InternalRemark,
    InternalRemarkReply,
    InternalRemarkReplyForm,
    Multiselect,
    Role,
    User,
} from "@/types";
import { dateTimeToLocaleString } from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    internalRemarks?: InternalRemark[];
    mainCompanyRoles?: Multiselect<Role>;
    mainCompanyUsers?: Multiselect<User>;
    form: any;
}>();

const formShown: Ref<boolean> = ref(false);

watch(
    [() => props.mainCompanyRoles, () => props.mainCompanyUsers],
    ([newRoles, newUsers]) => {
        if (!newRoles || !newUsers) {
            formShown.value = false;
        }
    }
);

const showForm = async () => {
    formShown.value = !formShown.value;

    if (
        !formShown.value ||
        (props?.mainCompanyRoles && props?.mainCompanyUsers)
    ) {
        return;
    }

    await new Promise((resolve, reject) => {
        router.reload({
            only: ["mainCompanyUsers", "mainCompanyRoles"],
            onSuccess: resolve,
            onError: reject,
        });
    });
};

const replyForm = useForm<InternalRemarkReplyForm>({
    id: null!,
    internal_remark_id: null!,
    reply: null!,
});

const showRemarkRepliesModal = ref(false);

const closeRemarkRepliesModal = () => {
    showRemarkRepliesModal.value = false;
    replyForm.reset();
};

const replies = ref<InternalRemarkReply[]>([]);

const openRemarkRepliesModal = (item: InternalRemark) => {
    replies.value = item.replies;
    replyForm.internal_remark_id = item.id;
    showRemarkRepliesModal.value = true;
};

const replyFormRules = {
    internal_remark_id: {
        required: true,
        type: "number",
    },
    reply: {
        required: true,
        type: "string",
    },
};

const saveReply = () => {
    validate(replyForm, replyFormRules);

    replyForm.post(route("internal-remark-reply-controller.store"), {
        preserveScroll: true,
        onSuccess: () => {
            replyForm.reset();
            closeRemarkRepliesModal();
        },
        onError: () => {},
    });
};
</script>

<template>
    <div
        class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
    >
        <div class="font-semibold text-xl sm:text-2xl mb-4">
            {{ __("Internal Remarks") }}
        </div>

        <div
            class="absolute top-5 right-4 text-slate-400 z-10 w-8 h-8 cursor-pointer flex items-center justify-center rounded-full bg-white shadow-lg transition-all hover:bg-slate-100"
        >
            <IconRightArrowAccordion
                class="w-6 h-6 transition-all duration-500"
                :class="formShown ? 'ml-0.5' : 'rotate-90 mt-1'"
                @click="showForm"
            />
        </div>

        <div v-if="internalRemarks" class="overflow-y-auto px-2 max-h-[70vh]">
            <div
                v-for="(remark, index) in internalRemarks"
                v-show="!formShown"
                :key="index"
                :class="[
                    'grid grid-cols-1 sm:grid-cols-3 my-2 gap-4 py-4',
                    {
                        'border-b border-[#E9E7E7]':
                            index !== internalRemarks.length - 1,
                    },
                ]"
            >
                <div
                    class="lg:border-r border-[#E9E7E7] lg:pr-8 sm:gap-y-2 items-center flex flex-col"
                >
                    <div class="flex items-center h-fit rounded-md">
                        <div class="text-[#C7C7CC] mr-2 p-1 element-center">
                            {{ __("Creator") }}
                        </div>
                        <div class="text-[#6D6D73]">
                            <span
                                class="bg-[#F2F2F7] cursor-default rounded px-3 py-1 font-light m-1"
                            >
                                {{ remark.creator?.full_name }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center h-fit rounded-md">
                        <div class="text-[#C7C7CC] mr-2 p-1 element-center">
                            {{ __("Users") }}
                        </div>
                        <div class="text-[#6D6D73] flex flex-wrap">
                            <span
                                v-for="(user, secondIndex) in remark.users"
                                :key="secondIndex"
                                class="bg-[#F2F2F7] cursor-default rounded px-2 py-1 font-light m-1"
                            >
                                {{ user.full_name }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center h-fit rounded-md">
                        <div class="text-[#C7C7CC] mr-2 p-1 element-center">
                            {{ __("Date") }}
                        </div>
                        <div class="text-[#6D6D73] flex flex-wrap">
                            <span
                                class="bg-[#F2F2F7] cursor-pointer rounded px-2 py-1 font-light m-1"
                            >
                                {{ dateTimeToLocaleString(remark.created_at) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="lg:pl-4 items-center flex flex-col col-span-2">
                    <Textarea
                        v-model="remark.note"
                        :disabled="true"
                        classes="h-full"
                    />
                    <div
                        class="flex items-center mt-4 space-x-4"
                        @click="openRemarkRepliesModal(remark)"
                    >
                        <button
                            type="button"
                            class="flex items-center text-sm text-gray-500 hover:underline font-medium"
                        >
                            <CommentIcon />
                            {{ __("Reply") }} ({{ remark.replies.length }})
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-show="formShown" class="grid lg:grid-cols-2 gap-4 mb-4">
            <div
                class="grid grid-cols-1 sm:grid-cols-3 xl:border-r border-[#E9E7E7] xl:pr-8 sm:gap-y-2 items-center"
            >
                <label for="internal_remark_user_ids">
                    {{ __("Users") }}
                </label>
                <SelectMultiple
                    :key="'internal_remark_user_ids'"
                    v-model="form.internal_remark_user_ids"
                    :name="'internal_remark_user_ids'"
                    :options="mainCompanyUsers"
                    :reset="!formShown"
                    :placeholder="__('Users to send')"
                    class="w-full mb-3.5 col-span-2"
                />

                <label for="internal_remark_role_ids">
                    {{ __("Roles") }}
                </label>
                <SelectMultiple
                    v-model="form.internal_remark_role_ids"
                    :name="'internal_remark_role_ids'"
                    :options="mainCompanyRoles"
                    :reset="!formShown"
                    :placeholder="__('Roles to send')"
                    class="w-full mb-3.5 col-span-2"
                />
            </div>

            <div class="lg:pl-4 items-center flex flex-col">
                <Textarea
                    v-model="form.internal_remark"
                    :name="'internal_remark'"
                    :placeholder="__('Internal Remarks / notes')"
                    classes="h-full"
                />
            </div>
        </div>
    </div>

    <Modal
        :show="showRemarkRepliesModal"
        max-width="4xl"
        @close="closeRemarkRepliesModal"
    >
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Remark Replies") }}
        </div>

        <hr />

        <div
            v-for="reply in replies"
            :key="reply.id"
            class="px-6 py-3.5 text-base bg-white border-b border-gray-200"
        >
            <div class="flex justify-between items-center mb-2">
                <div class="flex items-center">
                    <p
                        class="inline-flex items-center mr-3 text-sm text-gray-900 font-semibold"
                    >
                        {{ reply.creator.full_name }}
                    </p>
                    <p class="text-sm text-gray-600">
                        {{ dateTimeToLocaleString(reply.created_at) }}
                    </p>
                </div>
            </div>
            <p class="text-gray-500 whitespace-break-spaces">
                {{ reply.reply }}
            </p>
        </div>

        <div class="lg:px-4 items-center flex flex-col mt-3">
            <Textarea
                v-model="replyForm.reply"
                :placeholder="__('Write reply')"
                classes="h-full"
            />
        </div>

        <div class="col-span-2 flex justify-end gap-3 mt-2 pt-1 pb-3 px-4">
            <button
                :disabled="replyForm.processing"
                class="bg-[#008FE3] text-white px-12 py-2 rounded hover:opacity-80 active:scale-95 transition"
                @click="saveReply"
            >
                {{ __("Save Reply") }}
            </button>

            <button
                class="bg-[#F0F0F0] px-12 py-2 rounded hover:opacity-80 active:scale-95 transition"
                @click="closeRemarkRepliesModal"
            >
                {{ __("Close") }}
            </button>
        </div>
    </Modal>
</template>
