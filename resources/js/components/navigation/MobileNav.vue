<script setup lang="ts">
import { Link } from "@inertiajs/vue3";

import MobileNavLink from "@/components/link/MobileNavLink.vue";
import IconDocument from "@/icons/Document.vue";
import FLagNl from "@/icons/flags/Netherlands.vue";
import FlagUk from "@/icons/flags/UnitedKingdom.vue";
import IconHome from "@/icons/Home.vue";
import IconLogout from "@/icons/Logout.vue";
import NotificationBell from "@/icons/NotificationBell.vue";
import IconPlus from "@/icons/Plus.vue";
import { startsWith } from "@/utils";

defineProps<{
    title: string;
}>();
</script>

<template>
    <div
        class="fixed md:hidden top-0 left-0 z-50 w-full py-3.5 bg-white border-b border-gray-200"
    >
        <div class="w-full flex items-center justify-between px-4">
            <div class="text-lg font-semibold text-slate-800">
                {{ title }}
            </div>

            <div class="flex items-center gap-x-2.5">
                <div class="text-sm font-thin text-slate-800">
                    {{ $page.props.auth.user.last_name }}
                </div>

                <a
                    :href="
                        route('language', {
                            locale: $page.props.lang === 'nl' ? 'en' : 'nl',
                        })
                    "
                >
                    <FLagNl
                        v-if="$page.props.lang === 'en'"
                        class="rounded-full w-8 h-8"
                    />

                    <FlagUk v-else class="rounded-full w-8 h-8" />
                </a>

                <Link :href="route('profile.edit')">
                    <img
                        src="/images/pexels-photo-774909.webp"
                        class="w-8 h-8 object-cover rounded-full"
                        alt="profile image"
                    />
                </Link>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="bg-[#008FE3] text-white rounded-full w-8 h-8 element-center p-1.5 shadow"
                >
                    <IconLogout classes="w-6 h-6" />
                </Link>
            </div>
        </div>
    </div>

    <div
        class="fixed md:hidden z-50 w-full h-14 max-w-lg -translate-x-1/2 bg-white border border-gray-200 rounded-full bottom-4 left-1/2"
    >
        <div class="grid h-full max-w-lg grid-cols-7 mx-auto">
            <MobileNavLink
                :href="route('dashboard')"
                :active="
                    route().current('dashboard') ||
                    startsWith(route().current(), [
                        'purchase-orders',
                        'pre-orders',
                        'sales-orders',
                        'service-orders',
                        'service-levels',
                        'items',
                    ])
                "
            >
                <IconHome solid="yes" />
            </MobileNavLink>

            <MobileNavLink
                v-if="$can('create-vehicle')"
                :href="route('vehicles.create')"
            >
                <IconPlus solid="yes" />
            </MobileNavLink>

            <MobileNavLink
                v-if="$can('view-any-vehicle')"
                :href="route('vehicles.index')"
                :active="route().current('vehicles.index')"
            >
                <IconDocument solid="yes" />
            </MobileNavLink>

            <MobileNavLink
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
                        'Finance Manager',
                    ])
                "
                :href="route('notifications.index')"
                :active="route().current('notifications.index')"
            >
                <NotificationBell />
            </MobileNavLink>
        </div>
    </div>
</template>
