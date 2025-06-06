<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";

import Header from "@/components/Header.vue";
import Input from "@/components/html/Input.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import Textarea from "@/components/html/Textarea.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { EmailTemplate, EmailTemplateForm } from "@/types";
import { validate } from "@/validations";

const props = defineProps<{
    emailTemplate: EmailTemplate;
}>();

const rules = {
    text_top: {
        required: false,
        type: "string",
    },

    text_bottom: {
        required: false,
        type: "string",
    },
};

const form = useForm<EmailTemplateForm>({
    _method: "put",
    text_top: props.emailTemplate.text_top,
    text_bottom: props.emailTemplate.text_bottom,
});

const save = async () => {
    validate(form, rules);

    return new Promise<void>((resolve, reject) => {
        form.post(route("email-templates.update", props.emailTemplate.id), {
            preserveScroll: true,
            preserveState: true,
            forceFormData: true,
            onSuccess: () => {
                resolve();
            },
            onError: () => {
                reject(new Error("Error, during update"));
            },
        });
    });
};
</script>

<template>
    <Head :title="__('Quote')" />

    <AppLayout>
        <Header :text="__('Email Template')" />

        <div
            class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
        >
            <label for="name">
                {{ __("Name") }}
            </label>
            <Input
                v-model="props.emailTemplate.name"
                disabled="true"
                :name="'name'"
                type="text"
                :placeholder="__('Name')"
                class="mb-3.5"
            />

            <label for="name">
                {{ __("Top") }} {{ __("Text") }}
                <span v-if="!form.text_bottom" class="text-red-500"> *</span>
            </label>
            <Textarea
                v-model="form.text_top"
                :name="'billing_remarks'"
                :placeholder="__('Text') + ' ' + __('Top')"
                classes="mb-3.5"
            />

            <label for="name">
                {{ __("Bottom") }} {{ __("Text") }}
                <span v-if="!form.text_top" class="text-red-500"> *</span>
            </label>
            <Textarea
                v-model="form.text_bottom"
                :name="'billing_remarks'"
                :placeholder="__('Text') + ' ' + __('Bottom')"
                classes="mb-3.5"
            />
        </div>

        <ResetSaveButtons
            :processing="form.processing"
            @reset="form.reset()"
            @save="save"
        />
    </AppLayout>
</template>
