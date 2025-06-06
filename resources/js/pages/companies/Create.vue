<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";

import GeneralInformation from "@/components/companies/GeneralInformation.vue";
import AddressRemarks from "@/components/html/AddressRemarks.vue";
import Header from "@/components/Header.vue";
import Addresses from "@/components/html/Addresses.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { companyFormRules } from "@/rules/company-form-rules";
import { CompanyForm } from "@/types";
import { withFlash } from "@/utils";
import { validate } from "@/validations";

const form = useForm<CompanyForm>({
    id: null!,
    default_currency: null!,
    country: null!,
    vat_percentage: null!,
    name: null!,
    postal_code: null!,
    city: null!,
    address: null!,
    vat_number: null!,
    email: null!,
    phone: null!,
    iban: null!,
    swift_or_bic: null!,
    bank_name: null!,
    kvk_number: null!,
    billing_remarks: null!,
    logistics_times: null!,
    pdf_footer_text: null!,
    logistics_remarks: null!,
    addresses: [],
});

const save = async (only?: Array<string>) => {
    validate(form, companyFormRules);

    return new Promise<void>((resolve, reject) => {
        form.post(route("companies.store"), {
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
    <Head :title="__('Base Company')" />

    <AppLayout>
        <Header :text="__('Base Company')" />

        <div class="flex justify-center">
            <div class="w-full max-w-[1721px]">
                <GeneralInformation :form="form" />

                <AddressRemarks :form="form" />

                <Addresses :addresses="form.addresses" />
            </div>
        </div>

        <ResetSaveButtons
            :processing="form.processing"
            @reset="form.reset()"
            @save="save"
        />
    </AppLayout>
</template>
