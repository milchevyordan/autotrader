<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";

import DashboardBox from "@/components/dashboard/DashboardBox.vue";
import Header from "@/components/Header.vue";
import ResetIcon from "@/icons/Reset.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import {
    DashboardBox as DashboardBoxInterface,
    ReCacheForm,
    User,
} from "@/types";

defineProps<{
    user: User;
    dashboardBoxes?: DashboardBoxInterface[];
}>();

const reCacheForm = useForm<ReCacheForm>({});

const refresh = () => {
    reCacheForm.post(route("dashboard.refresh"), {
        preserveScroll: true,
        preserveState: true,
        forceFormData: true,
    });
};
</script>

<template>
    <Head :title="__('Dashboard')" />

    <AppLayout>
        <Header
            :text="
                __('Welcome') +
                ': ' +
                user.full_name +
                ' / ' +
                $page.props.auth.user.roles
            "
        />

        <div class="flex justify-center">
            <div class="w-full max-w-[1721px]">
                <div
                    class="flex flex-wrap gap-x-0.5 gap-y-6 justify-between mb-8"
                >
                    <DashboardBox
                        v-for="(box, index) in dashboardBoxes"
                        :key="index"
                        class="w-full md:w-1/2 lg:w-1/5"
                        :box="box"
                    />
                </div>

                <div
                    v-if="
                        $hasRole([
                            'Super Manager',
                            'Management',
                            'Company Purchaser',
                            'Manager SalesPurchasing',
                            'Back Office Employee',
                            'Back Office Manager',
                            'Logistics Employee',
                            'Finance Employee',
                        ])
                    "
                    class="re-cache float-right"
                >
                    <button
                        :disabled="reCacheForm.processing"
                        :class="{
                            'bg-[#E50000] hover:bg-red-500':
                                !reCacheForm.processing,
                            'bg-gray-400 cursor-not-allowed':
                                reCacheForm.processing,
                            'rounded p-2 px-16 text-white active:scale-95 transition absolute bottom-0.5 right-2': true,
                        }"
                        @click="refresh"
                    >
                        <ResetIcon />
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
