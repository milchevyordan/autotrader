<script setup lang="ts">
import { InertiaForm, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

import Accordion from "@/components/Accordion.vue";
import Input from "@/components/html/Input.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Modal from "@/components/Modal.vue";
import Select from "@/components/Select.vue";
import CreateUserGeneralInformationComponent from "@/components/users/GeneralInformation.vue";
import { Multiselect } from "@/data-table/types";
import { Country } from "@/enums/Country";
import { Currency } from "@/enums/Currency";
import { Locale } from "@/enums/Locale";
import { NationalEuOrWorldType } from "@/enums/NationalEuOrWorldType";
import { crmUserFormRules } from "@/rules/crm-user-form-rules";
import {
    Company,
    CompanyForm,
    CompanyGroup,
    Role,
    User,
    UserForm,
} from "@/types";
import {
    CrmCompanyType,
    findEnumKeyByValue,
    replaceEnumUnderscores,
} from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    form: InertiaForm<CompanyForm>;
    companyGroups: Multiselect<CompanyGroup>;
    company?: Company;
    companies?: Multiselect<Company>;
    roles?: Multiselect<Role>;
    users?: Multiselect<User>;
}>();

const showCreateUserModal = ref<boolean>(false);

const createUserForm = useForm<UserForm>({
    id: null!,
    prefix: null!,
    first_name: null!,
    last_name: null!,
    company_id: props.company?.id,
    email: null!,
    mobile: null!,
    other_phone: null!,
    gender: null!,
    language: null!,
    roles: null!,
});

const handleCreateUser = () => {
    validate(createUserForm, crmUserFormRules);

    createUserForm.post(route("crm.companies.store-user"), {
        preserveScroll: true,
        preserveState: true,
        only: ["users", "company"],
        onSuccess: () => {
            closeCreateUserModal();
            createUserForm.reset();
            props.form.main_user_id = props.company?.main_user_id;
        },
        onError: () => {},
    });
};

const openCreateUserModal = () => {
    showCreateUserModal.value = true;
};

const closeCreateUserModal = () => {
    showCreateUserModal.value = false;
};
</script>

