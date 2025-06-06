<script setup lang="ts">
import { useForm, router } from "@inertiajs/vue3";
import { ref } from "vue";

import Modal from "@/components/Modal.vue";
import { ExportType } from "../enums/ExportType";
import { downLoadAndDeleteFile } from "@/utils";
import { DataTable } from "../types";

const props = defineProps<{
    dataTable: DataTable<any>;
    propName: string;
    exportTypes: ExportType[];
}>();

const showModal = ref<boolean>(false);

const closeModal = () => {
    showModal.value = false;
};

const handleExport = async (exportType: string) => {
    router.reload({
        data: {
            export_type: exportType,
        },
        only: [props.propName],
        onSuccess: () => {
            const url = new URL(window.location.href);
            url.searchParams.delete("export_type");
            window.history.replaceState({}, "", url.toString());

            downLoadAndDeleteFile(
                props.dataTable.export.encryptedExportPath,
                "export"
            );

            closeModal();
        },
    });
};
</script>

<template>
    <div>
        <button
            class="w-full border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50 my-2 sm:my-0 cursor-pointer"
            @click="showModal = true"
        >
            {{ __("Exports") }}
        </button>

        <Modal :show="showModal" @close="closeModal">
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{ __("Exports") }}
            </div>

            <div class="exportTypes">
                <div
                    v-for="(exportType, index) in exportTypes"
                    :key="index"
                    class="exportType"
                >
                    <span
                        @click="handleExport(exportType)"
                        class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out cursor-pointer"
                    >
                        {{ exportType }}
                    </span>
                </div>
            </div>

            <div class="col-span-2 flex justify-end gap-3 mt-2 pt-1 pb-3 px-4">
                <button
                    class="bg-[#F0F0F0] px-12 py-2 rounded hover:opacity-80 active:scale-95 transition"
                    @click="closeModal"
                >
                    {{ __("Cancel") }}
                </button>
            </div>
        </Modal>
    </div>
</template>
