<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";

import Input from "@/components/html/Input.vue";
import InputLabel from "@/components/html/InputLabel.vue";
import PrimaryButton from "@/components/html/PrimaryButton.vue";
import GuestLayout from "@/layouts/GuestLayout.vue";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="__('Register')" />

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Name" />

                <Input
                    id="name"
                    v-model="form.name"
                    :name="'name'"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="name"
                />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />

                <Input
                    id="email"
                    v-model="form.email"
                    :name="'email'"
                    type="email"
                    class="mt-1 block w-full"
                    required
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
                <Link
                    href="/"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md"
                >
                    {{ __("Already registered?") }}
                </Link>

                <PrimaryButton
                    class="ml-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ __("Register") }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
