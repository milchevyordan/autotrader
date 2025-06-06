<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";

import Accordion from "@/components/Accordion.vue";
import DatePicker from "@/components/html/DatePicker.vue";
import InformationRow from "@/components/html/InformationRow.vue";
import Input from "@/components/html/Input.vue";
import InputFile from "@/components/html/InputFile.vue";
import InputImage from "@/components/html/InputImage.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Modal from "@/components/Modal.vue";
import Select from "@/components/Select.vue";
import { Multiselect } from "@/data-table/types";
import { WorkOrderTaskStatus } from "@/enums/WorkOrderTaskStatus";
import { WorkOrderTaskType } from "@/enums/WorkOrderTaskType";
import IconDocument from "@/icons/Document.vue";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import IconTrash from "@/icons/Trash.vue";
import { baseFormRules } from "@/rules/base-form-rules";
import { workOrderTasksFormRules } from "@/rules/work-order-tasks-form-rules";
import {
    DatabaseFile,
    DatabaseImage,
    User,
    WorkOrder,
    WorkOrderTask,
    WorkOrderTaskForm,
} from "@/types";
import {
    dateTimeToLocaleString,
    dateToLocaleString,
    findEnumKeyByValue,
    findKeyByValue,
    formatPriceOnBlur,
    formatPriceOnFocus,
    replaceEnumUnderscores,
    withFlash,
} from "@/utils.js";
import { validate } from "@/validations";

const props = defineProps<{
    workOrder: WorkOrder;
    mainCompanyUsers: Multiselect<User>;
}>();

const modelDefaults: WorkOrderTaskForm = {
    _method: "post",
    id: null!,
    work_order_id: props.workOrder.id,
    creator_id: null!,
    name: null!,
    description: null!,
    status: WorkOrderTaskStatus.Open,
    type: null!,
    assigned_to_id: null!,
    estimated_price: null!,
    actual_price: null!,
    planned_date: null!,
    images: [],
    files: [],
};

const images: DatabaseImage[] = [];
const files: DatabaseFile[] = [];

const storeForm = useForm<WorkOrderTaskForm>(modelDefaults);
const updateForm = useForm<WorkOrderTaskForm>(modelDefaults);
const deleteForm = useForm<WorkOrderTaskForm>(modelDefaults);
const showModalTask = ref<WorkOrderTask>(null!);

const showCreateModal = ref(false);
const showUpdateModal = ref(false);
const showDeleteModal = ref(false);
const showTaskInformationModal = ref(false);

const closeCreateModal = () => {
    showCreateModal.value = false;
    storeForm.reset();
};

const closeUpdateModal = () => {
    showUpdateModal.value = false;
    updateForm.reset();
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deleteForm.reset();
};

const store = () => {
    validate(storeForm, workOrderTasksFormRules);

    storeForm.post(route("work-order-tasks.store"), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            storeForm.reset();
            closeCreateModal();
        },
        onError: () => {},
    });
};

const update = async (only?: Array<string>) => {
    validate(updateForm, workOrderTasksFormRules);

    return new Promise<void>((resolve, reject) => {
        updateForm.post(
            route("work-order-tasks.update", updateForm.id as number),
            {
                preserveScroll: true,
                preserveState: true,
                only: withFlash(only),
                onSuccess: () => {
                    updateForm.reset();
                    closeUpdateModal();
                    resolve();
                },
                onError: () => {
                    reject(new Error("Error, during update"));
                },
            }
        );
    });
};

const handleDelete = () => {
    validate(deleteForm, baseFormRules);

    deleteForm.delete(
        route("work-order-tasks.destroy", deleteForm.id as number),
        {
            preserveScroll: true,
            preserveState: true,
        }
    );
    closeDeleteModal();
};

const showEditForm = (task: WorkOrderTask) => {
    updateForm._method = "put";
    updateForm.id = task.id;
    updateForm.work_order_id = task.work_order_id;
    updateForm.creator_id = task.creator_id;
    updateForm.name = task.name;
    updateForm.description = task.description;
    updateForm.type = task.type;
    updateForm.status = task.status;
    updateForm.assigned_to_id = task.assigned_to_id;
    updateForm.estimated_price = task.estimated_price;
    updateForm.actual_price = task.actual_price;
    updateForm.planned_date = task.planned_date;
    updateForm.images = [];
    updateForm.existing_images = task.images;
    updateForm.files = [];
    updateForm.existing_files = task.files;

    showUpdateModal.value = true;
};

const showCreateForm = () => {
    storeForm._method = "post";
    showCreateModal.value = true;
};

const openTaskInformationModal = (task: WorkOrderTask) => {
    showTaskInformationModal.value = true;
    showModalTask.value = task;
};

