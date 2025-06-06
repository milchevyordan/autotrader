<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";

import Input from "@/components/html/Input.vue";
import InputLabel from "@/components/html/InputLabel.vue";
import PrimaryButton from "@/components/html/PrimaryButton.vue";
import GuestLayout from "@/layouts/GuestLayout.vue";

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: "",
});

const submit = () => {
    form.post(route("password.email"));
};
</script>

<template>
    <GuestLayout>
        <Head :title="__('Forgot Password')" />

        <div class="mb-4 text-sm text-gray-600">
            {{
                __(
                    "Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one."
                )
            }}
        </div>

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
                    :type="form.email"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="username"
                />
            </div>

            <div class="flex items-center justify-end mt-4">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ __("Email Password Reset Link") }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
