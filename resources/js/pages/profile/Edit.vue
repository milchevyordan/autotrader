<script setup lang="ts">
import { Head } from "@inertiajs/vue3";

import DeleteUserForm from "./partials/DeleteUserForm.vue";
import UpdateImageForm from "./partials/UpdateImageForm.vue";
import UpdatePasswordForm from "./partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "../settings/partials/UpdateProfileInformationForm.vue";

import Header from "@/components/Header.vue";
import AppLayout from "@/layouts/AppLayout.vue";

import { DatabaseImage, User } from "@/types";

defineProps<{
    user: User;
    images: {
        profileImages: Array<DatabaseImage>;
    };
    mustVerifyEmail?: boolean;
    status?: string;
}>();
</script>

<template>
    <Head :title="__('Profile')" />

    <AppLayout>
        <Header :text="__('Profile')" />

        <div class="grid lg:grid-cols-2 gap-5">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <UpdateProfileInformationForm
                    :user="user"
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <div>
                <div
                    class="p-4 px-4 sm:p-8 mb-16 sm:mb-0 bg-white shadow sm:rounded-lg"
                >
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div
                    class="p-4 px-4 sm:p-8 mb-16 sm:mb-0 bg-white shadow sm:rounded-lg mt-5"
                >
                    <UpdateImageForm
                        :images="images.profileImages"
                        class="w-full"
                    />
                </div>
            </div>
        </div>

        <div class="p-4 px-4 sm:p-8 mt-5 bg-white shadow sm:rounded-lg">
            <DeleteUserForm class="max-w-xl" />
        </div>
    </AppLayout>
</template>
