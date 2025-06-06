<script setup lang="ts">
import { ref } from "vue";

import IconBottomArrowAccordion from "@/icons/BottomArrowAccordion.vue";

const props = withDefaults(
    defineProps<{
        bodyShown?: boolean;
        showArrow?: boolean;
    }>(),
    {
        bodyShown: true,
        showArrow: true,
    }
);

const bodyShown = ref(props.bodyShown);
</script>

<template>
    <div class="accordion-container relative">
        <slot name="head" class="pr-5" />

        <div
            v-if="showArrow"
            class="absolute top-0 left-[calc(100%-1.5rem)] text-slate-400 z-10 w-7 h-7 cursor-pointer flex items-center justify-center rounded-full bg-white shadow-lg transition-all hover:bg-slate-100"
        >
            <IconBottomArrowAccordion
                class="w-5 h-5 transition-all duration-500"
                :class="bodyShown ? 'mt-1' : 'rotate-180'"
                @click="bodyShown = !bodyShown"
            />
        </div>

        <div v-if="!bodyShown">
            <slot name="collapsedContent" />
        </div>

        <div v-if="bodyShown">
            <slot />
        </div>
    </div>
</template>
