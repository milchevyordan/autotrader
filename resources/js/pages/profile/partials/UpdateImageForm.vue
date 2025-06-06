<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";

import InputImage from "@/components/html/InputImage.vue";
import PrimaryButton from "@/components/html/PrimaryButton.vue";
import { DatabaseImage } from "@/types";

defineProps<{
    images: Array<DatabaseImage>;
}>();

const form = useForm<{
    profileImages: Array<File>;
}>({
    profileImages: [],
});

const updateProfileImage = () => {
    form.post(route("profile.update-profile-image"), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                {{ __("Profile Photo") }}
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                {{ __("First image will be profile image.") }}
            </p>
        </header>

        <div class="mt-6 w-full">
            <InputImage
                v-model="form.profileImages"
                :images="images"
                text-classes="py-6"
            />

            <div class="flex items-center gap-4">
                <PrimaryButton
                    :disabled="form.processing"
                    @click="updateProfileImage"
                >
                    {{ __("Save") }}
                </PrimaryButton>
            </div>
        </div>
    </section>
</template>
