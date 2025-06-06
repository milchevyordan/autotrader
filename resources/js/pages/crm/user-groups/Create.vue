<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

import Header from "@/components/Header.vue";
import Input from "@/components/html/Input.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import Table from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import IconMinus from "@/icons/Minus.vue";
import IconPlus from "@/icons/Plus.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { User, UserGroupForm } from "@/types";
import { dateTimeToLocaleString } from "@/utils";
import { validate } from "@/validations";

defineProps<{
    dataTable: DataTable<User>;
}>();

const selectedUsers = ref<User[]>([]);

const rules = {
    name: {
        required: true,
        type: "string",
    },
};

const form = useForm<UserGroupForm>({
    name: null!,
    userIds: [],
});

const save = () => {
    validate(form, rules);

    form.post(route("crm.user-groups.store"), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const addUser = (user: User) => {
    selectedUsers.value.push(user);
    form.userIds.push(user.id);
};

const removeUser = (user: User) => {
    const formIndex = form.userIds.indexOf(user.id);
    const vehicleIndex = selectedUsers.value.findIndex(
        (userObj) => userObj.id === user.id
    );

    selectedUsers.value.splice(vehicleIndex, 1);

    if (formIndex !== -1) {
        form.userIds.splice(formIndex, 1);
    }
};
</script>

<template>
    <Head :title="__('Crm User Group')" />

    <AppLayout>
        <Header :text="__('Crm User Group')" />

        <div class="my-4 bg-white p-4 rounded-md shadow">
            <!-- <Header :text="__('Group')" /> -->

            <div class="container w-1/3">
                <label for="name">
                    {{ __("Group") }} {{ __("Name") }}
                    <span class="text-red-500">*</span>
                </label>

                <Input
                    v-model="form.name"
                    :name="'name'"
                    type="text"
                    :placeholder="__('Name')"
                    class="mb-3.5 sm:mb-0"
                />
            </div>
        </div>

        <Table
            :data-table="dataTable"
            :per-page-options="[5, 10, 15, 20, 50]"
            :global-search="true"
            :advanced-filters="false"
            :selected-row-indexes="form.userIds"
            :selected-row-column="'id'"
        >
            <template #cell(created_at)="{ value, item }">
                <div class="flex gap-1.5">
                    {{ dateTimeToLocaleString(value) }}
                </div>
            </template>

            <template #cell(updated_at)="{ value, item }">
                <div class="flex gap-1.5">
                    {{ dateTimeToLocaleString(value) }}
                </div>
            </template>

            <template #cell(action)="{ value, item }">
                <div class="flex gap-1.5">
                    <div
                        v-if="form.userIds.includes(item.id)"
                        class="flex gap-1.5"
                    >
                        <button
                            class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                            @click="removeUser(item)"
                        >
                            <IconMinus classes="w-4 h-4 text-slate-600" />
                        </button>
                    </div>
                    <div v-else>
                        <button
                            class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                            @click="addUser(item)"
                        >
                            <IconPlus classes="w-4 h-4 text-slate-600" />
                        </button>
                    </div>
                </div>
            </template>
        </Table>

        <ResetSaveButtons
            :processing="form.processing"
            @reset="form.reset()"
            @save="save"
        />
    </AppLayout>
</template>
