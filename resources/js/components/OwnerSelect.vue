<script setup lang="ts">
import Select from "@/components/Select.vue";
import { Multiselect } from "@/data-table/types";
import { Ownership, User } from "@/types";

withDefaults(
    defineProps<{
        users: Multiselect<User>;
        pendingOwnerships?: Ownership[];
        label?: string;
        disabled?: boolean;
    }>(),
    {
        label: "Owner",
    }
);

const model = defineModel<null | string | number>();
</script>

<template>
    <label for="owner">
        {{ __(label) }}
        <span class="text-red-500"> *</span>
    </label>

    <div class="owner-container">
        <Select
            id="owner"
            :key="model as number"
            v-model="model"
            :name="'owner_id'"
            :options="users"
            :placeholder="__('Owner')"
            class="w-full mb-3.5 sm:mb-0"
            :disabled="disabled"
        />

        <div
            v-if="pendingOwnerships && Object.keys(pendingOwnerships)?.length"
            class="shadow p-4 overflow-y-auto max-h-36 text-[#6D6D73]"
        >
            <span class="text-[#C7C7CC] mb-2 p-1 element-center">
                {{ __("Invitation has been sent to") }}
            </span>

            <div class="flex flex-wrap">
                <span
                    v-for="(pendingOwnership, index) in pendingOwnerships"
                    :key="index"
                    class="bg-[#F2F2F7] cursor-default rounded px-2 py-1 font-light m-1"
                >
                    {{ pendingOwnership.user.full_name }}
                </span>
            </div>
        </div>
    </div>
</template>
