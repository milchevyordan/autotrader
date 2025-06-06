<script setup lang="ts">
import { Link, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";

import Pagination from "@/data-table/components/Pagination.vue";
import { Paginator } from "@/data-table/types";
import IconBottomArrowAccordion from "@/icons/BottomArrowAccordion.vue";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import IconTrash from "@/icons/Trash.vue";
import { UserGroup } from "@/types";
import { dateTimeToLocaleString } from "@/utils";

const emit = defineEmits(["deleteForm"]);

const props = defineProps<{
    paginator: Paginator;
    userGroups: UserGroup[];
}>();

watch(
    () => props.paginator,
    () => {
        loadGroupIds = [];
        expandedGroupIds.value = [];
    }
);

const expandedGroupIds = ref<number[]>([]);
let loadGroupIds: number[] = [];

const handleDeleteModalClick = (userGroup: UserGroup) => {
    emit("deleteForm", userGroup.id, userGroup.name);
};

const toggleExpanded = (groupId: number) => {
    const index = expandedGroupIds.value.indexOf(groupId);
    expandedGroupIds.value =
        index === -1
            ? [...expandedGroupIds.value, groupId]
            : expandedGroupIds.value.filter((x) => x !== groupId);

    if (!loadGroupIds.includes(groupId)) {
        loadGroupIds.push(groupId);

        loadAll();
    }
};

const loadAll = () => {
    router.reload({
        data: {
            loadGroupIds: loadGroupIds,
        },
        only: ["userGroups"],
    });
};
</script>

<template>
    <div
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-1 justify-evenly xl:block gap-4 xl:space-y-4"
    >
        <div
            v-for="(userGroup, groupIndex) in userGroups"
            :key="groupIndex"
            class="py-1"
        >
            <div
                class="flex items-center justify-between py-2.5 bg-white shadow-md border rounded-t-lg border-[#E9E7E7] px-4"
            >
                <div class="flex items-center gap-2">
                    <div
                        class="p-2 rounded-md element-center bg-[#008FE3] text-white text-sm font-semibold"
                    >
                        {{ userGroup.name }}
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        v-if="$can('edit-user-group')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        :href="route('crm.user-groups.edit', userGroup.id)"
                    >
                        <IconPencilSquare classes="w-5 h-5 text-[#909090]" />
                    </Link>
                    <button
                        v-if="$can('delete-user-group')"
                        class="hidden xl:block border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        @click="handleDeleteModalClick(userGroup)"
                    >
                        <IconTrash classes="w-5 h-5 text-[#909090]" />
                    </button>
                    <IconBottomArrowAccordion
                        v-show="
                            userGroup.users?.length &&
                            userGroup.users?.length > 1
                        "
                        class="w-5 h-5 transition-all duration-500 text-gray-500 cursor-pointer"
                        :class="{
                            'rotate-180': expandedGroupIds.includes(
                                userGroup.id
                            ),
                        }"
                        @click="toggleExpanded(userGroup.id)"
                    />
                </div>
            </div>

            <table
                v-if="userGroup.users?.length"
                class="w-full text-sm text-left text-gray-500"
            >
                <thead class="text-xs uppercase text-black bg-[#F0F0F0]">
                    <tr>
                        <th class="px-6 py-3 border-r">#</th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Creator") }}
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Company") }}
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Name") }}
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Email") }}
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Date") }}
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Updated") }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(user, index) in userGroup.users"
                        v-show="
                            expandedGroupIds.includes(userGroup.id) ||
                            index === 0
                        "
                        :key="index"
                        class="bg-white border-b border-[#E9E7E7]"
                    >
                        <td class="whitespace-nowrap px-6 py-3.5">
                            {{ user.id }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-3.5">
                            {{ user.creator.full_name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-3.5">
                            {{ user.company.name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-3.5">
                            {{ user.full_name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-3.5">
                            {{ user.email }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-3.5">
                            {{ dateTimeToLocaleString(user.created_at) }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-3.5">
                            {{ dateTimeToLocaleString(user.updated_at) }}
                        </td>
                    </tr>

                    <tr
                        v-show="
                            !expandedGroupIds.includes(userGroup.id) &&
                            userGroup.users.length > 1
                        "
                    >
                        <td
                            class="bg-white text-center font-semibold border-b border-[#E9E7E7] py-2 cursor-pointer"
                            colspan="7"
                            @click="toggleExpanded(userGroup.id)"
                        >
                            {{ __("See all") }}...
                        </td>
                    </tr>
                </tbody>
            </table>

            <div
                v-else
                class="w-full text-lg font-semibold py-2 text-center text-gray-500 bg-white border-b border-[#E9E7E7]"
            >
                {{ __("No found data") }}
            </div>
        </div>

        <div>
            <Pagination
                v-if="userGroups.length > 1 || paginator.currentPage > 1"
                :paginator="paginator"
                :prop-name="'userGroups'"
            />
        </div>
    </div>
</template>