<template>
    <div
        class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
    >
        <Accordion>
            <template #head>
                <div class="font-semibold text-xl sm:text-2xl mb-4">
                    {{ __("General Information") }}: {{ form.id }}
                </div>
            </template>

            <template #collapsedContent>
                <div
                    class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-y-3 xl:gap-y-0"
                >
                    <div class="border-r-2 border-[#E9E7E7] border-dashed">
                        <div class="font-medium text-[#676666]">
                            {{ __("Name") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ form.name }}
                        </div>
                    </div>

                    <div
                        class="ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Phone") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ form.phone }}
                        </div>
                    </div>

                    <div
                        class="ml-12 md:ml-0 xl:ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Country") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ findEnumKeyByValue(Country, form.country) }}
                        </div>
                    </div>

                    <div
                        class="ml-12 md:ml-0 xl:ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Default Currency") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{
                                __(
                                    replaceEnumUnderscores(
                                        findEnumKeyByValue(
                                            Currency,
                                            form.default_currency
                                        )
                                    )
                                )
                            }}
                        </div>
                    </div>
                </div>
            </template>

            <div class="add-users flex justify-end my-4">
                <button
                    v-if="form.id"
                    class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                    @click="openCreateUserModal"
                >
                    {{ __("Add Users") }}
                </button>
            </div>

            <div class="grid lg:grid-cols-1 xl:grid-cols-2 gap-4">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:border-r border-[#E9E7E7] xl:pr-8 sm:gap-y-2 items-center"
                >
                    <label for="type">
                        {{ __("Type") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        v-model="form.type"
                        :name="'type'"
                        :options="CrmCompanyType"
                        :placeholder="__('Type')"
                        class="w-full mb-3.5 sm:mb-0"
                    />

                    <label for="name">
                        {{ __("Name") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Input
                        v-model="form.name"
                        :name="'name'"
                        type="text"
                        :placeholder="__('Name')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="country">
                        {{ __("Country") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        v-model="form.country"
                        :name="'country'"
                        :options="Country"
                        :placeholder="__('Country')"
                        class="w-full mb-3.5 sm:mb-0"
                    />

                    <label for="postal_code">
                        {{ __("Postal Code") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Input
                        v-model="form.postal_code"
                        :name="'postal_code'"
                        type="text"
                        :placeholder="__('Postal Code')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="city">
                        {{ __("City") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Input
                        v-model="form.city"
                        :name="'city'"
                        type="text"
                        :placeholder="__('City')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="address">
                        {{ __("Address") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Input
                        v-model="form.address"
                        :name="'address'"
                        type="text"
                        :placeholder="__('Address')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="street">
                        {{ __("Street") }}
                    </label>
                    <Input
                        v-model="form.street"
                        :name="'street'"
                        type="text"
                        :placeholder="__('Street')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="address_number">
                        {{ __("Address Number") }}
                    </label>
                    <Input
                        v-model="form.address_number"
                        :name="'address_number'"
                        type="text"
                        :placeholder="__('Address Number')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="address_number_addition">
                        {{ __("Address Number Addition") }}
                    </label>
                    <Input
                        v-model="form.address_number_addition"
                        :name="'address_number_addition'"
                        type="text"
                        :placeholder="__('Address Number Addition')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="province">
                        {{ __("Province") }}
                    </label>
                    <Input
                        v-model="form.province"
                        :name="'province'"
                        type="text"
                        :placeholder="__('Province')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="phone">
                        {{ __("Phone") }}
                    </label>
                    <Input
                        v-model="form.phone"
                        :name="'phone'"
                        type="text"
                        :placeholder="__('Phone')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="default_currency">
                        {{ __("Default Currency") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        v-model="form.default_currency"
                        :name="'default_currency'"
                        :options="Currency"
                        :placeholder="__('Default Currency')"
                        class="w-full"
                    />

                    <label v-if="form.id" for="main_user_id">
                        {{ __("Main Contact Person") }}
                    </label>
                    <Select
                        v-if="form.id"
                        v-model="form.main_user_id"
                        :name="'main_user_id'"
                        :options="users"
                        :disabled="!users"
                        :placeholder="__('Main Contact Person')"
                        class="w-full mb-3.5 sm:mb-0"
                    />

                    <label for="company_group_id">
                        {{ __("Company Group") }}
                    </label>
                    <Select
                        v-model="form.company_group_id"
                        :name="'company_group_id'"
                        :options="companyGroups"
                        :placeholder="__('Company Group')"
                        class="w-full"
                    />

                    <label for="locale">
                        {{ __("Language") }}
                    </label>
                    <Select
                        v-model="form.locale"
                        :name="'locale'"
                        :options="Locale"
                        :capitalize="true"
                        :placeholder="__('Language')"
                        class="w-full mb-3.5 sm:mb-0"
                    />
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:pl-4 sm:gap-y-2 items-center"
                >
                    <label for="vat_number">
                        {{ __("Vat Number") }}
                    </label>
                    <Input
                        v-model="form.vat_number"
                        :name="'vat_number'"
                        type="text"
                        :placeholder="__('Vat Number')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="type">
                        {{ __("Type of purchase order") }}
                    </label>
                    <Select
                        v-model="form.purchase_type"
                        :name="'purchase_type'"
                        :options="NationalEuOrWorldType"
                        :placeholder="__('Type of purchase order')"
                        class="w-full"
                    />

                    <label for="number">
                        {{ __("Number") }}
                    </label>
                    <Input
                        v-model="form.number"
                        :name="'number'"
                        type="text"
                        :placeholder="__('Number')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="number_addition">
                        {{ __("Number Addition") }}
                    </label>
                    <Input
                        v-model="form.number_addition"
                        :name="'number_addition'"
                        type="text"
                        :placeholder="__('Number Addition')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="company_number_accounting_system">
                        {{ __("Company Number Accounting system") }}
                    </label>
                    <Input
                        v-model="form.company_number_accounting_system"
                        :name="'company_number_accounting_system'"
                        type="text"
                        :placeholder="__('Company Number Accounting system')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="debtor_number_accounting_system">
                        {{ __("Debtor Number Accounting system") }}
                    </label>
                    <Input
                        v-model="form.debtor_number_accounting_system"
                        :name="'debtor_number_accounting_system'"
                        type="text"
                        :placeholder="__('Debtor Number Accounting system')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="creditor_number_accounting_system">
                        {{ __("Creditor Number Accounting system") }}
                    </label>
                    <Input
                        v-model="form.creditor_number_accounting_system"
                        :name="'creditor_number_accounting_system'"
                        type="text"
                        :placeholder="__('Creditor Number Accounting system')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="website">
                        {{ __("Website") }}
                    </label>
                    <Input
                        v-model="form.website"
                        :name="'website'"
                        type="text"
                        :placeholder="__('Website')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="email">
                        {{ __("General Email") }}
                    </label>
                    <Input
                        v-model="form.email"
                        :name="'email'"
                        type="text"
                        :placeholder="__('Email')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="kvk_number">
                        {{ __("KvK Number") }}
                    </label>
                    <Input
                        v-model="form.kvk_number"
                        :name="'kvk_number'"
                        type="text"
                        :placeholder="__('KvK Number')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="bank_name">
                        {{ __("Bank Name") }}
                    </label>
                    <Input
                        v-model="form.bank_name"
                        :name="'bank_name'"
                        type="text"
                        :placeholder="__('Bank Name')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="iban">
                        {{ __("IBAN") }}
                    </label>
                    <Input
                        v-model="form.iban"
                        :name="'iban'"
                        type="text"
                        :placeholder="__('IBAN')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="swift_or_bic">
                        {{ __("SWIFT/BIC") }}
                    </label>
                    <Input
                        v-model="form.swift_or_bic"
                        :name="'swift_or_bic'"
                        type="text"
                        :placeholder="__('SWIFT/BIC')"
                        class="mb-3.5 sm:mb-0"
                    />
                </div>
            </div>
        </Accordion>
    </div>

    <Modal :show="showCreateUserModal">
        <CreateUserGeneralInformationComponent
            v-if="companies && roles"
            :form="createUserForm"
            :companies="companies"
            :roles="roles"
        />

        <ModalSaveButtons
            :processing="createUserForm.processing"
            :save-text="__('Create')"
            @cancel="closeCreateUserModal"
            @save="handleCreateUser"
        />
    </Modal>
</template>
