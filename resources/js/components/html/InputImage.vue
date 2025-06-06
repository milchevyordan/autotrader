<script setup lang="ts">
import { router, usePage } from "@inertiajs/vue3";
import { component as ViewerComponent } from "v-viewer";
import { ModelRef, ref } from "vue";
import "viewerjs/dist/viewer.css";
import { computed, watch } from "vue";

import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Modal from "@/components/Modal.vue";
import { addFlashErrorMessage, setFlashMessages } from "@/globals";
import DownloadIcon from "@/icons/Download.vue";
import Eye from "@/icons/Eye.vue";
import IconTrash from "@/icons/Trash.vue";
import { __ } from "@/translations";
import { DatabaseImage, ImagePreview } from "@/types";
import { downLoadMultipleFiles } from "@/utils";

const emit = defineEmits(["change", "delete"]);

const props = withDefaults(
    defineProps<{
        images: DatabaseImage[];
        dir?: string;
        text?: string;
        textClasses?: string;
        for?: string;
        id?: string;
        singleImage?: boolean;
        disabled?: boolean;
        deleteDisabled?: boolean;
        minImageSizeMb?: number;
        maxImageSizeMb?: number;
        mimeTypes?: string[];
    }>(),
    {
        text: "Upload Image",
        textClasses: "py-1.5",
        for: "dropzone-image",
        id: "dropzone-image",
        singleImage: false,
        disabled: false,
        deleteDisabled: false,
    }
);

const minImageSizeMb = computed(
    () =>
        props.minImageSizeMb ??
        usePage().props.config.validation.image.minImageSizeMb
);

const maxImageSizeMb = computed(
    () =>
        props.maxImageSizeMb ??
        usePage().props.config.validation.image.maxImageSizeMb
);

const mimeTypes = computed(
    () => props.mimeTypes ?? usePage().props.config.validation.image.mimeTypes
);

const dir = props.dir ?? "/storage";

const inputElement = ref<HTMLInputElement | null>(null);

const previewList = ref<ImagePreview[]>(
    props.images.map((image) => ({
        ...image,
        name: image.original_name,
    }))
);

const model = defineModel<File[]>() as ModelRef<File[], string>;

let $viewer: Viewer;

const inited = (viewer: Viewer) => {
    $viewer = viewer;
};

const show = (index: number) => {
    $viewer.view(index);
    $viewer.show();
};

watch(
    () => props.images,
    () => {
        previewList.value = props.images.map((image) => ({
            ...image,
            name: image.original_name,
        }));

        if ($viewer) {
            $viewer.update();
        }
    }
);

const update = (): void => {
    if (props.disabled) {
        return;
    }

    const images: FileList | null = inputElement.value?.files!;

    if (props.disabled) {
        return;
    }

    if (!images) {
        setFlashMessages({
            error: "Something went wrong, no images attached",
        });

        return;
    }

    if (props.singleImage && previewList.value.length > 0) {
        setFlashMessages({
            error: "Only one image allowed.",
        });

        return;
    }

    for (let i = 0; i < images.length; i++) {
        const image: File = images[i];
        const imageSizeInMb = image.size / (1024 * 1024);

        if (!mimeTypes.value.includes(image.type)) {
            const allowedTypes = mimeTypes.value.join(", ");

            addFlashErrorMessage(
                `Image ${image.name} type not allowed. Allowed types ${allowedTypes}`
            );
            continue;
        }

        if (imageSizeInMb < minImageSizeMb.value) {
            addFlashErrorMessage(
                `Image ${image.name} size is less than minimum required size off ${minImageSizeMb.value}MB.`
            );
            continue;
        }

        if (imageSizeInMb > maxImageSizeMb.value) {
            addFlashErrorMessage(
                `Image  ${image.name} exceeds the maximum allowed size of ${maxImageSizeMb.value}MB.`
            );
            continue;
        }

        // Generate the preview of the files
        const reader = new FileReader();
        reader.readAsDataURL(image);
        reader.onload = () => {
            const preview = {
                name: image.name,
                imageData: reader.result as string,
                size: image.size,
                type: image.type,
            } as ImagePreview;

            previewList.value.push(preview);
        };

        model.value.push(image);
    }

    emit("change", model.value);
};

const removeFromDB = async (path: string) => {
    const encodedPath = encodeURIComponent(path);

    router.delete(route("images.destroy", encodedPath as string), {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            throw new Error("Failed to delete the file");
        },
    });
};

const isDatabaseImageFile = (
    previewOrImage: ImagePreview | DatabaseImage
): previewOrImage is DatabaseImage => {
    return (
        typeof previewOrImage === "object" &&
        previewOrImage !== null &&
        "path" in previewOrImage
    );
};

const imageSources = computed(() =>
    previewList.value.map((previewOrImage) => {
        return isDatabaseImageFile(previewOrImage)
            ? `${dir}/${(previewOrImage as DatabaseImage).path}`
            : (previewOrImage as ImagePreview).imageData;
    })
);

