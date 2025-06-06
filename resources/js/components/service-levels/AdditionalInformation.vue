<script setup lang="ts">
import { InertiaForm } from "@inertiajs/vue3";

import Accordion from "@/components/Accordion.vue";
import Input from "@/components/html/Input.vue";
import { ServiceLevelForm } from "@/types";

defineProps<{
    form: InertiaForm<ServiceLevelForm>;
}>();
</script>

<template>
    <div
        class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
    >
        <Accordion>
            <template #head>
                <div class="font-semibold text-xl sm:text-2xl mb-4">
                    {{ __("Additional Information") }}
                </div>
            </template>

            <div class="grid lg:grid-cols-1 xl:grid-cols-2 gap-4">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:border-r border-[#E9E7E7] xl:pr-8 sm:gap-y-2 items-center h-fit"
                >
                    <label for="rdw_company_number">
                        {{ __("Rdw Company Number") }}
                    </label>
                    <Input
                        v-model="form.rdw_company_number"
                        :name="'rdw_company_number'"
                        :placeholder="__('Rdw Company Number')"
                        type="text"
                        class="w-full mb-3.5 sm:mb-0"
                    />

                    <label for="login_autotelex">
                        {{ __("Login Autotelex") }}
                    </label>
                    <Input
                        v-model="form.login_autotelex"
                        :name="'login_autotelex'"
                        :placeholder="__('Login Autotelex')"
                        type="text"
                        class="w-full mb-3.5 sm:mb-0"
                    />

                    <label for="api_japie">
                        {{ __("Api Japie") }}
                    </label>
                    <Input
                        v-model="form.api_japie"
                        :name="'api_japie'"
                        :placeholder="__('Api Japie')"
                        type="text"
                        class="w-full mb-3.5 sm:mb-0"
                    />
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:pl-4 sm:gap-y-2 items-center"
                >
                    <label for="bidder_name_autobid">
                        {{ __("Bidder Name Autobid") }}
                    </label>
                    <Input
                        v-model="form.bidder_name_autobid"
                        :name="'bidder_name_autobid'"
                        :placeholder="__('Bidder Name Autobid')"
                        type="text"
                        class="w-full mb-3.5 sm:mb-0"
                    />

                    <label for="bidder_number_autobid">
                        {{ __("Bidder Number Autobid") }}
                    </label>
                    <Input
                        v-model="form.bidder_number_autobid"
                        :name="'bidder_number_autobid'"
                        :placeholder="__('Bidder Number Autobid')"
                        type="text"
                        class="w-full mb-3.5 sm:mb-0"
                    />

                    <label for="api_vwe">
                        {{ __("Api Vwe") }}
                    </label>
                    <Input
                        v-model="form.api_vwe"
                        :name="'api_vwe'"
                        :placeholder="__('Api Vwe')"
                        type="text"
                        class="w-full mb-3.5 sm:mb-0"
                    />
                </div>
            </div>
        </Accordion>
    </div>
</template>
