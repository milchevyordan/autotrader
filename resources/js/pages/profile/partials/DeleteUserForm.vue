<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { nextTick, ref } from "vue";

import DangerButton from "@/components/html/DangerButton.vue";
import Input from "@/components/html/Input.vue";
import InputLabel from "@/components/html/InputLabel.vue";
import SecondaryButton from "@/components/html/SecondaryButton.vue";
import Modal from "@/components/Modal.vue";

const confirmingUserDeletion = ref(false);
const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    password: "",
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route("profile.destroy"), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                {{ __("Delete Account") }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{
                    __(
                        "Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain."
                    )
                }}
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion">
            Delete Account
        </DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ __("Are you sure you want to delete your account?") }}
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    {{
                        __(
                            "Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account."
                        )
                    }}
                </p>

                <div class="mt-6">
                    <InputLabel
                        for="password"
                        value="Password"
                        class="sr-only"
                    />

                    <Input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        :name="'password'"
                        type="password"
                        class="mt-1 block w-3/4"
                        placeholder="Password"
                        @keyup.enter="deleteUser"
                    />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">
                        {{ __("Cancel") }}
                    </SecondaryButton>

                    <DangerButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        {{ __("Delete Account") }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
