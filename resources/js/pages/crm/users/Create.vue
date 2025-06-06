<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";

import Accordion from "@/components/Accordion.vue";
import GeneralInformation from "@/components/crm/users/GeneralInformation.vue";
import Header from "@/components/Header.vue";
import DatePicker from "@/components/html/DatePicker.vue";
import InputFile from "@/components/html/InputFile.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import Section from "@/components/html/Section.vue";
import { Multiselect } from "@/data-table/types";
import AppLayout from "@/layouts/AppLayout.vue";
import { crmUserFormRules } from "@/rules/crm-user-form-rules";
import { Company, CompanyGroup, Role, UserForm } from "@/types";
import { withFlash } from "@/utils";
import { validate } from "@/validations";

defineProps<{
    companyGroups: Multiselect<CompanyGroup>;
    companies: Multiselect<Company>;
    crmRoles: Multiselect<Role>;
}>();

const form = useForm<UserForm>({
    id: null!,
    prefix: null!,
    first_name: null!,
    last_name: null!,
    company_id: null!,
    email: null!,
    mobile: null!,
    other_phone: null!,
    gender: null!,
    language: null!,
    id_card_expiry_date: null!,
    id_card_files: [],
    roles: null!,
});

const save = async (only?: Array<string>) => {
    validate(form, crmUserFormRules);

    return new Promise<void>((resolve, reject) => {
        form.post(route("crm.users.store"), {
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
    <Head :title="__('Crm User')" />

    <AppLayout>
        <Header :text="__('Crm User')" />

        <div class="flex justify-center">
            <div class="w-full max-w-[1721px]">
                <GeneralInformation
                    :form="form"
                    :companies="companies"
                    :crm-roles="crmRoles"
                />

                <Section>
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
                                class="grid grid-cols-1 sm:grid-cols-2 xl:pr-8 sm:gap-y-2 items-center"
                            >
                                <label for="id_card_expiry_date">
                                    {{ __("ID card expiry date") }}
                                </label>

                                <DatePicker
                                    v-model="form.id_card_expiry_date"
                                    name="id_card_expiry_date"
                                    :enable-time-picker="false"
                                    class="mb-3.5 sm:mb-0"
                                />

                                <div class="col-span-2">
                                    <InputFile
                                        id="internal-files"
                                        v-model="form.id_card_files"
                                        :files="[]"
                                        :text="__('ID Card')"
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
