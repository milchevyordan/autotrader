<script setup lang="ts">
import { Head } from "@inertiajs/vue3";

import CompanyWorkflow from "@/components/configurations/CompanyWorkflow.vue";
import AppLayout from "@/layouts/AppLayout.vue";

defineProps<{
    // user: User;
}>();
</script>

<template>
    <Head title="Configurations" />

    <AppLayout>
        <CompanyWorkflow />
    </AppLayout>
</template>
