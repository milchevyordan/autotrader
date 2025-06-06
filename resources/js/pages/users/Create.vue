<script setup lang="ts">
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { computed, ComputedRef } from "vue";

import Header from "@/components/Header.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import GeneralInformation from "@/components/users/GeneralInformation.vue";
import { Multiselect } from "@/data-table/types";
import AppLayout from "@/layouts/AppLayout.vue";
import { userFormRules } from "@/rules/user-form-rules";
import { Company, UserForm } from "@/types";
import { withFlash } from "@/utils";
import { validate } from "@/validations";

defineProps<{
    companies: Multiselect<Company>;
    roles: Multiselect<{
        name: string;
        type: string;
    }>;
}>();

const isRootUser: ComputedRef<boolean> = computed(() => {
    return usePage().props.auth.user.roles.includes("Root");
});

const form = useForm<UserForm>({
    id: null!,
    prefix: null!,
    first_name: null!,
    last_name: null!,
    company_id: null!,
    email: null!,
    mobile: null!,
    other_phone: null!,
    gender: null!,
    language: null!,
    roles: null!,
});

const save = async (only?: Array<string>) => {
    const rules = { ...userFormRules };

    if (isRootUser.value) {
        rules.company_id = {
            ...rules.company_id,
            required: true,
        };
    }

    validate(form, rules);

    return new Promise<void>((resolve, reject) => {
        form.post(route("users.store"), {
            preserveScroll: true,
            preserveState: true,
            only: withFlash(only),
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
    <Head :title="__('User')" />

    <AppLayout>
        <Header :text="__('User')" />

        <div class="flex justify-center">
            <div class="w-full max-w-[1721px]">
                <GeneralInformation
                    :form="form"
                    :companies="companies"
                    :roles="roles"
                    :hide-company="!isRootUser"
                />
            </div>
        </div>

        <ResetSaveButtons
            :processing="form.processing"
            @reset="form.reset()"
            @save="save"
        />
    </AppLayout>
</template>
