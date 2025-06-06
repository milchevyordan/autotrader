<script setup lang="ts">
import Accordion from "@/components/Accordion.vue";
import Input from "@/components/html/Input.vue";
import Section from "@/components/html/Section.vue";
import Textarea from "@/components/html/Textarea.vue";
import Select from "@/components/Select.vue";
import { Multiselect } from "@/data-table/types";
import { User } from "@/types";

defineProps<{
    form: any;
    users?: Multiselect<User>;
    showMainUsersSelect?: boolean;
}>();
</script>

<template>
    <Section classes="p-4 pb-0 mt-4 relative">
        <Accordion>
            <template #head>
                <div class="font-semibold text-xl sm:text-2xl mb-4">
                    {{ __("Address Remarks") }}
                </div>
            </template>

            <div
                class="grid lg:grid-cols-1 xl:grid-cols-2 gap-4 mt-4 lg:mt-10 pb-4"
            >
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:border-r border-[#E9E7E7] xl:pr-8 sm:gap-y-2 items-center"
                >
                    <label v-if="showMainUsersSelect" for="billing_contact_id">
                        {{ __("Billing Contact Person") }}
                    </label>
                    <Select
                        v-if="showMainUsersSelect"
                        v-model="form.billing_contact_id"
                        :name="'billing_contact_id'"
                        :options="users"
                        :disabled="!users"
                        :placeholder="__('Billing Contact Person')"
                        class="w-full mb-3.5 sm:mb-0"
                    />

                    <div class="col-span-2">
                        <Textarea
                            v-model="form.billing_remarks"
                            :name="'billing_remarks'"
                            :placeholder="__('Billing Remarks')"
                        />
                    </div>
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:pl-4 sm:gap-y-2 items-center"
                >
                    <label for="logistics_times">
                        {{ __("Logistics times") }}
                    </label>
                    <Input
                        v-model="form.logistics_times"
                        :name="'logistics_times'"
                        type="text"
                        :placeholder="__('Logistics times')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label
                        v-if="showMainUsersSelect"
                        for="logistics_contact_id"
                    >
                        {{ __("Logistics Contact Person") }}
                    </label>
                    <Select
                        v-if="showMainUsersSelect"
                        v-model="form.logistics_contact_id"
                        :name="'logistics_contact_id'"
                        :options="users"
                        :disabled="!users"
                        :placeholder="__('Logistics Contact Person')"
                        class="w-full mb-3.5 sm:mb-0"
                    />

                    <div class="col-span-2">
                        <Textarea
                            v-model="form.logistics_remarks"
                            :name="'logistics_remarks'"
                            :placeholder="__('Logistics Remarks')"
                        />
                    </div>
                </div>
            </div>
        </Accordion>
    </Section>
</template>
