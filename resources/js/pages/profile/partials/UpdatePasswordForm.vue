<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";

import Input from "@/components/html/Input.vue";
import InputLabel from "@/components/html/InputLabel.vue";
import PrimaryButton from "@/components/html/PrimaryButton.vue";
import { validate } from "@/validations";

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const rules = {
    current_password: {
        required: true,
        type: "password",
        requireUppercase: true,
        requireLowercase: true,
        requireSymbol: true,
        requireNumber: true,
        requireLetter: true,
        minLength: 8,
    },
    password: {
        required: true,
        type: "password",
        requireUppercase: true,
        requireLowercase: true,
        requireSymbol: true,
        requireNumber: true,
        requireLetter: true,
        minLength: 8,
    },
    password_confirmation: {
        required: true,
        type: "password",
        requireUppercase: true,
        requireLowercase: true,
        requireSymbol: true,
        requireNumber: true,
        requireLetter: true,
        minLength: 8,
    },
};

const updatePassword = () => {
    validate(form, rules);

    form.put(route("password.update"), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset("password", "password_confirmation");
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset("current_password");
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                {{ __("Update Password") }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{
                    __(
                        "Ensure your account is using a long, random password to stay secure."
                    )
                }}
            </p>
        </header>

        <form class="mt-6 space-y-6" @submit.prevent="updatePassword">
            <div
                class="grid grid-cols-1 sm:grid-cols-2 sm:gap-y-2 items-center"
            >
                <InputLabel for="current_password" value="Current Password" />

                <Input
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    :name="'current_password'"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="current-password"
                />

                <InputLabel for="password" value="New Password" />

                <Input
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    :name="'password'"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                />

                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                />

                <Input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    :name="'password_confirmation'"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">
                    Save
                </PrimaryButton>

                <Transition
                    enter-from-class="opacity-0"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        {{ __("Saved.") }}
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
