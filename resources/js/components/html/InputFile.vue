<script setup lang="ts">
import { router, usePage } from "@inertiajs/vue3";
import { computed, ModelRef, ref, watch } from "vue";

import FileIcon from "@/components/FileIcon.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Modal from "@/components/Modal.vue";
import PreviewPdfModal from "@/components/PreviewPdfModal.vue";
import { addFlashErrorMessage, setFlashMessages } from "@/globals";
import Download from "@/icons/Download.vue";
import IconEye from "@/icons/Eye.vue";
import IconTrash from "@/icons/Trash.vue";
import { __ } from "@/translations";
import { DatabaseFile } from "@/types";
import { downLoadMultipleFiles } from "@/utils";
import DownloadIcon from "@/icons/Download.vue";

const emit = defineEmits(["change", "delete"]);

export interface FilePreview {
    id?: number;
    type?: string;
    name: string;
    size: number;
}

const model = defineModel<File[]>() as ModelRef<File[], string>;
const inputElement = ref<HTMLInputElement | null>(null);

const props = withDefaults(
    defineProps<{
        files: DatabaseFile[];
        text?: string;
        textClasses?: string;
        for?: string;
        id?: string;
        singleFile?: boolean;
        disabled?: boolean;
        deleteDisabled?: boolean;
        minFileSizeMb?: number;
        maxFileSizeMb?: number;
        mimeTypes?: string[];
        isUploadable?: boolean;
    }>(),
    {
        text: "Upload File",
        textClasses: "py-1.5",
        for: "dropzone-file",
        id: "dropzone-file",
        singleFile: false,
        disabled: false,
        deleteDisabled: false,
        isUploadable: true,
    }
);

const mimeTypes = computed(
    () => props.mimeTypes ?? usePage().props.config.validation.file.mimeTypes
);

const minFileSizeMb = computed(
    () =>
        props.minFileSizeMb ??
        usePage().props.config.validation.file.minFileSizeMb
);

const maxFileSizeMb = computed(
    () =>
        props.maxFileSizeMb ??
        usePage().props.config.validation.file.maxFileSizeMb
);

const previewList = ref<FilePreview[]>(
    props.files.map((file) => ({
        ...file,
        name: file instanceof File ? file.name : file.original_name,
    }))
);

watch(
    () => props.files,
    () => {
        previewList.value = props.files.map((file) => ({
            ...file,
            name: file.original_name,
        }));
    }
);

const isDatabaseFile = (
    previewOrFile: FilePreview | DatabaseFile
): previewOrFile is DatabaseFile => {
    return (
        typeof previewOrFile === "object" &&
        Object.keys(previewOrFile).includes("path")
    );
};

const update = (): void => {
    if (props.disabled) {
        return;
    }

    const files: FileList | null = inputElement.value?.files!;

    if (props.singleFile && previewList.value.length > 0) {
        setFlashMessages({
            error: __("Only one file allowed."),
        });

        return;
    }

    if (!files) {
        setFlashMessages({
            error: __("Something went wrong, no files attached"),
        });

        return;
    }

    for (let i = 0; i < files.length; i++) {
        const file: File = files[i];
        const fileSizeMb = file.size / (1024 * 1024);

        if (!mimeTypes.value.includes(file.type)) {
            const allowedTypes = mimeTypes.value.join(", ");
            addFlashErrorMessage(
                `File type not allowed. Allowed types ${allowedTypes}`
            );
            continue;
        }

        if (fileSizeMb > maxFileSizeMb.value) {
            addFlashErrorMessage(
                `File "${file.name}" exceeds the maximum allowed size of ${maxFileSizeMb.value}MB.`
            );
            continue;
        }

        if (fileSizeMb < minFileSizeMb.value) {
            addFlashErrorMessage(
                `File "${file.name}" size is less than minimum required size of ${minFileSizeMb.value}MB.`
            );

            continue;
        }

        // Generate the Preview
        const filePreview = {
            name: file.name,
            size: file.size,
            type: file.type,
        } as FilePreview;
        previewList.value.push(filePreview);

        model.value.push(file);
    }

    emit("change", model.value);
};

