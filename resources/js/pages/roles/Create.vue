<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";

import Header from "@/components/Header.vue";
import Input from "@/components/html/Input.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { roleFormRules } from "@/rules/role-form-rules";
import {
    Permission,
    RoleForm,
} from "@/types";
import { validate } from "@/validations";

defineProps<{
    allPermissions: Permission[];
}>();


const form = useForm<RoleForm>({
    name: null!,
    guard_name: 'web',
    permissions: [],
});

const save = async () => {
    validate(form, roleFormRules);

    form.post(route("roles.store"), {
        preserveScroll: true,
        preserveState: true,
        forceFormData: true,
        onSuccess: () => {},
        onError: () => {},
    });
};
</script>

<template>
    <Head :title="__('Create Role')" />

    <AppLayout>
        <Header :text="__('Create Role')" />

        <div class="max-w-4xl mx-auto px-4 py-6">
            <div class="space-y-6">
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="text-xl font-medium text-gray-800 mb-6">
                        <label for="name">
                            {{ __("Role name") }}
                            <span class="text-red-500"> *</span>
                        </label>

                        <Input
                            v-model="form.name"
                            :name="'name'"
                            type="text"
                            :placeholder="__('Name')"
                            class="mb-3.5 sm:mb-0 mt-2"
                        />
                    </div>

                    <form @submit.prevent="save">
                        <div class="mb-6">
                            <div class="text-lg font-medium text-gray-700 mb-2">
                                {{ __("Permissions") }}
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                <label
                                    v-for="permission in allPermissions"
                                    :key="permission.id"
                                    class="inline-flex items-center space-x-2"
                                >
                                    <input
                                        v-model="form.permissions"
                                        type="checkbox"
                                        :value="permission.id"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-200"
                                    >
                                    <span class="text-gray-700">{{
                                        permission.name
                                    }}</span>
                                </label>
                            </div>
                        </div>

                        <ResetSaveButtons
                            :processing="form.processing"
                            @reset="form.reset()"
                            @save="save"
                        />
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
