<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";

import Input from "@/components/html/Input.vue";
import InputLabel from "@/components/html/InputLabel.vue";
import PrimaryButton from "@/components/html/PrimaryButton.vue";
import GuestLayout from "@/layouts/GuestLayout.vue";
import { validate } from "@/validations";

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: "",
    password_confirmation: "",
});

const rules = {
    email: {
        required: true,
        type: "email",
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

const submit = () => {
    validate(form, rules);

    form.post(route("password.store"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="__('Reset Password')" />

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <Input
                    id="email"
                    v-model="form.email"
                    :name="'email'"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="username"
                />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <Input
                    id="password"
                    v-model="form.password"
                    :name="'password'"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
            </div>

            <div class="mt-4">
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
                    required
                    autocomplete="new-password"
                />
            </div>

            <div class="flex items-center justify-end mt-4">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ __("Reset Password") }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
