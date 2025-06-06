<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";

import NavLink from "@/components/link/NavLink.vue";
import Tooltip from "@/components/Tooltip.vue";
import IconCog from "@/icons/Cog.vue";
import IconComputer from "@/icons/Computer.vue";
import DocumentChecked from "@/icons/DocumentChecked.vue";
import FLagNl from "@/icons/flags/Netherlands.vue";
import FlagUk from "@/icons/flags/UnitedKingdom.vue";
import IconHome from "@/icons/Home.vue";
import IconLogout from "@/icons/Logout.vue";
import MailIcon from "@/icons/MailIcon.vue";
import NotificationBell from "@/icons/NotificationBell.vue";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import IconPlus from "@/icons/Plus.vue";
import IconTruck from "@/icons/Truck.vue";
import UserRoleIcon from "@/icons/UserRole.vue";
import IconVehicles from "@/icons/Vehicles.vue";
import { startsWith } from "@/utils";
</script>

<template>
    <div
        class="hidden md:flex flex-col justify-between h-screen py-3 bg-[#F5F5F5] rounded-lg m-2"
    >
        <!-- Top -->
        <div>
            <div class="mt-2 2xl:mt-7 px-1.5">
                <Link :href="route('dashboard')">
                    <img
                        :src="$page.props.auth.company.logo"
                        class="hover:opacity-60 transition"
                        alt="app logo"
                    />
                </Link>
            </div>

            <div
                class="mt-2 2xl:mt-7 text-center text-sm font-thin text-slate-800"
            >
                {{ __("Navigation") }}
            </div>

            <div class="element-center mt-2 2xl:mt-6">
                <Tooltip>
                    <div
                        :class="
                            route().current('dashboard') ||
                            startsWith(route().current(), [
                                'dashboard-boxes',
                                'purchase-orders',
                                'pre-orders',
                                'sales-orders',
                                'service-orders',
                                'work-orders',
                                'transport-orders',
                                'service-levels',
                                'items',
                                'documents',
                                'quotes',
                                'quote-invitations',
                                'vehicles.overview',
                                'vehicles.following',
                                'work-filters',
                            ])
                                ? 'p-2 rounded-md bg-[#008FE3] text-white transition duration-150 ease-in-out'
                                : 'p-2 rounded-md text-slate-600 hover:bg-[#008FE3] hover:text-white transition duration-150 ease-in-out'
                        "
                    >
                        <IconHome />
                    </div>

                    <template #content>
                        <div
                            class="flex flex-col text-center font-semibold text-sm text-slate-800"
                        >
                            <Link
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100':
                                        route().current('dashboard') ||
                                        route().current('dashboard-boxes.*'),
                                }"
                                :href="route('dashboard')"
                            >
                                {{ __("Dashboard") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-vehicle')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100':
                                        route().current('vehicles.index'),
                                }"
                                :href="route('vehicles.index')"
                            >
                                {{ __("Vehicle Grid") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-overview')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100':
                                        route().current('vehicles.overview'),
                                }"
                                :href="route('vehicles.overview')"
                            >
                                {{ __("Overview") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-following')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100':
                                        route().current('vehicles.following'),
                                }"
                                :href="route('vehicles.following')"
                            >
                                {{ __("Following") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-pre-order')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100':
                                        route().current('pre-orders.*'),
                                }"
                                :href="route('pre-orders.index')"
                            >
                                {{ __("Pre Orders") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-purchase-order')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100':
                                        route().current('purchase-orders.*'),
                                }"
                                :href="route('purchase-orders.index')"
                            >
                                {{ __("Purchase Orders") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-sales-order')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100':
                                        route().current('sales-orders.*'),
                                }"
                                :href="route('sales-orders.index')"
                            >
                                {{ __("Sales orders") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-service-order')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100':
                                        route().current('service-orders.*'),
                                }"
                                :href="route('service-orders.index')"
                            >
                                {{ __("Service orders") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-work-order')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100':
                                        route().current('work-orders.*'),
                                }"
                                :href="route('work-orders.index')"
                            >
                                {{ __("Work Orders") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-transport-order')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100':
                                        route().current('transport-orders.*'),
                                }"
                                :href="route('transport-orders.index')"
                            >
                                {{ __("Transport Orders") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-service-level')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100':
                                        route().current('service-levels.*'),
                                }"
                                :href="route('service-levels.index')"
                            >
                                {{ __("Service level") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-item')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100':
                                        route().current('items.index'),
                                }"
                                :href="route('items.index')"
                            >
                                {{ __("Items") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-document')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100':
                                        route().current('documents.*'),
                                }"
                                :href="route('documents.index')"
                            >
                                {{ __("Invoices") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-quote')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100': route().current('quotes.*'),
                                }"
                                :href="route('quotes.index')"
                            >
                                {{ __("Quotes") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-quote-invitation')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100': route().current(
                                        'quote-invitations.*'
                                    ),
                                }"
                                :href="route('quote-invitations.index')"
                            >
                                {{ __("Quote Invitations") }}
                            </Link>
                        </div>
                    </template>
                </Tooltip>
            </div>

            <div v-if="$can('view-any-role')" class="element-center mt-3">
                <NavLink
                    :href="route('roles.index')"
                    :active="route().current('roles.*')"
                >
                    <UserRoleIcon />
                </NavLink>
            </div>

            <div v-if="$can('create-vehicle')" class="element-center mt-3">
                <NavLink
                    :href="route('vehicles.create')"
                    :active="route().current('vehicles.create')"
                >
                    <IconPlus />
                </NavLink>
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
                        'Finance Manager',
                    ])
                "
                class="element-center mt-3"
            >
                <Tooltip>
                    <div
                        :class="
                            startsWith(route().current(), [
                                'vehicles.index',
                                'vehicles.edit',
                                'vehicles.management',
                                'pre-order-vehicles',
                                'service-vehicles',
                                'workflows',
                            ])
                                ? 'p-2 rounded-md bg-[#008FE3] text-white transition duration-150 ease-in-out'
                                : 'p-2 rounded-md text-slate-600 hover:bg-[#008FE3] hover:text-white transition duration-150 ease-in-out'
                        "
                    >
                        <IconVehicles />
                    </div>
                    <template #content>
                        <div
                            class="flex flex-col text-center font-semibold text-sm text-slate-800"
                        >
                            <Link
                                v-if="$can('view-any-management')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100': route().current(
                                        'vehicles.management'
                                    ),
                                }"
                                :href="route('vehicles.management', 'overview')"
                            >
                                {{ __("Management") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-vehicle')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100': startsWith(
                                        route().current(),
                                        ['vehicles.index', 'vehicles.edit']
                                    ),
                                }"
                                :href="route('vehicles.index')"
                            >
                                {{ __("Vehicles") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-pre-order-vehicle')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100': route().current(
                                        'pre-order-vehicles.*'
                                    ),
                                }"
                                :href="route('pre-order-vehicles.index')"
                            >
                                {{ __("Pre-Order") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-service-vehicle')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100':
                                        route().current('service-vehicles.*'),
                                }"
                                :href="route('service-vehicles.index')"
                            >
                                {{ __("Service Vehicles") }}
                            </Link>
                        </div>
                    </template>
                </Tooltip>
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
                        'Finance Manager',
                    ])
                "
                class="element-center mt-3"
            >
                <Tooltip>
                    <div
                        :class="
                            startsWith(route().current(), [
                                'makes.index',
                                'vehicle-models.index',
                                'vehicle-groups.index',
                                'engines.index',
                                'variants.index',
                            ])
                                ? 'p-2 rounded-md bg-[#008FE3] text-white transition duration-150 ease-in-out'
                                : 'p-2 rounded-md text-slate-600 hover:bg-[#008FE3] hover:text-white transition duration-150 ease-in-out'
                        "
                    >
                        <IconTruck />
                    </div>

                    <template #content>
                        <div
                            class="flex flex-col text-center font-semibold text-sm text-slate-800"
                        >
                            <Link
                                v-if="$can('view-any-make')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100':
                                        route().current('makes.index'),
                                }"
                                :href="route('makes.index')"
                            >
                                {{ __("Makes") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-vehicle-model')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100': route().current(
                                        'vehicle-models.index'
                                    ),
                                }"
                                :href="route('vehicle-models.index')"
                            >
                                {{ __("Models") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-vehicle-group')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100': route().current(
                                        'vehicle-groups.index'
                                    ),
                                }"
                                :href="route('vehicle-groups.index')"
                            >
                                {{ __("Groups") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-engine')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100':
                                        route().current('engines.index'),
                                }"
                                :href="route('engines.index')"
                            >
                                {{ __("Engines") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-variant')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100':
                                        route().current('variants.index'),
                                }"
                                :href="route('variants.index')"
                            >
                                {{ __("Variants") }}
                            </Link>
                        </div>
                    </template>
                </Tooltip>
            </div>

            <div v-if="$can('create-vehicles')" class="element-center mt-3">
                <NavLink href="">
                    <IconPencilSquare />
                </NavLink>
            </div>

            <div
                v-if="$hasRole(['Root', 'Company Administrator'])"
                class="element-center mt-3"
            >
                <Tooltip>
                    <div
                        :class="
                            startsWith(route().current(), [
                                'companies',
                                'users',
                            ])
                                ? 'p-2 rounded-md bg-[#008FE3] text-white transition duration-150 ease-in-out'
                                : 'p-2 rounded-md text-slate-600 hover:bg-[#008FE3] hover:text-white transition duration-150 ease-in-out'
                        "
                    >
                        <IconComputer />
                    </div>

                    <template #content>
                        <div
                            class="flex flex-col text-center font-semibold text-sm text-slate-800"
                        >
                            <Link
                                v-if="
                                    $can('view-any-base-company') &&
                                    $hasRole('Root')
                                "
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100':
                                        route().current('companies.*'),
                                }"
                                :href="route('companies.index')"
                            >
                                {{ __("Companies") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-base-user')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100': route().current('users.*'),
                                }"
                                :href="route('users.index')"
                            >
                                {{ __("Users") }}
                            </Link>
                        </div>
                    </template>
                </Tooltip>
            </div>

            <!-- CRM  -->
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
                        'Finance Manager',
                    ])
                "
                class="element-center mt-3"
            >
                <Tooltip>
                    <div
                        :class="
                            route().current('crm.*')
                                ? 'p-2 rounded-md bg-[#008FE3] text-white transition duration-150 ease-in-out'
                                : 'p-2 rounded-md text-slate-600 hover:bg-[#008FE3] hover:text-white transition duration-150 ease-in-out'
                        "
                    >
                        <IconComputer />
                    </div>

                    <template #content>
                        <div
                            class="flex flex-col text-center font-semibold text-sm text-slate-800"
                        >
                            <Link
                                v-if="$can('view-any-crm-company')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100':
                                        route().current('crm.companies.*'),
                                }"
                                :href="route('crm.companies.index')"
                            >
                                {{ __("Companies") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-crm-company-group')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100': route().current(
                                        'crm.company-groups.*'
                                    ),
                                }"
                                :href="route('crm.company-groups.index')"
                            >
                                {{ __("Company Groups") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-crm-user')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100':
                                        route().current('crm.users.*'),
                                }"
                                :href="route('crm.users.index')"
                            >
                                {{ __("Users") }}
                            </Link>

                            <Link
                                v-if="$can('view-any-user-group')"
                                class="hover:opacity-70 transition py-1 px-2"
                                :class="{
                                    'bg-gray-100':
                                        route().current('crm.user-groups.*'),
                                }"
                                :href="route('crm.user-groups.index')"
                            >
                                {{ __("User Groups") }}
                            </Link>
                        </div>
                    </template>
                </Tooltip>
            </div>

            <div v-if="$can('view-any-mail')" class="element-center mt-3">
                <NavLink
                    :href="route('mails.index')"
                    :active="route().current('mails.index')"
                >
                    <MailIcon
                        classes="w-6 h-6 flex-shrink-0"
                        color="currentColor"
                    />
                </NavLink>
            </div>

            <div v-if="$can('view-any-ownership')" class="element-center mt-3">
                <NavLink
                    :href="route('ownerships.index')"
                    :active="route().current('ownerships.*')"
                    :notifications-count="
                        usePage().props.auth.user.pendingOwnershipsCount
                    "
                >
                    <DocumentChecked
                        classes="w-6 h-6 flex-shrink-0"
                        color="currentColor"
                        stroke="4"
                    />
                </NavLink>
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
                        'Finance Manager',
                    ])
                "
                class="element-center mt-3"
            >
                <NavLink
                    :href="route('notifications.index')"
                    :active="route().current('notifications.index')"
                    :notifications-count="
                        usePage().props.auth.user.notificationsCount
                    "
                >
                    <NotificationBell />
                </NavLink>
            </div>

            <div v-if="$can('edit-setting')" class="element-center mt-3">
                <NavLink
                    :href="route('setting.index')"
                    :active="
                        startsWith(route().current(), [
                            'setting.',
                            'email-templates.',
                        ])
                    "
                >
                    <IconCog />
                </NavLink>
            </div>

            <div
                v-if="$can('view-any-configuration')"
                class="element-center mt-3"
            >
                <NavLink
                    :href="route('configurations.index')"
                    :active="route().current('configurations.index')"
                >
                    <IconCog />
                </NavLink>
            </div>
        </div>
        <!-- Top END -->

        <!-- Bottom -->
        <div class="element-center-col my-3 shrink-0">
            <a
                :href="
                    route('language', {
                        locale: $page.props.lang === 'nl' ? 'en' : 'nl',
                    })
                "
                class="w-9 h-9 mb-1 rounded-md cursor-pointer hover:opacity-80 transition ease-in-out active:scale-95"
            >
                <FLagNl v-if="$page.props.lang === 'en'" class="rounded" />
                <FlagUk v-else class="rounded" />
            </a>

            <Link :href="route('profile.edit')" class="pb-2">
                <img
                    :src="
                        $page.props.auth.user.image_path
                            ? '/storage/' + $page.props.auth.user.image_path
                            : '/images/pexels-photo-774909.webp'
                    "
                    class="w-10 h-10 object-cover rounded-md cursor-pointer hover:opacity-80 transition ease-in-out active:scale-95"
                    alt="profile image"
                />
            </Link>

            <div
                class="text-center text-sm font-thin text-slate-800 border-b border-gray-200 pb-1 2xl:pb-4 truncate w-full"
            >
                <div>
                    {{ $page.props.auth.user.last_name }}
                </div>
            </div>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="p-2 mt-2 rounded-md bg-[#008FE3] hover:opacity-80 text-white transition ease-in-out active:scale-95"
            >
                <IconLogout />
            </Link>
        </div>
        <!-- Bottom END -->
    </div>
</template>