const closeTaskInformationModal = () => {
    showTaskInformationModal.value = false;
    showModalTask.value = null!;
};

const handleRowClick = (event: MouseEvent, task: WorkOrderTask) => {
    if (["DIV", "TD"].includes((event.target as HTMLElement).tagName)) {
        openTaskInformationModal(task);
    }
};
</script>

<template>
    <div
        class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
    >
        <Accordion>
            <template #head>
                <div class="font-semibold text-xl sm:text-2xl mb-4">
                    {{ __("Tasks") }}
                </div>
            </template>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
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
                                {{ __("Type") }}
                            </th>
                            <th class="px-6 py-3 border-r">
                                {{ __("Status") }}
                            </th>
                            <th class="px-6 py-3 border-r">
                                {{ __("Estimated Price") }}
                            </th>
                            <th class="px-6 py-3 border-r">
                                {{ __("Actual Price") }}
                            </th>
                            <th class="px-6 py-3 border-r">
                                {{ __("Assigned To") }}
                            </th>
                            <th class="px-6 py-3 border-r">
                                {{ __("Date") }}
                            </th>
                            <th class="px-6 py-3 border-r">
                                {{ __("Planned Date") }}
                            </th>
                            <th class="px-6 py-3 border-r">
                                {{ __("Completed") }}
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="(task, rowIndex) in workOrder.tasks"
                            v-if="workOrder.tasks.length > 0"
                            :key="rowIndex"
                            class="bg-white border-b border-[#E9E7E7]"
                            :class="{
                                'cursor-pointer': $can('view-work-order-task'),
                            }"
                            @click="
                                $can('view-work-order-task')
                                    ? handleRowClick($event, task)
                                    : ''
                            "
                        >
                            <td class="whitespace-nowrap px-6 py-3.5">
                                <div class="flex gap-1.5">
                                    <button
                                        v-if="$can('edit-work-order-task')"
                                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                        @click="showEditForm(task)"
                                    >
                                        <IconPencilSquare
                                            classes="w-4 h-4 text-[#909090]"
                                        />
                                    </button>

                                    <button
                                        v-if="$can('view-work-order-task')"
                                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                        @click="openTaskInformationModal(task)"
                                    >
                                        <IconDocument
                                            classes="w-4 h-4 text-[#909090]"
                                        />
                                    </button>

                                    <button
                                        v-if="$can('delete-work-order-task')"
                                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                        :title="__('Delete work order task')"
                                        @click="
                                            showDeleteModal = true;
                                            deleteForm.id = task.id;
                                        "
                                    >
                                        <IconTrash
                                            classes="w-4 h-4 text-[#909090]"
                                        />
                                    </button>
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-6 py-3.5">
                                {{ task.id }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-3.5">
                                {{ task.name }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-3.5">
                                {{
                                    replaceEnumUnderscores(
                                        findEnumKeyByValue(
                                            WorkOrderTaskType,
                                            task.type
                                        )
                                    )
                                }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-3.5">
                                {{
                                    findEnumKeyByValue(
                                        WorkOrderTaskStatus,
                                        task.status
                                    )
                                }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-3.5">
                                {{ task.estimated_price }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-3.5">
                                {{ task.actual_price }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-3.5">
                                {{
                                    findKeyByValue(
                                        props.mainCompanyUsers,
                                        task.assigned_to_id
                                    )
                                }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-3.5">
                                {{ dateTimeToLocaleString(task.created_at) }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-3.5">
                                {{ dateToLocaleString(task.planned_date) }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-3.5">
                                {{ dateTimeToLocaleString(task.completed_at) }}
                            </td>
                        </tr>

                        <tr v-else>
                            <td
                                class="bg-white text-center py-5 text-lg font-semibold border-b border-[#E9E7E7]"
                                colspan="11"
                            >
                                {{ __("No found data") }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <button
                v-if="$can('create-work-order-task')"
                class="bg-[#00A793] text-white rounded px-5 py-2 mt-4 font-light active:scale-95 transition hover:bg-emerald-500 cursor-pointer"
                @click="showCreateForm"
            >
                {{ __("Add task") }}
            </button>
        </Accordion>
    </div>

    <Modal :show="showCreateModal" @close="closeCreateModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Create Task") }}
        </div>

        <div class="p-3.5">
            <label for="create_name">
                {{ __("Name") }}
                <span class="text-red-500"> *</span>
            </label>
            <Input
                id="create_name"
                v-model="storeForm.name"
                :name="'name'"
                type="text"
                :placeholder="__('Name')"
                class="mb-3.5"
            />

            <label for="create_type">
                {{ __("Type") }}
                <span class="text-red-500"> *</span>
            </label>
            <Select
                id="create_type"
                v-model="storeForm.type"
                :name="'type'"
                :options="WorkOrderTaskType"
                :placeholder="__('Type')"
                class="w-full mb-3.5"
            />

            <label for="create_status">
                {{ __("Status") }}
                <span class="text-red-500"> *</span>
            </label>
            <Select
                id="create_status"
                v-model="storeForm.status"
                :name="'status'"
                :options="WorkOrderTaskStatus"
                :placeholder="__('Status')"
                class="w-full mb-3.5"
            />

            <label for="create_estimated_price">
                {{ __("Estimated Price") }}
                <span class="text-red-500"> *</span>
            </label>
            <Input
                id="create_estimated_price"
                v-model="storeForm.estimated_price"
                :name="'estimated_price'"
                type="text"
                class="mb-3.5"
                :placeholder="__('Estimated Price')"
                @blur="formatPriceOnBlur(storeForm, 'estimated_price')"
                @focus="formatPriceOnFocus(storeForm, 'estimated_price')"
            />

            <label for="create_actual_price">
                {{ __("Actual Price") }}
                <span
                    v-if="storeForm.status == WorkOrderTaskStatus.Completed"
                    class="text-red-500"
                >
                    *</span
                >
            </label>
            <Input
                id="create_actual_price"
                v-model="storeForm.actual_price"
                :name="'actual_price'"
                type="text"
                class="mb-3.5"
                :placeholder="__('Actual Price')"
                @blur="formatPriceOnBlur(storeForm, 'actual_price')"
                @focus="formatPriceOnFocus(storeForm, 'actual_price')"
            />

            <label for="create_assigned_to_id">
                {{ __("Assigned To") }}
            </label>
            <Select
                id="create_assigned_to_id"
                v-model="storeForm.assigned_to_id"
                :name="'assigned_to_id'"
                :options="mainCompanyUsers"
                :placeholder="__('Assigned To')"
                class="w-full mb-3.5"
            />

            <label for="create_planned_date">
                {{ __("Planned Date") }}
            </label>
            <DatePicker
                id="create_planned_date"
                v-model="storeForm.planned_date"
                :name="'planned_date'"
                :enable-time-picker="false"
                class="mb-3.5"
                :placeholder="__('Planned Date')"
            />

            <label for="create_description">
                {{ __("Description") }}
            </label>
            <Input
                id="create_description"
                v-model="storeForm.description"
                :name="'description'"
                type="text"
                :placeholder="__('Description')"
                class="mb-3.5"
            />

            <div class="grid sm:grid-cols-1 xl:grid-cols-2 gap-5 gap-y-0">
                <InputImage
                    v-model="storeForm.images"
                    :images="images"
                    text-classes="py-14"
                    @delete="closeCreateModal"
                />
                <InputFile
                    v-model="storeForm.files"
                    :files="files"
                    text-classes="py-14"
                    @delete="closeCreateModal"
                />
            </div>
        </div>

        <ModalSaveButtons
            :processing="storeForm.processing"
            :save-text="__('Add')"
            @cancel="closeCreateModal"
            @save="store"
        />
    </Modal>

    <Modal :show="showUpdateModal" @close="closeUpdateModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Update Task #") + updateForm.id }}
        </div>

        <div class="p-3.5">
            <label for="update_name">
                {{ __("Name") }}
                <span class="text-red-500"> *</span>
            </label>
            <Input
                id="update_name"
                v-model="updateForm.name"
                :name="'name'"
                type="text"
                :placeholder="__('Shortcode')"
                class="mb-3.5"
            />

            <label for="update_type">
                {{ __("Type") }}
                <span class="text-red-500"> *</span>
            </label>
            <Select
                id="update_type"
                v-model="updateForm.type"
                :name="'type'"
                :options="WorkOrderTaskType"
                :placeholder="__('Type')"
                class="w-full mb-3.5"
            />

            <label for="update_status">
                {{ __("Status") }}
                <span class="text-red-500"> *</span>
            </label>
            <Select
                id="update_status"
                v-model="updateForm.status"
                :name="'status'"
                :options="WorkOrderTaskStatus"
                :placeholder="__('Status')"
                class="w-full mb-3.5"
            />

            <label for="update_estimated_price">
                {{ __("Estimated Price") }}
                <span class="text-red-500"> *</span>
            </label>
            <Input
                id="update_estimated_price"
                v-model="updateForm.estimated_price"
                :name="'estimated_price'"
                type="text"
                class="mb-3.5"
                :placeholder="__('Estimated Price')"
                @blur="formatPriceOnBlur(updateForm, 'estimated_price')"
                @focus="formatPriceOnFocus(updateForm, 'estimated_price')"
            />

            <label for="update_actual_price">
                {{ __("Actual Price") }}
                <span
                    v-if="updateForm.status == WorkOrderTaskStatus.Completed"
                    class="text-red-500"
                >
                    *</span
                >
            </label>
            <Input
                id="update_actual_price"
                v-model="updateForm.actual_price"
                :name="'actual_price'"
                type="text"
                class="mb-3.5"
                :placeholder="__('Actual Price')"
                @blur="formatPriceOnBlur(updateForm, 'actual_price')"
                @focus="formatPriceOnFocus(updateForm, 'actual_price')"
            />

            <label for="update_planned_date">
                {{ __("Planned Date") }}
            </label>
            <DatePicker
                id="update_planned_date"
                v-model="updateForm.planned_date"
                :name="'planned_date'"
                :enable-time-picker="false"
                class="mb-3.5"
                :placeholder="__('Planned Date')"
            />

            <label for="update_assigned_to_id">
                {{ __("Assigned To") }}
            </label>
            <Select
                id="update_assigned_to_id"
                v-model="updateForm.assigned_to_id"
                :name="'assigned_to_id'"
                :options="mainCompanyUsers"
                :placeholder="__('Assigned To')"
                class="w-full mb-3.5"
            />

            <label for="update_description">
                {{ __("Description") }}
            </label>
            <Input
                id="update_description"
                v-model="updateForm.description"
                :name="'description'"
                type="text"
                :placeholder="__('Description')"
                class="mb-3.5"
            />

            <div class="grid sm:grid-cols-1 xl:grid-cols-2 gap-5 gap-y-0">
                <InputImage
                    v-model="updateForm.images"
                    :images="updateForm.existing_images"
                    text-classes="py-14"
                    @delete="closeUpdateModal"
                />
                <InputFile
                    v-model="updateForm.files"
                    :files="updateForm.existing_files"
                    text-classes="py-14"
                    @delete="closeUpdateModal"
                />
            </div>
        </div>

        <ModalSaveButtons
            :processing="updateForm.processing"
            :save-text="__('Update')"
            @cancel="closeUpdateModal"
            @save="update"
        />
    </Modal>

    <Modal :show="showDeleteModal" @close="closeDeleteModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Delete task #") + deleteForm.id }} ?
        </div>

        <ModalSaveButtons
            :processing="deleteForm.processing"
            :save-text="__('Delete')"
            @cancel="closeDeleteModal"
            @save="handleDelete"
        />
    </Modal>

    <Modal
        max-width="lg"
        :show="showTaskInformationModal"
        @close="closeTaskInformationModal"
    >
        <div v-if="showModalTask">
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{ __("Task #") + showModalTask.id }}
            </div>

            <InformationRow :title="__('Name')">
                {{ showModalTask.name }}
            </InformationRow>

            <InformationRow :title="__('Type')">
                {{
                    replaceEnumUnderscores(
                        findEnumKeyByValue(
                            WorkOrderTaskType,
                            showModalTask.type
                        )
                    )
                }}
            </InformationRow>

            <InformationRow :title="__('Status')">
                {{
                    findEnumKeyByValue(
                        WorkOrderTaskStatus,
                        showModalTask.status
                    )
                }}
            </InformationRow>

            <InformationRow :title="__('Estimated Price')">
                {{ showModalTask.estimated_price }}
            </InformationRow>

            <InformationRow :title="__('Actual Price')">
                {{ showModalTask.actual_price }}
            </InformationRow>

            <InformationRow
                v-if="showModalTask.assigned_to_id"
                :title="__('Assigned To')"
            >
                {{
                    findKeyByValue(
                        mainCompanyUsers,
                        showModalTask.assigned_to_id
                    )
                }}
            </InformationRow>

            <InformationRow
                v-if="showModalTask.planned_date"
                :title="__('Planned Date')"
            >
                {{ dateTimeToLocaleString(showModalTask.planned_date) }}
            </InformationRow>

            <InformationRow
                v-if="showModalTask.description"
                :title="__('Description')"
            >
                {{ showModalTask.description }}
            </InformationRow>

            <div
                class="grid sm:grid-cols-1 xl:grid-cols-2 gap-5 gap-y-0 m-2 p-3.5"
            >
                <InputImage
                    :disabled="true"
                    :images="showModalTask.images"
                    text-classes="py-14"
                />
                <InputFile
                    :files="showModalTask.files"
                    :disabled="true"
                    text-classes="py-14"
                />
            </div>
        </div>
    </Modal>
</template>
