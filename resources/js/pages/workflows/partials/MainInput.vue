<script setup lang="ts">

defineProps<{
    text?: string | { [key: string]: number } | null;
}>();

</script>

<template>
    <div class="border border-[#F2F2F7] flex items-center h-fit rounded-md">
        <div class="text-[#C7C7CC] bg-[#F2F2F7] mr-2 p-1 element-center">
            <slot />
        </div>
        <div class="text-[#6D6D73]">
            {{ text }}
        </div>
    </div>
</template>
