<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import { computed } from "vue";

import PrimaryButton from "@/components/html/PrimaryButton.vue";
import GuestLayout from "@/layouts/GuestLayout.vue";

const props = defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(route("verification.send"));
};

const verificationLinkSent = computed(
    () => props.status === "verification-link-sent"
);
</script>

<template>
    <GuestLayout>
        <Head :title="__('Email Verification')" />

        <div class="mb-4 text-sm text-gray-600">
            {{
                __(
                    "Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another."
                )
            }}
        </div>

        <div
            v-if="verificationLinkSent"
            class="mb-4 font-medium text-sm text-green-600"
        >
            {{
                __(
                    "A new verification link has been sent to the email address you provided during registration."
                )
            }}
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ __("Resend Verification Email") }}
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    {{ __("Log Out") }}
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
