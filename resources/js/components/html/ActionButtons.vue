<script setup lang="ts">
const emit = defineEmits(["cancel", "save", "delete", "open-delete-modal"]);

withDefaults(
    defineProps<{
        processing: boolean
        cancelText?: string;
        saveText?: string;
        deleteText?: string;
        setSaving?: boolean;
        hasDeleteOption?: boolean;
    }>(),
    {
        cancelText: "Cancel",
        saveText: "Save",
        deleteText: "Delete",
        setSaving: false,
        hasDeleteOption: false,
    }
);
</script>

<template>
    <div class="container flex wrap">
        <div class="col-span-2 ml-auto flex gap-3 mt-2 pt-1 pb-3 px-4">
            <button
                :disabled="processing"
                :class="{
                    'bg-gray-400 text-white': !processing,
                    'bg-gray-100 cursor-not-allowed': processing,
                    'px-12 py-2 rounded hover:opacity-80 active:scale-95 transition': true,
                }"
                @click="emit('cancel')"
            >
                {{ cancelText }}
            </button>

            <button
                :disabled="processing"
                :class="{
                    'bg-[#00A793] text-white': !processing,
                    'bg-gray-300 cursor-not-allowed': processing,
                    'px-12 py-2 rounded hover:opacity-80 active:scale-95 transition': true,
                }"
                @click="emit('save')"
            >
                {{ saveText }}
            </button>

            <button
                :disabled="!hasDeleteOption"
                :class="{
                    'bg-red-600 text-white hover:bg-red-700': hasDeleteOption,
                    'bg-gray-100 cursor-not-allowed': !hasDeleteOption,
                    'px-12 py-2 rounded hover:opacity-80 active:scale-95 transition': true,
                }"
                @click="emit('open-delete-modal')"
            >
                {{ deleteText }}
            </button>
        </div>
    </div>
</template>