const removeFromDB = async (path: string) => {
    const encodedPath = encodeURIComponent(path);

    return new Promise<void>((resolve, reject) => {
        router.delete(route("files.destroy", encodedPath as string), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                resolve();
            },
            onError: (err: any) => {
                reject();
                throw new Error("Failed to delete the file");
            },
        });
    });
};

const removeFile = async () => {
    const index = deleteIndex.value;

    if (index === null || index === undefined) return;

    const fileToRemove = props.files[index];

    try {
        if (isDatabaseFile(fileToRemove)) {
            await removeFromDB(fileToRemove.path);

            setFlashMessages({ success: __("File successfully removed") });
            closeDeleteModal();
            emit("delete");

            return;
        }
    } catch (error) {
        setFlashMessages({ error: __("Error occurred during the deletion") });
        return;
    }

    const databaseFilesCount = previewList.value.filter(isDatabaseFile).length;
    const modelIndex = index - databaseFilesCount;

    model.value.splice(modelIndex, 1);
    previewList.value.splice(index, 1);

    setFlashMessages({ success: __("File successfully removed") });
    closeDeleteModal();
    emit("delete");
};

const previewPdfUrl = ref<string>(null!);

const handleFilePreview = (file: DatabaseFile) => {
    previewPdfUrl.value = file.path;

    openPreviewModal();
};

const downLoadFile = (index: number) => {
    const fileToDownload = props.files[index];

    if (isDatabaseFile(fileToDownload)) {
        const encodedPath = encodeURIComponent(fileToDownload.path);
        location.replace(route("files.download", encodedPath as string));

        setFlashMessages({
            success: __("File downloading..."),
        });
    } else {
        setFlashMessages({
            error: __("Failed to download the file"),
        });
    }
};

const handleDragStart = (event: DragEvent, index: number) => {
    if (props.disabled || model.value.length) {
        event.preventDefault();
        return;
    }

    event.dataTransfer?.setData("text/plain", index.toString());
};

const handleDragOver = (event: any) => {
    event.preventDefault();
};

const handleDrop = (event: DragEvent, droppedIndex: number) => {
    if (props.disabled || model.value.length) {
        event.preventDefault();
        return;
    }

    event.preventDefault();

    const dragIndexStr = event.dataTransfer?.getData("text/plain");
    if (dragIndexStr === null || dragIndexStr === undefined) {
        return;
    }

    const dragIndex = Number(dragIndexStr);
    if (isNaN(dragIndex) || dragIndex === droppedIndex) {
        return;
    }

    const temp = previewList.value[dragIndex];
    previewList.value[dragIndex] = previewList.value[droppedIndex];
    previewList.value[droppedIndex] = temp;

    const orderArray = previewList.value.map((file) => file.id);
    router.put(route("files.order"), { orderArray }, { preserveScroll: true });
};

const showButton = ref<Record<number, boolean>>([]);

const showDeleteModal = ref<boolean>(false);
const deleteIndex = ref<number>(null!);

const openDeleteModal = (index: number) => {
    deleteIndex.value = index;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deleteIndex.value = null!;
};

const showPreviewModal = ref<boolean>(false);

const openPreviewModal = () => {
    showPreviewModal.value = true;
};

const closePreviewModal = () => {
    showPreviewModal.value = false;
};

const isDragging = ref(false);

const handleInputDragOver = (event: DragEvent): void => {
    isDragging.value = true;
};

const handleInputDragLeave = (event: DragEvent): void => {
    isDragging.value = false;
};

const handleInputDrop = (event: DragEvent): void => {
    isDragging.value = false;
};
</script>

