<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";

import Accordion from "@/components/Accordion.vue";
import GeneralInformation from "@/components/crm/companies/GeneralInformation.vue";
import Header from "@/components/Header.vue";
import Addresses from "@/components/html/Addresses.vue";
import AddressRemarks from "@/components/html/AddressRemarks.vue";
import ChangeLogs from "@/components/html/ChangeLogs.vue";
import DatePicker from "@/components/html/DatePicker.vue";
import InputFile from "@/components/html/InputFile.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import Section from "@/components/html/Section.vue";
import Table from "@/data-table/Table.vue";
import { DataTable, Multiselect } from "@/data-table/types";
import IconDocument from "@/icons/Document.vue";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { crmCompanyFormRules } from "@/rules/crm-company-form-rules";
import {
    CompanyGroup,
    CrmCompany,
    CrmCompanyForm,
    DatabaseFile,
    Role,
    User,
} from "@/types";
import { Company } from "@/types";
import { dateTimeToLocaleString, withFlash } from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    company: CrmCompany;
    companyGroups: Multiselect<CompanyGroup>;
    users: Multiselect<User>;
    dataTable: DataTable<User>;
    companies: Multiselect<Company>;
    crmRoles: Multiselect<Role>;
    files: {
        kvk_files: DatabaseFile[];
        vat_files: DatabaseFile[];
    };
}>();

const form = useForm<CrmCompanyForm>({
    _method: "put",
    id: props.company.id,
    type: props.company.type,
    company_group_id: props.company.company_group_id,
    main_user_id: props.company.main_user_id,
    billing_contact_id: props.company.billing_contact_id,
    logistics_contact_id: props.company.logistics_contact_id,
    default_currency: props.company.default_currency,
    country: props.company.country,
    name: props.company.name,
    number: props.company.number,
    number_addition: props.company.number_addition,
    postal_code: props.company.postal_code,
    city: props.company.city,
    address: props.company.address,
    province: props.company.province,
    street: props.company.street,
    address_number: props.company.address_number,
    address_number_addition: props.company.address_number_addition,
    vat_number: props.company.vat_number,
    purchase_type: props.company.purchase_type,
    locale: props.company.locale,
    company_number_accounting_system:
        props.company.company_number_accounting_system,
    debtor_number_accounting_system:
        props.company.debtor_number_accounting_system,
    creditor_number_accounting_system:
        props.company.creditor_number_accounting_system,
    website: props.company.website,
    email: props.company.email,
    phone: props.company.phone,
    kvk_number: props.company.kvk_number,
    iban: props.company.iban,
    swift_or_bic: props.company.swift_or_bic,
    bank_name: props.company.bank_name,
    billing_remarks: props.company.billing_remarks,
    logistics_times: props.company.logistics_times,
    logistics_remarks: props.company.logistics_remarks,
    addresses: props.company.addresses ?? [],
    kvk_expiry_date: props.company.kvk_expiry_date,
    vat_expiry_date: props.company.vat_expiry_date,
    kvk_files: [],
    vat_files: [],
});

const save = async (only?: Array<string>) => {
    validate(form, crmCompanyFormRules);

    return new Promise<void>((resolve, reject) => {
        form.post(route("crm.companies.update", props.company.id as number), {
            preserveScroll: true,
            preserveState: true,
            forceFormData: true, // preserves all form data
            only: withFlash(only),
            onSuccess: () => {
                form.reset("kvk_files", "vat_files");
                resolve();
            },
            onError: () => {
                reject(new Error("Error, during update"));
            },
        });
    });
};
</script>