const removeImage = async () => {
    if (deleteIndex.value === null!) {
        return;
    }

    const imageToRemove = props.images[deleteIndex.value];

    if (isDatabaseImageFile(imageToRemove)) {
        try {
            await removeFromDB(imageToRemove.path);
        } catch (error) {
            setFlashMessages({
                error: error as string,
            });

            return;
        }
    }

    const databaseImagesLength = previewList.value.filter((image) =>
        isDatabaseImageFile(image)
    ).length;

    const modelDeleteIndex = deleteIndex.value - databaseImagesLength;
    model.value.splice(modelDeleteIndex, 1);
    previewList.value.splice(deleteIndex.value, 1);

    setFlashMessages({
        success: "Image successfully removed",
    });

    closeDeleteModal();
    emit("delete", previewList.value.length);
};

const handlePreviewDragStart = (event: DragEvent, index: number): void => {
    if (props.disabled || model.value.length) {
        event.preventDefault();
        return;
    }

    event.dataTransfer?.setData("text/plain", index.toString());
};

const handlePreviewDragOver = (event: DragEvent): void => {
    event.preventDefault();
};

const handlePreviewDrop = (event: DragEvent, droppedIndex: number): void => {
    if (props.disabled || model.value.length) {
        event.preventDefault();
        return;
    }

    event.preventDefault();

    const dragIndex = event.dataTransfer?.getData("text/plain");

    if (
        dragIndex === null ||
        dragIndex === undefined ||
        +dragIndex === droppedIndex
    ) {
        return;
    }

    const dragIdx = parseInt(dragIndex, 10);
    const temp = previewList.value[dragIdx];
    previewList.value[dragIdx] = previewList.value[droppedIndex];
    previewList.value[droppedIndex] = temp;

    const orderArray = previewList.value.map((file: ImagePreview) => file.id);

    router.put(route("images.order"), { orderArray }, { preserveScroll: true });
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

const showButton = ref<Record<number, boolean>>([]);

const showDeleteModal = ref(false);
const deleteIndex = ref<number>(null!);

const openDeleteModal = (index: number) => {
    deleteIndex.value = index;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deleteIndex.value = null!;
};
</script>

<template>
    <div>
        <div class="flex items-center justify-center w-full mb-4">
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
                    {{ isDragging ? "Drop your images here" : text }}
                </p>

                <input
                    v-if="!disabled"
                    :id="id"
                    ref="inputElement"
                    type="file"
                    accept="image/*"
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
            @click="downLoadMultipleFiles(previewList, text)"
        >
            <span class="text-xs font-semibold text-gray-700">
                {{ __("Download all") }}
            </span>
            <DownloadIcon
                class="w-4 h-4 text-gray-500 group-hover:text-gray-700 transition"
            />
        </div>

        <ViewerComponent
            v-if="previewList.length"
            :images="previewList"
            @inited="inited"
        >
            <div
                class="flex flex-wrap items-center justify-center gap-4 py-4 max-h-36 overflow-y-auto"
            >
                <div
                    v-for="(preview, index) in previewList"
                    :key="index"
                    @mouseenter="showButton[index] = true"
                    @mouseleave="showButton[index] = false"
                >
                    <div
                        class="relative"
                        :draggable="!disabled && !model.length"
                        @dragstart="
                            (event) => handlePreviewDragStart(event, index)
                        "
                        @dragover="handlePreviewDragOver"
                        @drop="(event) => handlePreviewDrop(event, index)"
                    >
                        <div class="relative">
                            <img
                                :src="imageSources[index]"
                                class="w-20 h-20 rounded-md object-cover shadow sm:group-hover:opacity-80"
                                alt="img"
                            />

                            <button
                                v-if="
                                    (!disabled || !deleteDisabled) &&
                                    $can('delete-image')
                                "
                                v-show="showButton[index]"
                                class="absolute top-0 right-0 -mt-2 mr-2 text-red-400 w-6 h-6 bg-red-100 rounded-full border border-red-200 flex items-center justify-center hover:bg-red-200 transition duration-300"
                                @click="openDeleteModal(index)"
                            >
                                <IconTrash classes="w-5 h-5" />
                            </button>
                        </div>

                        <button
                            v-show="showButton[index]"
                            class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-blue-400 w-9 h-9 bg-blue-100 rounded-full border border-blue-200 flex items-center justify-center hover:bg-blue-200 transition duration-300"
                            :title="__('Look Images')"
                            @click="show(index)"
                        >
                            <Eye classes="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </div>
        </ViewerComponent>
    </div>

    <Modal :show="showDeleteModal" @close="closeDeleteModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Delete the selected image ?") }}
        </div>

        <ModalSaveButtons
            :processing="false"
            :save-text="__('Delete')"
            @cancel="closeDeleteModal"
            @save="removeImage"
        />
    </Modal>
</template>
