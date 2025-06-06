<script setup lang="ts">

withDefaults(
    defineProps<{
        label: string;
        modelValue?: boolean;
        hasDivider?: boolean;
    }>(),
    {
        modelValue: false,
        hasDivider: true
    }
);

</script>
<template>
    <div class="pl-5">
        {{ label }}
    </div>
    <div class="flex items-center justify-end gap-3 pr-5">
        <slot />
        <label class="relative inline-flex items-center cursor-pointer">
            <input
                type="checkbox"
                :checked="modelValue"
                class="sr-only peer"
                @change="$emit('update:modelValue', ($event.target as HTMLInputElement).checked)"
            >
            <div
                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"
            />
        </label>
    </div>
    <div
        v-if="hasDivider"
        class="border-b border-[#E9E7E7] col-span-2"
    />
</template>
