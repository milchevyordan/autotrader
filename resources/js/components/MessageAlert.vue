<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import { ref, watchEffect } from "vue";

import { clearFlashMessages, flashMessages } from "@/globals";
import { Flash } from "@/types";
import { isNotEmpty } from "@/utils";

const page = usePage();
const showAlert = ref(false);
const flash = ref<Flash>(); // Specify the type of flash as Flash | null

const closeAlert = () => {
    showAlert.value = false;
    clearFlashMessages();
};

watchEffect(() => {
    flash.value = Object.assign({}, page.props.flash, flashMessages.value);

    // Check if there's any message (error, warning, success) and set showAlert to true
    if (
        isNotEmpty(flash.value.errors) ||
        flash.value.error ||
        flash.value.warning ||
        flash.value.success
    ) {
        showAlert.value = true;

        setTimeout(() => {
            closeAlert();
        }, 9000);
    }
});
</script>

<template>
    <transition name="slide-fade">
        <div
            v-if="showAlert"
            class="fixed inset-0 z-50"
            role="alert"
            @click="closeAlert"
        >
            <div
                v-if="isNotEmpty(flash?.errors)"
                class="'text-red-800 bg-red-100 text-red-700 py-4 px-10 text-sm rounded-lg cursor-pointer text-center fixed top-6 left-1/2 transform -translate-x-1/2 z-[80]'"
            >
                <div
                    v-for="(error, index) in flash?.errors"
                    :key="index"
                >
                    {{ error[0] ?? error }}
                </div>
            </div>

            <div
                v-else-if="flash?.error"
                class="'text-red-800 bg-red-100 text-red-700 py-4 px-10 text-sm rounded-lg cursor-pointer text-center fixed top-6 left-1/2 transform -translate-x-1/2 z-[80]'"
            >
                {{ flash?.error }}
            </div>

            <div
                v-else-if="flash?.warning"
                class="'text-red-800 bg-yellow-100 text-yellow-700 py-4 px-10 text-sm rounded-lg cursor-pointer text-center fixed top-6 left-1/2 transform -translate-x-1/2 z-[80]'"
            >
                {{ flash?.warning }}
            </div>

            <div
                v-else-if="flash?.success"
                class="'text-red-800 bg-green-100 text-green-700 py-4 px-10 text-sm rounded-lg cursor-pointer text-center fixed top-6 left-1/2 transform -translate-x-1/2 z-[80]'"
            >
                {{ flash?.success }}
            </div>
        </div>
    </transition>
</template>
