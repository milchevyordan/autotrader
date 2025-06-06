<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";

import Header from "@/components/Header.vue";
import ChangeLogs from "@/components/html/ChangeLogs.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import GeneralInformation from "@/components/users/GeneralInformation.vue";
import { Multiselect } from "@/data-table/types";
import AppLayout from "@/layouts/AppLayout.vue";
import { userFormRules } from "@/rules/user-form-rules";
import { Company, Role, User, UserForm } from "@/types";
import { withFlash } from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    user: User;
    userRoles: number[];
    tokenExists: boolean;
    companies: Multiselect<Company>;
    roles: Multiselect<Role>;
}>();

const form = useForm<UserForm>({
    _method: "put",
    id: props.user.id,
    prefix: props.user.prefix,
    first_name: props.user.first_name,
    last_name: props.user.last_name,
    company_id: props.user.company_id,
    email: props.user.email,
    mobile: props.user.mobile,
    other_phone: props.user.other_phone,
    gender: props.user.gender,
    language: props.user.language,
    roles: props.userRoles,
});

const save = async (only?: Array<string>) => {
    validate(form, userFormRules);

    return new Promise<void>((resolve, reject) => {
        form.post(route("users.update", props.user.id as number), {
            preserveScroll: true,
            preserveState: true,
            forceFormData: true, // preserves all form data
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
                    :token-exists="tokenExists"
                    :roles="roles"
                    :form-disabled="true"
                />

                <ChangeLogs :change-logs="user.change_logs" />
            </div>
        </div>

        <ResetSaveButtons
            :processing="form.processing"
            @reset="form.reset()"
            @save="save"
        />
    </AppLayout>
</template>
