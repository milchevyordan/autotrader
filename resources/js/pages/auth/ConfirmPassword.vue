<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";

import Input from "@/components/html/Input.vue";
import InputLabel from "@/components/html/InputLabel.vue";
import PrimaryButton from "@/components/html/PrimaryButton.vue";
import GuestLayout from "@/layouts/GuestLayout.vue";

const form = useForm({
    password: "",
});

const submit = () => {
    form.post(route("password.confirm"), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="__('Confirm Password')" />

        <div class="mb-4 text-sm text-gray-600">
            {{
                __(
                    "This is a secure area of the application. Please confirm your password before continuing."
                )
            }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="password" value="Password" />
                <Input
                    id="password"
                    v-model="form.password"
                    :name="'password'"
                    :type="form.password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="current-password"
                    autofocus
                />
            </div>

            <div class="flex justify-end mt-4">
                <PrimaryButton
                    class="ml-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ __("Confirm") }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
