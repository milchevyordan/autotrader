<script setup lang="ts">
import Accordion from "@/components/Accordion.vue";
import DatePicker from "@/components/html/DatePicker.vue";
import Input from "@/components/html/Input.vue";
import Section from "@/components/html/Section.vue";
import WeekRangePicker from "@/components/html/WeekRangePicker.vue";
import Select from "@/components/Select.vue";
import { Coc } from "@/enums/Coc";

defineProps<{
    form: any;
}>();
</script>

<template>
    <Section classes="p-4 pb-0 mt-4 relative">
        <Accordion>
            <template #head>
                <div class="font-semibold text-xl sm:text-2xl mb-4">
                    {{ __("Dates") }}
                </div>
            </template>

            <div class="grid lg:grid-cols-1 xl:grid-cols-2 gap-4 pb-4">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:border-r border-[#E9E7E7] xl:pr-8 sm:gap-y-2 items-center"
                >
                    <label for="expected_date_of_availability_from_supplier">
                        {{ __("Expected date of availability from supplier") }}
                    </label>
                    <WeekRangePicker
                        v-model="
                            form.expected_date_of_availability_from_supplier
                        "
                        :name="'expected_date_of_availability_from_supplier'"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="expected_leadtime_for_delivery_from">
                        {{ __("Expected leadtime for delivery (weeks)") }}
                    </label>
                    <div class="flex gap-2">
                        <Input
                            v-model="form.expected_leadtime_for_delivery_from"
                            :name="'expected_leadtime_for_delivery_from'"
                            type="number"
                            step="1"
                            min="0"
                            max="255"
                            :placeholder="__('From')"
                            class="mb-3.5 sm:mb-0"
                        />

                        <Input
                            v-model="form.expected_leadtime_for_delivery_to"
                            :name="'expected_leadtime_for_delivery_to'"
                            type="number"
                            step="1"
                            min="0"
                            max="255"
                            :placeholder="__('To')"
                            class="mb-3.5 sm:mb-0"
                        />
                    </div>

                    <label for="coc">COC</label>
                    <Select
                        v-model="form.coc"
                        :name="'coc'"
                        :options="Coc"
                        placeholder="COC"
                        class="w-full mb-3.5 sm:mb-0"
                    />

                    <label for="last_name_registration">
                        {{ __("Last registration on a name") }}
                    </label>
                    <DatePicker
                        v-model="form.last_name_registration"
                        :name="'last_name_registration'"
                        :enable-time-picker="false"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="registration_valid_until">
                        {{ __("APK / Check valid until") }}
                    </label>
                    <DatePicker
                        v-model="form.registration_valid_until"
                        :name="'registration_valid_until'"
                        :enable-time-picker="false"
                        class="mb-3.5 sm:mb-0"
                    />
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:pl-4 sm:gap-y-2 items-center"
                >
                    <label for="first_registration_nl">
                        {{ __("First registration in NL") }}
                    </label>
                    <DatePicker
                        v-model="form.first_registration_nl"
                        :name="'first_registration_nl'"
                        :enable-time-picker="false"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="registration_nl">
                        {{ __("Registration in NL") }}
                    </label>
                    <DatePicker
                        v-model="form.registration_nl"
                        :name="'registration_nl'"
                        :enable-time-picker="false"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="registration_date_approval">
                        {{ __("Registration date approval") }}
                    </label>
                    <DatePicker
                        v-model="form.registration_date_approval"
                        :name="'registration_date_approval'"
                        :enable-time-picker="false"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="first_name_registration_nl">
                        {{ __("First registration on a name in NL") }}
                    </label>
                    <DatePicker
                        v-model="form.first_name_registration_nl"
                        :name="'first_name_registration_nl'"
                        :enable-time-picker="false"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="nl_registration_number">
                        {{ __("NL registration number") }}
                    </label>
                    <Input
                        v-model="form.nl_registration_number"
                        :name="'nl_registration_number'"
                        type="text"
                        placeholder="Registration number"
                        class="mb-3.5 sm:mb-0"
                    />
                </div>
            </div>
        </Accordion>
    </Section>
</template>
