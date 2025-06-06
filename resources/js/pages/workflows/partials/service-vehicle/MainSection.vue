<script setup lang="ts">
import MainInput from "../MainInput.vue";

import Section from "@/components/html/Section.vue";
import { Co2Type } from "@/enums/Co2Type";
import { Country } from "@/enums/Country";
import { VehicleType } from "@/enums/VehicleType";
import IconArrowUpTray from "@/icons/ArrowUpTray.vue";
import IconCalendar from "@/icons/Calendar.vue";
import IconCloud from "@/icons/Cloud.vue";
import Configuration from "@/icons/Configuration.vue";
import RegistrationNumber from "@/icons/RegistrationNumber.vue";
import { ServiceVehicle } from "@/types";
import {
    dateTimeToLocaleString,
    dateToLocaleString,
    findEnumKeyByValue,
    replaceEnumUnderscores,
} from "@/utils.js";

defineProps<{
    data: ServiceVehicle;
}>();
</script>

<template>
    <Section>
        <div
            class="md:flex items-center justify-between p-4 border-b border-[#E9E7E7]"
        >
            <div class="font-semibold text-xl sm:text-2xl">
                {{ data.make.name }}, {{ data.vehicle_model.name }},
                {{ data.vin }}
            </div>

            <div class="text-slate-400 text-sm">
                {{ data.creator.full_name }}
                {{ dateTimeToLocaleString(data.created_at) }}
            </div>
        </div>

        <div class="lg:flex p-4 gap-4">
            <img
                src="/images/no-image.webp"
                alt="default image"
                class="object-cover w-full rounded-b-lg mb-3 lg:mb-0 lg:w-48 h-[173px] rounded"
            />

            <div class="w-full grid grid-cols-2 md:grid-cols-3 gap-3 lg:gap-4">
                <div class="space-y-3">
                    <MainInput
                        :text="
                            replaceEnumUnderscores(
                                findEnumKeyByValue(
                                    VehicleType,
                                    data.vehicle_type
                                )
                            )
                        "
                    >
                        <Configuration />
                    </MainInput>

                    <MainInput
                        :text="
                            findEnumKeyByValue(
                                Country,
                                data.current_registration
                            )
                        "
                    >
                        <RegistrationNumber />
                    </MainInput>
                </div>

                <div class="space-y-3">
                    <MainInput :text="data.kilometers + ' km'">
                        <IconArrowUpTray classes="w-6 h-6 rotate-90" />
                    </MainInput>

                    <MainInput
                        :text="dateToLocaleString(data.first_registration_date)"
                    >
                        <IconCalendar />
                    </MainInput>
                </div>

                <div
                    class="grid grid-cols-2 md:grid-cols-1 col-span-2 md:col-span-1 gap-3 md:gap-0 md:space-y-3"
                >
                    <MainInput
                        :text="
                            data.co2_type +
                            ' ' +
                            findEnumKeyByValue(Co2Type, data.co2_type)
                        "
                    >
                        <IconCloud />
                    </MainInput>
                </div>
            </div>
        </div>
    </Section>
</template>
