<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";

import GeneralInformation from "@/components/companies/GeneralInformation.vue";
import Header from "@/components/Header.vue";
import Addresses from "@/components/html/Addresses.vue";
import AddressRemarks from "@/components/html/AddressRemarks.vue";
import ChangeLogs from "@/components/html/ChangeLogs.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import { Multiselect } from "@/data-table/types";
import AppLayout from "@/layouts/AppLayout.vue";
import { companyFormRules } from "@/rules/company-form-rules";
import { Company, CompanyForm, User } from "@/types";
import { withFlash } from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    company: Company;
    users: Multiselect<User>;
}>();

const form = useForm<CompanyForm>({
    _method: "put",
    id: props.company.id,
    default_currency: props.company.default_currency,
    country: props.company.country,
    billing_contact_id: props.company.billing_contact_id,
    logistics_contact_id: props.company.logistics_contact_id,
    vat_percentage: props.company.vat_percentage,
    name: props.company.name,
    postal_code: props.company.postal_code,
    city: props.company.city,
    address: props.company.address,
    vat_number: props.company.vat_number,
    email: props.company.email,
    phone: props.company.phone,
    iban: props.company.iban,
    swift_or_bic: props.company.swift_or_bic,
    bank_name: props.company.bank_name,
    kvk_number: props.company.kvk_number,
    billing_remarks: props.company.billing_remarks,
    logistics_times: props.company.logistics_times,
    pdf_footer_text: props.company.pdf_footer_text,
    logistics_remarks: props.company.logistics_remarks,
    addresses: props.company.addresses ?? [],
});

const save = async (only?: Array<string>) => {
    validate(form, companyFormRules);

    return new Promise<void>((resolve, reject) => {
        form.post(route("companies.update", props.company.id as number), {
            preserveScroll: true,
            preserveState: true,
            forceFormData: true, // preserves all form data
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
    <Head :title="__('Base Company')" />

    <AppLayout>
        <Header :text="__('Base Company')" />

        <div class="flex justify-center">
            <div class="w-full max-w-[1721px]">
                <GeneralInformation :form="form" />

                <AddressRemarks
                    :form="form"
                    :users="users"
                    :show-main-users-select="true"
                />

                <Addresses :addresses="form.addresses" :form="form" />

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
