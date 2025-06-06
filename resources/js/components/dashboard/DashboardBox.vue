<script setup lang="ts">
import { Link } from "@inertiajs/vue3";

import IconChevronRight from "@/icons/ChevronRight.vue";
import { DashboardBox } from "@/types";

defineProps<{
    box: DashboardBox;
}>();
</script>

<template>
    <Link
        :href="
            route('dashboard-boxes.index', {
                slug: box.slug,
            })
        "
        class="relative flex flex-wrap p-4 bg-white rounded-lg shadow-md hover:bg-gray-50 transition active:scale-[.97]"
    >
        <!-- Icon on the left in the middle -->
        <div class="flex items-center justify-center gap-4 w-full rounded-md">
            <div class="col right-col p-4">
                <div class="">
                    <div
                        class="text-[#676666] md:text-xl font-bold text-center mb-2"
                    >
                        {{ box.name }}
                    </div>

                    <div
                        class="text-[#676666] rounded border p-1 bg-slate-100 text-center"
                    >
                        {{ __("Total") }} - {{ box.boxBuilder.count }}
                    </div>

                    <div
                        v-if="box.boxBuilder.redFlagsCount"
                        class="ref-flags-hr-container"
                    >
                        <hr class="my-2" />

                        <div
                            class="bg-red-100 text-red-800 text-sm font-medium me-2 px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300"
                        >
                            {{ __("Red flags") }} -
                            {{ box.boxBuilder.redFlagsCount }}
                        </div>
                    </div>

                    <hr class="my-2" />

                    <!-- Description -->
                    <div class="text-[#676666] text-sm md:text-base">
                        {{ box.description }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Chevron icon -->
        <IconChevronRight
            classes="w-5 h-5 absolute right-2 top-1/2 transform -translate-y-1/2 text-[#008FE3]"
        />
    </Link>
</template>
