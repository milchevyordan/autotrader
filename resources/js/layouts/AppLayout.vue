<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";

import MessageAlert from "@/components/MessageAlert.vue";
import MobileNav from "@/components/navigation/MobileNav.vue";
import NavBar from "@/components/navigation/NavBar.vue";

const pageProps = usePage();

const pageName =
    pageProps.url.replace("/", "").charAt(0).toUpperCase() +
    pageProps.url.slice(2);
</script>

<template>
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div class="w-28 fixed top-0 bottom-0 left-0 bg-white z-50">
            <NavBar />
        </div>

        <!-- Mobile navigation bar -->
        <MobileNav :title="pageName" />

        <!-- Main section -->
        <div class="flex-1 ml-28 bg-[#F5F5F5] overflow-auto">
            <div class="min-h-screen px-4 pb-[5em] pt-1">
                <MessageAlert />
                <div class="p-4 bg-[#F5F5F5]">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
