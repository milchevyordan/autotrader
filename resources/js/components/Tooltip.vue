<script setup lang="ts">
import { ref } from "vue";

const showTooltip = ref(false);
</script>

<template>
    <div
        class="relative"
        @mouseenter="showTooltip = true"
        @mouseleave="showTooltip = false"
    >
        <button type="button">
            <slot />
        </button>

        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-show="showTooltip"
                class="whitespace-nowrap absolute z-[1000] overflow-x-hidden bg-white rounded shadow-xl border border-gray-200"
            >
                <slot name="content" />
            </div>
        </Transition>
    </div>
</template>
