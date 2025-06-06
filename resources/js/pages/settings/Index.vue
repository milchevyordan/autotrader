<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";

import Header from "@/components/Header.vue";
import Section from "@/components/html/Section.vue";
import Toggle from "@/components/html/Toggle.vue";
import Select from "@/components/Select.vue";
import VehicleDefaults from "@/components/settings/VehicleDefaults.vue";
import Table from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import { MailType } from "@/enums/MailType";
import { VehicleStatus } from "@/enums/VehicleStatus";
import IconEye from "@/icons/Eye.vue";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { CompanyPdfAssets, EmailTemplate, SettingForm } from "@/types";
import {
    dateTimeToLocaleString,
    findEnumKeyByValue,
    replaceEnumUnderscores,
} from "@/utils";

import UpdatePdfImages from "@/pages/settings/partials/UpdatePdfImages.vue";

const props = defineProps<{
    userCompanyId: number | null;
    setting: SettingForm;
    dataTable: DataTable<EmailTemplate>;
    companyPdfAssets: CompanyPdfAssets;
}>();
</script>

<template>
    <Head :title="__('Settings')" />

    <AppLayout>
        <Header :text="__('Settings')" />

        <div v-if="$can('view-any-email-template')" class="mt-4">
            <Table
                :data-table="dataTable"
                :per-page-options="[5, 10, 15, 20, 50]"
                :global-search="true"
                :row-click-link="
                    $can('edit-email-template')
                        ? '/email-templates/?id/edit'
                        : ''
                "
            >
                <template #additionalContent>
                    <div class="w-full flex gap-2">
                        {{ __("Email Templates") }}
                    </div>
                </template>

                <template #cell(mail_type)="{ value, item }">
                    <div class="flex gap-1.5">
                        {{
                            replaceEnumUnderscores(
                                findEnumKeyByValue(MailType, value)
                            )
                        }}
                    </div>
                </template>

                <template #cell(updated_at)="{ value, item }">
                    <div class="flex gap-1.5">
                        {{ dateTimeToLocaleString(value) }}
                    </div>
                </template>

                <template #cell(action)="{ value, item }">
                    <div class="flex gap-1.5">
                        <Link
                            v-if="$can('edit-email-template')"
                            class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                            :href="route('email-templates.edit', item.id)"
                        >
                            <IconPencilSquare
                                classes="w-4 h-4 text-[#909090]"
                            />
                        </Link>
                    </div>
                </template>
            </Table>
        </div>

        <div class="flex justify-center">
            <div class="w-full max-w-[1721px]">
                <div class="grid lg:grid-cols-2 gap-4 auto-rows-auto h-fit">
                    <div>
                        <Section classes="py-4 my-4">
                            <div class="font-semibold text-xl px-5">
                                {{ __("Select table") }}
                            </div>

                            <div class="grid grid-cols-2 mt-3 items-center">
                                <div class="pl-5 font-medium">
                                    {{ __("Table") }}
                                </div>
                                <div
                                    class="flex items-center justify-end gap-3 pr-5"
                                >
                                    <Select
                                        :name="''"
                                        :options="VehicleStatus"
                                        placeholder="Vehicle status"
                                        class="w-full mb-3.5 sm:mb-0"
                                    />
                                </div>
                            </div>
                        </Section>

                        <VehicleDefaults
                            v-if="userCompanyId"
                            :setting="setting"
                        />

                        <Section classes="pt-4 h-fit">
                            <div class="font-semibold text-xl px-5">
                                {{ __("Roles") }}
                            </div>

                            <div
                                class="grid grid-cols-2 mt-3 gap-2.5 items-center"
                            >
                                <div
                                    class="border-b border-[#E9E7E7] col-span-2"
                                />

                                <Toggle :label="__('All roles')">
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>

                                <Toggle :label="__('Administrator')">
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>

                                <Toggle :label="__('Management')">
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>

                                <Toggle :label="__('Back office')">
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>
                            </div>
                        </Section>

                        <Section v-if="$can('update-company-logo')">
                            <header>
                                <h2 class="text-lg font-medium text-gray-900">
                                    {{ __("Company Images") }}
                                </h2>
                            </header>

                            <UpdatePdfImages
                                :company-pdf-assets="companyPdfAssets"
                                class="w-full"
                            />
                        </Section>

                        <Section classes="pt-4 mt-4 h-fit">
                            <div class="font-semibold text-xl px-5">
                                {{ __("Modules") }}
                            </div>

                            <div
                                class="grid grid-cols-2 mt-3 gap-2.5 items-center"
                            >
                                <div class="pl-5 font-medium">
                                    {{ __("Select user") }}
                                </div>
                                <div
                                    class="flex items-center justify-end gap-3 pr-5"
                                >
                                    <Select
                                        :name="''"
                                        :options="VehicleStatus"
                                        placeholder="User"
                                        class="w-full"
                                    />
                                </div>

                                <Toggle :label="__('All modules')">
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>

                                <Toggle :label="__('Base CRM')">
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>

                                <Toggle :label="__('Vehicle Grid')">
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>

                                <Toggle :label="__('Purchase Order')">
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>

                                <Toggle :label="__('PO Approval')">
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>

                                <Toggle :label="__('Sales Order')">
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>

                                <Toggle :label="__('SO Approval')">
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>

                                <Toggle :label="__('Pre-order management')">
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>

                                <Toggle :label="__('Pre Order Approval')">
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>

                                <Toggle :label="__('Service Order Contract')">
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>

                                <Toggle :label="__('Service order management')">
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>

                                <Toggle
                                    :label="__('Transport order management')"
                                >
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>

                                <Toggle :label="__('Request work order')">
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>

                                <Toggle :label="__('Work order management')">
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>

                                <Toggle :label="__('Project Templates')">
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>

                                <Toggle :label="__('Invoicing')">
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>

                                <Toggle :label="__('Calculator')">
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>

                                <Toggle :label="__('Create Vehicle')">
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>

                                <Toggle
                                    :label="__('Create Vehicle Licenceplate')"
                                >
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>

                                <Toggle
                                    :label="
                                        __(
                                            'Create Vehicle Model / Type Autotelex'
                                        )
                                    "
                                >
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>

                                <Toggle
                                    :label="
                                        __(
                                            'Create Vehicle Model / Type VehicleFlow'
                                        )
                                    "
                                >
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>

                                <Toggle :label="__('Assign Roles & Right')">
                                    <IconEye
                                        solid="yes"
                                        classes="w-5 h-5 text-gray-300"
                                    />
                                </Toggle>
                            </div>
                        </Section>
                    </div>

                    <Section classes="pt-4 mt-4 h-fit">
                        <div class="font-semibold text-xl px-5">
                            {{ __("Table Columns") }}
                        </div>

                        <div class="grid grid-cols-2 mt-3 gap-2.5 items-center">
                            <div class="border-b border-[#E9E7E7] col-span-2" />

                            <Toggle label="All columns">
                                <IconEye
                                    solid="yes"
                                    classes="w-5 h-5 text-gray-300"
                                />
                            </Toggle>

                            <Toggle label="Make">
                                <IconEye
                                    solid="yes"
                                    classes="w-5 h-5 text-gray-300"
                                />
                            </Toggle>

                            <Toggle label="Models">
                                <IconEye
                                    solid="yes"
                                    classes="w-5 h-5 text-gray-300"
                                />
                            </Toggle>

                            <Toggle label="Variant">
                                <IconEye
                                    solid="yes"
                                    classes="w-5 h-5 text-gray-300"
                                />
                            </Toggle>

                            <Toggle label="Engine">
                                <IconEye
                                    solid="yes"
                                    classes="w-5 h-5 text-gray-300"
                                />
                            </Toggle>

                            <Toggle label="Type">
                                <IconEye
                                    solid="yes"
                                    classes="w-5 h-5 text-gray-300"
                                />
                            </Toggle>

                            <Toggle label="Body type">
                                <IconEye
                                    solid="yes"
                                    classes="w-5 h-5 text-gray-300"
                                />
                            </Toggle>

                            <Toggle label="Fuel type">
                                <IconEye
                                    solid="yes"
                                    classes="w-5 h-5 text-gray-300"
                                />
                            </Toggle>

                            <Toggle label="KW">
                                <IconEye
                                    solid="yes"
                                    classes="w-5 h-5 text-gray-300"
                                />
                            </Toggle>

                            <Toggle label="Kilometers">
                                <IconEye
                                    solid="yes"
                                    classes="w-5 h-5 text-gray-300"
                                />
                            </Toggle>

                            <Toggle label="Wheel drive">
                                <IconEye
                                    solid="yes"
                                    classes="w-5 h-5 text-gray-300"
                                />
                            </Toggle>

                            <Toggle label="Description">
                                <IconEye
                                    solid="yes"
                                    classes="w-5 h-5 text-gray-300"
                                />
                            </Toggle>

                            <Toggle label="Price">
                                <IconEye
                                    solid="yes"
                                    classes="w-5 h-5 text-gray-300"
                                />
                            </Toggle>

                            <Toggle label="WLTP">
                                <IconEye
                                    solid="yes"
                                    classes="w-5 h-5 text-gray-300"
                                />
                            </Toggle>

                            <Toggle label="NEDC">
                                <IconEye
                                    solid="yes"
                                    classes="w-5 h-5 text-gray-300"
                                />
                            </Toggle>

                            <Toggle label="HP">
                                <IconEye
                                    solid="yes"
                                    classes="w-5 h-5 text-gray-300"
                                />
                            </Toggle>
                        </div>
                    </Section>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
