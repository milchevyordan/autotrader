<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";

import Accordion from "@/components/Accordion.vue";
import GeneralInformation from "@/components/crm/companies/GeneralInformation.vue";
import Header from "@/components/Header.vue";
import Addresses from "@/components/html/Addresses.vue";
import AddressRemarks from "@/components/html/AddressRemarks.vue";
import DatePicker from "@/components/html/DatePicker.vue";
import InputFile from "@/components/html/InputFile.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import Section from "@/components/html/Section.vue";
import { Multiselect } from "@/data-table/types";
import { CompanyType } from "@/enums/CompanyType";
import AppLayout from "@/layouts/AppLayout.vue";
import { crmCompanyFormRules } from "@/rules/crm-company-form-rules";
import { CompanyGroup, CrmCompanyForm } from "@/types";
import { withFlash } from "@/utils";
import { validate } from "@/validations";

defineProps<{
    companyGroups: Multiselect<CompanyGroup>;
}>();

const form = useForm<CrmCompanyForm>({
    id: null!,
    type: CompanyType.General,
    company_group_id: null!,
    main_user_id: null!,
    billing_contact_id: null!,
    logistics_contact_id: null!,
    default_currency: null!,
    country: null!,
    name: null!,
    number: null!,
    number_addition: null!,
    postal_code: null!,
    city: null!,
    address: null!,
    province: null!,
    street: null!,
    address_number: null!,
    address_number_addition: null!,
    vat_number: null!,
    purchase_type: null!,
    locale: null!,
    company_number_accounting_system: null!,
    debtor_number_accounting_system: null!,
    creditor_number_accounting_system: null!,
    website: null!,
    email: null!,
    phone: null!,
    kvk_number: null!,
    iban: null!,
    swift_or_bic: null!,
    bank_name: null!,
    billing_remarks: null!,
    logistics_times: null!,
    logistics_remarks: null!,
    addresses: [],
    kvk_expiry_date: null!,
    vat_expiry_date: null!,
    kvk_files: [],
    vat_files: [],
});

const save = async (only?: Array<string>) => {
    validate(form, crmCompanyFormRules);

    return new Promise<void>((resolve, reject) => {
        form.post(route("crm.companies.store"), {
            preserveScroll: true,
            preserveState: true,
            only: withFlash(only),
            onSuccess: () => {
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
                    :form="form"
                    :company-groups="companyGroups"
                />

                <AddressRemarks :form="form" />

                <Addresses :addresses="form.addresses" />

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
                                        :files="[]"
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
                                        :files="[]"
                                        :text="__('VAT File')"
                                        :single-file="true"
                                        text-classes="py-14"
                                    />
                                </div>
                            </div>
                        </div>
                    </Accordion>
                </Section>
            </div>
        </div>

        <ResetSaveButtons
            :processing="form.processing"
            @reset="form.reset()"
            @save="save"
        />
    </AppLayout>
</template>
