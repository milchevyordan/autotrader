<script setup lang="ts">
import { Link, router, useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";

import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Modal from "@/components/Modal.vue";
import Pagination from "@/data-table/components/Pagination.vue";
import { Paginator } from "@/data-table/types";
import { Country } from "@/enums/Country";
import IconBottomArrowAccordion from "@/icons/BottomArrowAccordion.vue";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import IconTrash from "@/icons/Trash.vue";
import { baseFormRules } from "@/rules/base-form-rules";
import { Company, CompanyGroup } from "@/types";
import { findEnumKeyByValue } from "@/utils";
import { validate } from "@/validations";

const emit = defineEmits(["updateForm", "deleteForm"]);

const props = defineProps<{
    paginator: Paginator;
    companyGroups: CompanyGroup[];
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

const handleButtonClick = (companyGroup: CompanyGroup, type: string) => {
    switch (type) {
        case "update":
            emit("updateForm", companyGroup.id, companyGroup.name);
            break;

        case "delete":
            emit("deleteForm", companyGroup.id, companyGroup.name);
            break;

        default:
            break;
    }
};

const showDeleteModal = ref(false);

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deleteForm.reset();
};

const showDeleteForm = (item: Company) => {
    deleteForm.id = item.id;
    deleteForm.name = item.name;
    showDeleteModal.value = true;
};

const deleteForm = useForm<{
    id: number;
    name: string;
}>({
    id: null!,
    name: null!,
});

const handleDelete = () => {
    validate(deleteForm, baseFormRules);

    deleteForm.delete(route("crm.companies.destroy", deleteForm.id as number), {
        preserveScroll: true,
    });
    closeDeleteModal();
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
        only: ["companyGroups"],
    });
};
</script>

<template>
    <div
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-1 justify-evenly xl:block gap-4 xl:space-y-4"
    >
        <div
            v-for="(companyGroup, groupIndex) in companyGroups"
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
                        {{ companyGroup.name }}
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        v-if="$can('edit-crm-company-group')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        @click="handleButtonClick(companyGroup, 'update')"
                    >
                        <IconPencilSquare classes="w-5 h-5 text-[#909090]" />
                    </button>
                    <button
                        v-if="$can('delete-crm-company-group')"
                        class="hidden xl:block border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        @click="handleButtonClick(companyGroup, 'delete')"
                    >
                        <IconTrash classes="w-5 h-5 text-[#909090]" />
                    </button>
                    <IconBottomArrowAccordion
                        v-show="
                            companyGroup.companies?.length &&
                            companyGroup.companies?.length > 1
                        "
                        class="w-5 h-5 transition-all duration-500 text-gray-500 cursor-pointer"
                        :class="{
                            'rotate-180': expandedGroupIds.includes(
                                companyGroup.id
                            ),
                        }"
                        @click="toggleExpanded(companyGroup.id)"
                    />
                </div>
            </div>

            <table
                v-if="companyGroup.companies?.length"
                class="w-full text-sm text-left text-gray-500"
            >
                <thead class="text-xs uppercase text-black bg-[#F0F0F0]">
                    <tr>
                        <th class="px-6 py-3 border-r">
                            {{ __("Action") }}
                        </th>
                        <th class="px-6 py-3 border-r">#</th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Name") }}
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Phone") }}
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Country") }}
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("City") }}
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Postal Code") }}
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Main Contact") }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(company, index) in companyGroup.companies"
                        v-show="
                            expandedGroupIds.includes(companyGroup.id) ||
                            index === 0
                        "
                        :key="index"
                        class="bg-white border-b border-[#E9E7E7]"
                    >
                        <td class="whitespace-nowrap px-6 py-3.5">
                            <div class="flex gap-1.5">
                                <Link
                                    v-if="$can('edit-crm-company')"
                                    class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                    :href="
                                        route('crm.companies.edit', company.id)
                                    "
                                >
                                    <IconPencilSquare
                                        classes="w-4 h-4 text-[#909090]"
                                    />
                                </Link>

                                <button
                                    v-if="$can('delete-crm-company')"
                                    :title="__('Delete company')"
                                    class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                    @click="showDeleteForm(company)"
                                >
                                    <IconTrash
                                        classes="w-4 h-4 text-[#909090]"
                                    />
                                </button>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-3.5">
                            {{ company.id }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-3.5">
                            {{ company.name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-3.5">
                            {{ company.phone }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-3.5">
                            {{ findEnumKeyByValue(Country, company.country) }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-3.5">
                            {{ company.city }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-3.5">
                            {{ company.postal_code }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-3.5">
                            {{ company.main_user?.full_name }}
                        </td>
                    </tr>

                    <tr
                        v-show="
                            !expandedGroupIds.includes(companyGroup.id) &&
                            companyGroup.companies.length > 1
                        "
                    >
                        <td
                            class="bg-white text-center font-semibold border-b border-[#E9E7E7] py-2 cursor-pointer"
                            colspan="8"
                            @click="toggleExpanded(companyGroup.id)"
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
                v-if="companyGroups.length > 1 || paginator.currentPage > 1"
                :paginator="paginator"
                :prop-name="'companyGroups'"
            />
        </div>
    </div>

    <Modal :show="showDeleteModal" @close="closeDeleteModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Delete company") }} "{{ deleteForm.name ?? "" }}" ?
        </div>

        <ModalSaveButtons
            :processing="deleteForm.processing"
            :save-text="__('Delete')"
            @cancel="closeDeleteModal"
            @save="handleDelete"
        />
    </Modal>
</template>
