<script setup lang="ts">
import { InertiaForm } from "@inertiajs/vue3";

import Accordion from "@/components/Accordion.vue";
import Input from "@/components/html/Input.vue";
import Select from "@/components/Select.vue";
import SelectMultiple from "@/components/SelectMultiple.vue";
import { Multiselect } from "@/data-table/types";
import { Gender } from "@/enums/Gender";
import { Locale } from "@/enums/Locale";
import { Company, Role, UserForm } from "@/types";

defineProps<{
    form: InertiaForm<UserForm>;
    companies: Multiselect<Company>;
    roles: Multiselect<Role>;
    tokenExists?: boolean;
    formDisabled?: boolean;
    hideCompany?: boolean;
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
                    <div
                        class="ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Name") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ form.prefix }} {{ form.first_name }}
                            {{ form.last_name }}
                        </div>
                    </div>

                    <div
                        class="ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Mobile") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ form.mobile }}
                        </div>
                    </div>
                </div>
            </template>

            <div class="grid lg:grid-cols-1 xl:grid-cols-2 gap-4">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:border-r border-[#E9E7E7] xl:pr-8 sm:gap-y-2 items-center h-fit"
                >
                    <label for="prefix">
                        {{ __("Prefix") }}
                    </label>
                    <Input
                        v-model="form.prefix"
                        :name="'prefix'"
                        type="text"
                        :placeholder="__('Prefix')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="first_name">
                        {{ __("First Name") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Input
                        v-model="form.first_name"
                        :name="'first_name'"
                        type="text"
                        :placeholder="__('First Name')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="last_name">
                        {{ __("Last Name") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Input
                        v-model="form.last_name"
                        :name="'last_name'"
                        type="text"
                        :placeholder="__('Last Name')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="email">
                        {{ __("Email") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Input
                        v-model="form.email"
                        :name="'email'"
                        type="text"
                        :disabled="tokenExists"
                        :placeholder="__('Email')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="gender">
                        {{ __("Gender") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        v-model="form.gender"
                        :name="'gender'"
                        :options="Gender"
                        :placeholder="__('Gender')"
                        class="w-full mb-3.5 sm:mb-0"
                    />

                    <label for="language">
                        {{ __("Language") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        v-model="form.language"
                        :name="'language'"
                        :capitalize="true"
                        :options="Locale"
                        :placeholder="__('Language')"
                        class="w-full mb-3.5 sm:mb-0"
                    />
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:pl-4 sm:gap-y-2 items-center"
                >
                    <label for="mobile">
                        {{ __("Mobile") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Input
                        v-model="form.mobile"
                        :name="'mobile'"
                        type="text"
                        :placeholder="__('Mobile')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="other_phone">
                        {{ __("Other Phone") }}
                    </label>
                    <Input
                        v-model="form.other_phone"
                        :name="'other_phone'"
                        type="text"
                        :placeholder="__('Other Phone')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label v-if="!hideCompany" for="company_id">
                        {{ __("Company") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        v-if="!hideCompany"
                        v-model="form.company_id"
                        :name="'company_id'"
                        :options="companies"
                        :placeholder="__('Company')"
                        :disabled="formDisabled"
                        class="w-full mb-3.5 sm:mb-0"
                    />

                    <label for="roles">
                        {{ __("Roles") }}
                        <span class="text-red-500"> *</span>
                    </label>

                    <SelectMultiple
                        v-model="form.roles"
                        :name="'roles'"
                        :options="roles"
                        :placeholder="__('Roles')"
                        :disabled="formDisabled"
                        class="w-full mb-3.5 sm:mb-0"
                    />
                </div>
            </div>
        </Accordion>
    </div>
</template>
