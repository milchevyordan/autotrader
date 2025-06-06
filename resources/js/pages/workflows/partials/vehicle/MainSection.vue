<script setup lang="ts">
import { computed } from "vue";

import MainInput from "../MainInput.vue";

import Section from "@/components/html/Section.vue";
import { ExteriorColour } from "@/enums/ExteriorColour";
import { InteriorColour } from "@/enums/InteriorColour";
import { InteriorMaterial } from "@/enums/InteriorMaterial";
import { Transmission } from "@/enums/Transmission";
import { VehicleBody } from "@/enums/VehicleBody";
import IconArrowUpTray from "@/icons/ArrowUpTray.vue";
import IconCalendar from "@/icons/Calendar.vue";
import IconCloud from "@/icons/Cloud.vue";
import Color from "@/icons/Color.vue";
import Configuration from "@/icons/Configuration.vue";
import IdentificationNumber from "@/icons/IdentificationNumber.vue";
import Palette from "@/icons/Palette.vue";
import RegistrationNumber from "@/icons/RegistrationNumber.vue";
import IconTempo from "@/icons/Tempo.vue";
import VehicleSeat from "@/icons/VehicleSeat.vue";
import { Vehicle } from "@/types";
import colorClasses, {
    dateTimeToLocaleString,
    dateToLocaleString,
    findEnumKeyByValue,
    getImage,
    replaceEnumUnderscores,
} from "@/utils.js";
import { Link } from "@inertiajs/vue3";

const props = defineProps<{
    data: Vehicle;
}>();

const exteriorColorName = computed(() =>
    findEnumKeyByValue(ExteriorColour, props.data.specific_exterior_color)
);
</script>

<template>
    <Section>
        <div class="md:flex items-center justify-between p-4">
            <Link
                class="underline text-blue-600 hover:text-blue-900"
                :href="route('vehicles.edit', data.id)"
            >
                <div class="font-semibold text-xl sm:text-2xl">
                    {{ data.make?.name }}, {{ data.vehicle_model?.name }},
                    {{ findEnumKeyByValue(VehicleBody, data.body) }},
                    {{ data?.engine?.name }}
                    {{ data.kw ? ", " + data.kw + "kw" : "" }}
                </div>
            </Link>

            <div class="text-slate-400">
                {{ data.creator.full_name }}
                {{ dateTimeToLocaleString(data.created_at) }}
            </div>
        </div>

        <div class="lg:flex p-4 gap-4">
            <img
                :src="getImage(data.image_path)"
                alt="vehicle image"
                class="object-cover w-full rounded-b-lg mb-3 lg:mb-0 lg:w-48 h-[173px] rounded"
            />

            <div class="w-full grid grid-cols-2 md:grid-cols-3 gap-3 lg:gap-4">
                <div class="space-y-3">
                    <MainInput :text="data.nl_registration_number">
                        <RegistrationNumber />
                    </MainInput>

                    <MainInput :text="data.identification_code">
                        <IdentificationNumber />
                    </MainInput>

                    <MainInput :text="''">
                        <Configuration />
                    </MainInput>

                    <MainInput :text="exteriorColorName">
                        <div
                            v-if="exteriorColorName !== undefined"
                            :class="`w-6 h-6 rounded-l ${colorClasses[exteriorColorName as keyof typeof ExteriorColour]}`"
                        />
                    </MainInput>
                </div>

                <div class="space-y-3">
                    <MainInput
                        :text="
                            replaceEnumUnderscores(
                                findEnumKeyByValue(
                                    Transmission,
                                    props.data.transmission
                                )
                            )
                        "
                    >
                        <IconTempo />
                    </MainInput>

                    <MainInput :text="data.co2_wltp + ' gr WLTP'">
                        <IconCloud />
                    </MainInput>

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
                    <MainInput :text="data.factory_name_color">
                        <Color />
                    </MainInput>

                    <MainInput :text="data.co2_nedc + ' gr NEDC'">
                        <IconCloud />
                    </MainInput>

                    <MainInput
                        :text="
                            data.interior_material
                                ? __(
                                      replaceEnumUnderscores(
                                          findEnumKeyByValue(
                                              InteriorMaterial,
                                              data.interior_material
                                          )
                                      )
                                  )
                                : ''
                        "
                    >
                        <VehicleSeat />
                    </MainInput>

                    <MainInput
                        :text="
                            findEnumKeyByValue(
                                InteriorColour,
                                data.specific_interior_color
                            )
                        "
                    >
                        <Palette />
                    </MainInput>
                </div>
            </div>
        </div>
    </Section>
</template>
