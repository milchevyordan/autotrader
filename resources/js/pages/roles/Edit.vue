<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import { computed } from "vue";

import Header from "@/components/Header.vue";
import ChangeLogs from "@/components/html/ChangeLogs.vue";
import Input from "@/components/html/Input.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import {
    Permission,
    Role,
    RoleForm,
} from "@/types";

const props = defineProps<{
    role: Role;
    allPermissions: Permission[];
}>();

const assignedPermissionIds = computed(() =>
    props.role.permissions.map((permission) => permission.id)
);

const form = useForm<RoleForm>({
    _method: "PUT",
    name: props.role.name,
    guard_name: props.role.guard_name,
    permissions: assignedPermissionIds.value,
});

const save = async () => {
    //     validate(form, companyFormRules);

    form.post(route("roles.update", props.role.id), {
        preserveScroll: true,
        preserveState: true,
        forceFormData: true,
        // only: withFlash(only),
        onSuccess: () => {},
        onError: () => {},
    });
};
</script>

<template>
    <Head :title="__('Edit Role')" />

    <AppLayout>
        <Header :text="__('Edit Role')" />

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
                            <h3 class="text-lg font-medium text-gray-700 mb-2">
                                {{ __("Permissions") }}
                            </h3>

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

                <ChangeLogs :change-logs="role.change_logs" />
            </div>
        </div>
    </AppLayout>
</template>
