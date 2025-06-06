<script setup lang="ts">

withDefaults(
    defineProps<{
        processing: boolean;
        cancelText?: string;
        saveText?: string;
    }>(),
    {
        cancelText: "Cancel",
        saveText: "Save",
    }
);

const emit = defineEmits(['save', 'cancel']);
</script>

<template>
    <div class="col-span-2 flex justify-end gap-3 mt-2 pt-1 pb-3 px-4">
        <slot />

        <button
            :disabled="processing"
            :class="{
                'bg-[#F0F0F0] hover:opacity-80': !processing,
                'bg-gray-300 cursor-not-allowed': processing,
                'px-12 py-2 rounded  active:scale-95 transition': true
            }"
            @click="emit('cancel')"
        >
            {{ cancelText }}
        </button>

        <button
            :disabled="processing"
            :class="{
                'bg-[#00A793] text-white hover:opacity-80': !processing,
                'bg-gray-300 cursor-not-allowed': processing,
                'px-12 py-2 rounded active:scale-95 transition': true
            }"
            @click="emit('save')"
        >
            {{ saveText }}
        </button>
    </div>
</template>
