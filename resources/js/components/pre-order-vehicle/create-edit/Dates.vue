<script setup lang="ts">
import { watch } from "vue";

import Accordion from "@/components/Accordion.vue";
import WeekRangePicker from "@/components/html/WeekRangePicker.vue";
import Input from "@/components/html/Input.vue";
import Section from "@/components/html/Section.vue";
import { addWeeksToWeekYear } from "@/utils";

const props = defineProps<{
    form: any;
}>();

watch(
    () => [
        props.form.expected_leadtime_for_delivery_from,
        props.form.expected_leadtime_for_delivery_to,
        props.form.registration_weeks_from,
        props.form.registration_weeks_to,
    ],
    () => {
        updateExpectedDeliveryWeeks();
    }
);

const updateExpectedDeliveryWeeks = () => {
    props.form.expected_delivery_weeks = {
        from: addWeeksToWeekYear(
            props.form.production_weeks?.from,
            Number(props.form.expected_leadtime_for_delivery_from) +
                Number(props.form.registration_weeks_from)
        ),
        to: addWeeksToWeekYear(
            props.form.production_weeks?.to,
            Number(props.form.expected_leadtime_for_delivery_to) +
                Number(props.form.registration_weeks_to)
        ),
    };
};
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
                    <label for="production_weeks">
                        {{ __("Production weeks") }}
                    </label>
                    <WeekRangePicker
                        v-model="form.production_weeks"
                        :name="'production_weeks'"
                        class="mb-3.5 sm:mb-0"
                        @change="updateExpectedDeliveryWeeks"
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
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:pl-4 sm:gap-y-2 items-center"
                >
                    <label for="registration_weeks_from">
                        {{ __("Registration weeks") }}
                    </label>
                    <div class="flex gap-2">
                        <Input
                            v-model="form.registration_weeks_from"
                            :name="'registration_weeks_from'"
                            type="number"
                            step="1"
                            min="0"
                            max="255"
                            :placeholder="__('From')"
                            class="mb-3.5 sm:mb-0"
                        />

                        <Input
                            v-model="form.registration_weeks_to"
                            :name="'registration_weeks_to'"
                            type="number"
                            step="1"
                            min="0"
                            max="255"
                            :placeholder="__('To')"
                            class="mb-3.5 sm:mb-0"
                        />
                    </div>

                    <label for="expected_delivery_weeks">
                        {{ __("Expected delivery weeks") }}
                    </label>
                    <WeekRangePicker
                        :model-value="form.expected_delivery_weeks"
                        disabled
                        :name="'expected_delivery_weeks'"
                        class="mb-3.5 sm:mb-0"
                    />
                </div>
            </div>
        </Accordion>
    </Section>
</template>
