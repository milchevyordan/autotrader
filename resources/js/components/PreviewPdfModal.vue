<script setup lang="ts">
import Modal from "@/components/Modal.vue";

defineProps<{
    showPreviewModal: boolean;
    previewPdfUrl?: string;
}>();
</script>

<template>
    <Modal :show="showPreviewModal" max-width="7xl" @close="$emit('close')">
        <div class="container p-10 h-[90vh]">
            <iframe
                v-if="previewPdfUrl"
                :src="`/storage/${previewPdfUrl}`"
                width="100%"
                height="100%"
            />
        </div>
    </Modal>
</template>
