<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";

import Checkbox from "@/components/html/Checkbox.vue";
import Input from "@/components/html/Input.vue";
import InputLabel from "@/components/html/InputLabel.vue";
import PrimaryButton from "@/components/html/PrimaryButton.vue";
import GuestLayout from "@/layouts/GuestLayout.vue";

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: "parkershanahan@jny.nl",
    password: "12345678",
    remember: true,
});

const submit = () => {
    form.post(route("login"), {
        onSuccess: () => {
            window.location.reload();
        },
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="__('Log in')" />

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

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
                    autocomplete="current-password"
                />
            </div>

            <div class="block mt-4">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ml-2 text-sm text-gray-600">
                        {{ __("Remember me") }}
                    </span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link
                    :href="route('password.request')"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none"
                >
                    {{ __("Forgot your password?") }}
                </Link>

                <PrimaryButton
                    class="ml-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ __("Log in") }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