<template>
    <Head :title="__('Company')" />

    <AppLayout>
        <Header :text="__('Company')" />

        <div class="flex justify-center">
            <div class="w-full max-w-[1721px]">
                <GeneralInformation
                    :company="company"
                    :form="form"
                    :company-groups="companyGroups"
                    :users="users"
                    :companies="companies"
                    :roles="crmRoles"
                />

                <AddressRemarks
                    :form="form"
                    :users="users"
                    :show-main-users-select="true"
                />

                <Addresses :addresses="form.addresses" :form="form" />

                <!-- .documents -->
                <Section classes="p-4 pb-0 mt-4 relative">
                    <Accordion>
                        <template #head>
                            <div class="font-semibold text-xl sm:text-2xl mb-4">
                                {{ __("Documents") }}
                            </div>
                        </template>

                        <div
                            class="grid lg:grid-cols-1 xl:grid-cols-2 gap-4 mt-4 lg:mt-10 pb-4"
                        >
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 xl:border-r border-[#E9E7E7] xl:pr-8 sm:gap-y-2 items-center"
                            >
                                <label for="kvk_expiry_date">
                                    {{ __("KVK expiry date") }}
                                </label>

                                <DatePicker
                                    v-model="form.kvk_expiry_date"
                                    name="kvk_expiry_date"
                                    :enable-time-picker="false"
                                    class="mb-3.5 sm:mb-0"
                                />

                                <div class="col-span-2">
                                    <InputFile
                                        id="internal-files"
                                        v-model="form.kvk_files"
                                        :files="files.kvk_files"
                                        :text="__('KVK File')"
                                        :single-file="true"
                                        text-classes="py-14"
                                    />
                                </div>
                            </div>

                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 xl:pl-4 sm:gap-y-2 items-center"
                            >
                                <label for="vat_expiry_date">
                                    {{ __("VAT expiry date") }}
                                </label>

                                <DatePicker
                                    v-model="form.vat_expiry_date"
                                    name="vat_expiry_date"
                                    :enable-time-picker="false"
                                    class="mb-3.5 sm:mb-0"
                                />

                                <div class="col-span-2">
                                    <InputFile
                                        id="vat-files"
                                        v-model="form.vat_files"
                                        :files="files.vat_files"
                                        :text="__('VAT File')"
                                        :single-file="true"
                                        text-classes="py-14"
                                    />
                                </div>
                            </div>
                        </div>
                    </Accordion>
                </Section>
                <!-- / .documents -->

                <Section class="p-4 mt-4 relative">
                    <Accordion>
                        <template #head>
                            <div class="font-semibold text-xl sm:text-2xl mb-4">
                                {{ __("Users") }}
                            </div>
                        </template>

                        <Table
                            :data-table="dataTable"
                            :per-page-options="[5, 10, 15, 20, 50]"
                            :global-search="true"
                            :advanced-filters="false"
                        >
                            <template #cell(company.name)="{ value, item }">
                                {{ item.company?.name }}
                            </template>

                            <template #cell(roles.name)="{ value, item }">
                                <div
                                    v-for="(role, index) in item.roles"
                                    :key="index"
                                >
                                    {{ role.name }}
                                </div>
                            </template>

                            <template #cell(created_at)="{ value, item }">
                                <div class="flex gap-1.5">
                                    {{ dateTimeToLocaleString(value) }}
                                </div>
                            </template>
                            <template #cell(action)="{ value, item }">
                                <div class="flex gap-1.5">
                                    <Link
                                        v-if="$can('edit-crm-user')"
                                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                        :title="__('Edit crm user')"
                                        :href="route('crm.users.edit', item.id)"
                                    >
                                        <IconPencilSquare
                                            classes="w-4 h-4 text-[#909090]"
                                        />
                                    </Link>

                                    <Link
                                        v-if="$can('view-crm-user')"
                                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                        :title="__('View crm user profile')"
                                        :href="route('crm.users.show', item.id)"
                                    >
                                        <IconDocument
                                            classes="w-4 h-4 text-[#909090]"
                                        />
                                    </Link>
                                </div>
                            </template>
                        </Table>
                    </Accordion>
                </Section>

                <ChangeLogs :change-logs="company.change_logs" />
            </div>
        </div>

        <ResetSaveButtons
            :processing="form.processing"
            @reset="form.reset()"
            @save="save"
        />
    </AppLayout>
</template>
