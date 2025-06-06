<script setup lang="ts">
import { Link, useForm } from "@inertiajs/vue3";

import Input from "@/components/html/Input.vue";
import InputLabel from "@/components/html/InputLabel.vue";
import PrimaryButton from "@/components/html/PrimaryButton.vue";
import Select from "@/components/Select.vue";
import { Gender } from "@/enums/Gender";
import { profileFormRules } from "@/rules/profile-form-rules";
import { User } from "@/types";
import { validate } from "@/validations";
import { Locale } from "@/enums/Locale";

const props = defineProps<{
    user: User;
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const form = useForm({
    _method: "patch",
    prefix: props.user.prefix,
    first_name: props.user.first_name,
    last_name: props.user.last_name,
    email: props.user.email,
    mobile: props.user.mobile,
    other_phone: props.user.other_phone,
    gender: props.user.gender,
    language: props.user.language,
});

const save = async () => {
    validate(form, profileFormRules);

    return new Promise<void>((resolve, reject) => {
        form.post(route("profile.update"), {
            preserveScroll: true,
            preserveState: true,
            forceFormData: true, // preserves all form data
            onSuccess: () => {
                resolve();
            },
            onError: () => {
                reject(new Error("Error, during update"));
            },
        });
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                {{ __("Profile Information") }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{
                    __(
                        "Update your account's profile information and email address."
                    )
                }}
            </p>
        </header>

        <div class="mt-6 space-y-2">
            <div
                class="grid grid-cols-1 sm:grid-cols-2 sm:gap-y-2 items-center"
            >
                <InputLabel for="prefix" :value="__('Prefix')" />

                <Input
                    id="prefix"
                    v-model="form.prefix"
                    :name="'prefix'"
                    type="text"
                    class="mt-1 block w-full"
                    autofocus
                    autocomplete="prefix"
                />

                <InputLabel for="first_name" :value="__('First Name')" />
                <Input
                    id="first_name"
                    v-model="form.first_name"
                    :name="'first_name'"
                    type="text"
                    class="mt-1 block w-full"
                    autofocus
                    autocomplete="first_name"
                />

                <InputLabel for="last_name" :value="__('Last Name')" />

                <Input
                    id="last_name"
                    v-model="form.last_name"
                    :name="'last_name'"
                    type="text"
                    class="mt-1 block w-full"
                    autofocus
                    autocomplete="last_name"
                />

                <InputLabel for="email" value="Email" />

                <Input
                    id="email"
                    v-model="form.email"
                    :name="'email'"
                    type="email"
                    class="mt-1 block w-full"
                    autocomplete="email"
                />

                <InputLabel for="mobile" :value="__('Mobile')" />

                <Input
                    id="mobile"
                    v-model="form.mobile"
                    :name="'mobile'"
                    type="text"
                    class="mt-1 block w-full"
                    autocomplete="mobile"
                />

                <InputLabel for="other_phone" :value="__('Other Phone')" />

                <Input
                    id="other_phone"
                    v-model="form.other_phone"
                    :name="'other_phone'"
                    type="text"
                    class="mt-1 block w-full"
                    autocomplete="other_phone"
                />

                <InputLabel for="gender" :value="__('Gender')" />

                <Select
                    id="gender"
                    v-model="form.gender"
                    :name="'gender'"
                    :options="Gender"
                    :placeholder="__('Gender')"
                    class="mt-1 block w-full"
                />

                <InputLabel for="language" :value="__('Language')" />

                <Select
                    id="language"
                    v-model="form.language"
                    :name="'language'"
                    :options="Locale"
                    :capitalize="true"
                    :placeholder="__('Language')"
                    class="mt-1 block w-full"
                />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm mt-2 text-gray-800">
                    {{ __("Your email address is unverified.") }}
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        {{
                            __("Click here to re-send the verification email.")
                        }}
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 font-medium text-sm text-green-600"
                >
                    {{
                        __(
                            "A new verification link has been sent to your email address."
                        )
                    }}
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing" @click="save">
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
        </div>
    </section>
</template>
