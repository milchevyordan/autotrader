<script setup lang="ts">
import { InertiaForm } from "@inertiajs/vue3";

import Accordion from "@/components/Accordion.vue";
import Input from "@/components/html/Input.vue";
import Select from "@/components/Select.vue";
import { Country } from "@/enums/Country";
import { Currency } from "@/enums/Currency";
import { CompanyForm } from "@/types";
import { vatPercentage } from "@/vat-percentage";

defineProps<{
    form: InertiaForm<CompanyForm>;
}>();
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
                            {{ __("Email") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ form.email }}
                        </div>
                    </div>

                    <div
                        class="ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("KvK Number") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ form.kvk_number }}
                        </div>
                    </div>
                </div>
            </template>

            <div class="grid lg:grid-cols-1 xl:grid-cols-2 gap-4">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:border-r border-[#E9E7E7] xl:pr-8 sm:gap-y-2 items-center"
                >
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

                    <label for="email">
                        {{ __("Email") }}
                    </label>
                    <Input
                        v-model="form.email"
                        :name="'email'"
                        type="text"
                        :placeholder="__('Email')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="phone">
                        {{ __("Phone") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Input
                        v-model="form.phone"
                        :name="'phone'"
                        type="text"
                        :placeholder="__('Phone')"
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

                    <label for="default_currency">
                        {{ __("Default Currency") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        v-model="form.default_currency"
                        :name="'default_currency'"
                        :options="Currency"
                        :placeholder="__('Default Currency')"
                        class="w-full mb-3.5 sm:mb-0"
                    />

                    <label for="pdf_footer_text">
                        {{ __("Pdf Footer Text") }}
                    </label>
                    <Input
                        v-model="form.pdf_footer_text"
                        :name="'pdf_footer_text'"
                        type="text"
                        :placeholder="__('Pdf Footer Text')"
                        class="mb-3.5 sm:mb-0"
                    />
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:pl-4 sm:gap-y-2 items-center"
                >
                    <label for="vat_percentage">{{ __("VAT%") }}</label>
                    <Select
                        :key="form.vat_percentage"
                        v-model="form.vat_percentage"
                        :name="'vat_percentage'"
                        :options="vatPercentage"
                        :placeholder="__('VAT%')"
                        class="w-full mb-3.5 sm:mb-0"
                    />

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
</template>