<template>
    <div>
        <div
            v-if="isUploadable"
            class="flex items-center justify-center w-full mb-4"
        >
            <label
                :class="`w-full border ${
                    disabled
                        ? ' bg-slate-200 opacity-50'
                        : isDragging
                        ? 'border-[#008FE3] bg-blue-100 cursor-pointer'
                        : 'border-[#008FE3] cursor-pointer bg-white hover:bg-blue-50'
                } border-dashed rounded-lg relative transition-colors duration-300`"
            >
                <p
                    :class="`text-sm text-center ${
                        isDragging
                            ? 'bg-black text-white opacity-40'
                            : 'text-[#008FE3]'
                    } ${textClasses}`"
                >
                    {{ isDragging ? "Drop your files here" : text }}
                </p>

                <input
                    v-if="!disabled"
                    :id="id"
                    ref="inputElement"
                    type="file"
                    accept=".pdf, .doc, .docx, .xls, .xlsx, .txt"
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                    multiple
                    @change="update"
                    @drop="handleInputDrop"
                    @dragover="handleInputDragOver"
                    @dragleave="handleInputDragLeave"
                />
            </label>
        </div>

        <div
            v-if="previewList.length"
            class="download-all border-b-2 py-3 flex items-center justify-center gap-2 cursor-pointer hover:bg-gray-50 active:scale-[.98] transition"
            @click="downLoadMultipleFiles(files, text)"
        >
            <span class="text-xs font-semibold text-gray-700">
                {{ __("Download all") }}
            </span>
            <DownloadIcon
                class="w-4 h-4 text-gray-500 group-hover:text-gray-700 transition"
            />
        </div>

        <div
            v-if="previewList?.length"
            class="flex flex-wrap items-center justify-center mt-4 gap-4 py-4 max-h-24 overflow-y-auto"
        >
            <div
                v-for="(file, index) in previewList"
                :key="index"
                @mouseenter="showButton[index] = true"
                @mouseleave="showButton[index] = false"
            >
                <div
                    class="relative"
                    :draggable="!disabled"
                    @dragstart="(event) => handleDragStart(event, index)"
                    @dragover="handleDragOver"
                    @drop="(event) => handleDrop(event, index)"
                >
                    <div class="relative">
                        <FileIcon :file="file.name" />

                        <div
                            v-if="file.name"
                            :title="file.name"
                            class="text-xs text-slate-700 max-w-20 overflow-hidden whitespace-nowrap truncate"
                            :class="
                                showButton[index] ? 'font-bold' : 'font-light'
                            "
                        >
                            {{ file.name }}
                        </div>

                        <button
                            v-if="
                                isUploadable &&
                                (!disabled || !deleteDisabled) &&
                                $can('delete-file')
                            "
                            v-show="showButton[index]"
                            :title="__('Delete File')"
                            class="absolute top-0 right-0 -mt-2 mr-2 text-red-400 w-5 h-5 bg-red-100 rounded-full border border-red-200 flex items-center justify-center hover:bg-red-200 transition duration-300"
                            @click="openDeleteModal(index)"
                        >
                            <IconTrash classes="w-3 h-3" />
                        </button>

                        <div
                            v-if="isDatabaseFile(file)"
                            v-show="showButton[index]"
                            class="absolute left-1/2 transform -translate-x-1/2 top-4 flex space-x-1"
                        >
                            <button
                                :title="__('Download File')"
                                class="text-blue-400 w-5 h-5 bg-blue-100 rounded-full border border-blue-200 flex items-center justify-center hover:bg-blue-200 transition duration-300"
                                @click="downLoadFile(index)"
                            >
                                <Download classes="w-3 h-3" />
                            </button>

                            <button
                                v-if="
                                    ['pdf'].includes(
                                        file?.name?.split('.').pop() ?? ''
                                    )
                                "
                                :title="__('Look File')"
                                class="text-blue-400 w-5 h-5 bg-blue-100 rounded-full border border-blue-200 flex items-center justify-center hover:bg-blue-200 transition duration-300"
                                @click="handleFilePreview(file)"
                            >
                                <IconEye classes="w-3 h-3" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <Modal
        v-if="isUploadable"
        :show="showDeleteModal"
        @close="closeDeleteModal"
    >
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Delete the selected file ?") }}
        </div>

        <ModalSaveButtons
            :processing="false"
            :save-text="__('Delete')"
            @cancel="closeDeleteModal"
            @save="removeFile"
        />
    </Modal>

    <PreviewPdfModal
        :show-preview-modal="showPreviewModal"
        :preview-pdf-url="previewPdfUrl"
        @close="closePreviewModal"
    />
</template>
