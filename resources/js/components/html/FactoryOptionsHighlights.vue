<script setup lang="ts">
import Accordion from "@/components/Accordion.vue";
import Input from "@/components/html/Input.vue";
import Section from "@/components/html/Section.vue";
import Select from "@/components/Select.vue";
import { Airconditioning } from "@/enums/Airconditioning";
import { AppConnect } from "@/enums/AppConnect";
import { Camera } from "@/enums/Camera";
import { CruiseControl } from "@/enums/CruiseControl";
import { DigitalCockpit } from "@/enums/DigitalCockpit";
import { Headlights } from "@/enums/Headlights";
import { KeylessEntry } from "@/enums/KeylessEntry";
import { Navigation } from "@/enums/Navigation";
import { Optics } from "@/enums/Optics";
import { Panorama } from "@/enums/Panorama";
import { PDC } from "@/enums/PDC";
import { SeatHeating } from "@/enums/SeatHeating";
import { SeatMassage } from "@/enums/SeatMassage";
import { SeatsElectricallyAdjustable } from "@/enums/SeatsElectricallyAdjustable";
import { SecondWheels } from "@/enums/SecondWheels";
import { SportsPackage } from "@/enums/SportsPackage";
import { SportsSeat } from "@/enums/SportsSeat";
import { TintedWindows } from "@/enums/TintedWindows";
import { TowBar } from "@/enums/TowBar";
import { Warranty } from "@/enums/Warranty";
import { PreOrderVehicleForm, VehicleForm } from "@/types";

defineProps<{
    form: VehicleForm | PreOrderVehicleForm;
}>();
</script>

<template>
    <Section classes="p-4 pb-0 mt-4 relative">
        <Accordion>
            <template #head>
                <div class="font-semibold text-xl sm:text-2xl mb-4">
                    {{ __("Factory Options / Highlights") }}
                </div>
            </template>

            <div class="grid lg:grid-cols-1 xl:grid-cols-2 gap-4 pb-4">
                <div
                    class="grid grid-cols-1 sm:grid-cols-3 xl:border-r border-[#E9E7E7] xl:pr-8 sm:gap-y-2 items-center"
                >
                    <label for="warranty">
                        {{ __("Warranty") }}
                    </label>
                    <div class="flex col-span-2 gap-2">
                        <Select
                            v-model="form.warranty"
                            :name="'warranty'"
                            :options="Warranty"
                            :placeholder="__('Warranty')"
                            class="w-full mb-3.5 sm:mb-0"
                            :disabled="!!form.warranty_free_text"
                        />

                        <Input
                            v-model="form.warranty_free_text"
                            :name="'warranty_free_text'"
                            type="text"
                            :placeholder="__('Warranty Free Text')"
                            class="mb-3.5 sm:mb-0"
                            :disabled="form.warranty != null"
                        />
                    </div>

                    <label for="navigation">
                        {{ __("Navigation") }}
                    </label>
                    <div class="flex col-span-2 gap-2">
                        <Select
                            v-model="form.navigation"
                            :name="'navigation'"
                            :options="Navigation"
                            :placeholder="__('Navigation')"
                            class="w-full mb-3.5 sm:mb-0"
                            :disabled="!!form.navigation_free_text"
                        />

                        <Input
                            v-model="form.navigation_free_text"
                            :name="'navigation_free_text'"
                            type="text"
                            :placeholder="__('Navigation Free Text')"
                            class="mb-3.5 sm:mb-0"
                            :disabled="form.navigation != null"
                        />
                    </div>

                    <label for="app_connect">
                        {{ __("App connect") }}
                    </label>
                    <div class="flex col-span-2 gap-2">
                        <Select
                            v-model="form.app_connect"
                            :name="'app_connect'"
                            :options="AppConnect"
                            :placeholder="__('App connect')"
                            class="w-full mb-3.5 sm:mb-0"
                            :disabled="!!form.app_connect_free_text"
                        />

                        <Input
                            v-model="form.app_connect_free_text"
                            :name="'app_connect_free_text'"
                            type="text"
                            :placeholder="__('App connect Free Text')"
                            class="mb-3.5 sm:mb-0"
                            :disabled="form.app_connect != null"
                        />
                    </div>

                    <label for="panorama">
                        {{ __("Panorama") }}
                    </label>
                    <div class="flex col-span-2 gap-2">
                        <Select
                            v-model="form.panorama"
                            :name="'panorama'"
                            :options="Panorama"
                            :placeholder="__('Panorama')"
                            class="w-full mb-3.5 sm:mb-0"
                            :disabled="!!form.panorama_free_text"
                        />

                        <Input
                            v-model="form.panorama_free_text"
                            :name="'panorama_free_text'"
                            type="text"
                            :placeholder="__('Panorama Free Text')"
                            class="mb-3.5 sm:mb-0"
                            :disabled="!!form.panorama"
                        />
                    </div>

                    <label for="headlights">
                        {{ __("Headlights") }}
                    </label>
                    <div class="flex col-span-2 gap-2">
                        <Select
                            v-model="form.headlights"
                            :name="'headlights'"
                            :options="Headlights"
                            :placeholder="__('Headlights')"
                            class="w-full mb-3.5 sm:mb-0"
                            :disabled="!!form.headlights_free_text"
                        />

                        <Input
                            v-model="form.headlights_free_text"
                            :name="'headlights_free_text'"
                            type="text"
                            :placeholder="__('Headlights Free Text')"
                            class="mb-3.5 sm:mb-0"
                            :disabled="!!form.headlights"
                        />
                    </div>

                    <label for="digital_cockpit">
                        {{ __("Digital Cockpit") }}
                    </label>
                    <div class="flex col-span-2 gap-2">
                        <Select
                            v-model="form.digital_cockpit"
                            :name="'digital_cockpit'"
                            :options="DigitalCockpit"
                            :placeholder="__('Digital Cockpit')"
                            class="w-full mb-3.5 sm:mb-0"
                            :disabled="!!form.digital_cockpit_free_text"
                        />

                        <Input
                            v-model="form.digital_cockpit_free_text"
                            :name="'digital_cockpit_free_text'"
                            type="text"
                            :placeholder="__('Digital Cockpit Free Text')"
                            class="mb-3.5 sm:mb-0"
                            :disabled="!!form.digital_cockpit"
                        />
                    </div>

                    <label for="cruise_control">
                        {{ __("Cruise Control") }}
                    </label>
                    <div class="flex col-span-2 gap-2">
                        <Select
                            v-model="form.cruise_control"
                            :name="'cruise_control'"
                            :options="CruiseControl"
                            :placeholder="__('Cruise Control')"
                            class="w-full mb-3.5 sm:mb-0"
                            :disabled="!!form.cruise_control_free_text"
                        />

                        <Input
                            v-model="form.cruise_control_free_text"
                            :name="'cruise_control_free_text'"
                            type="text"
                            :placeholder="__('Cruise Control Free Text')"
                            class="mb-3.5 sm:mb-0"
                            :disabled="!!form.cruise_control"
                        />
                    </div>

                    <label for="keyless_entry">
                        {{ __("Keyless entry") }}
                    </label>
                    <div class="flex col-span-2 gap-2">
                        <Select
                            v-model="form.keyless_entry"
                            :name="'keyless_entry'"
                            :options="KeylessEntry"
                            :placeholder="__('Keyless entry')"
                            class="w-full mb-3.5 sm:mb-0"
                            :disabled="!!form.keyless_entry_free_text"
                        />

                        <Input
                            v-model="form.keyless_entry_free_text"
                            :name="'keyless_entry_free_text'"
                            type="text"
                            :placeholder="__('Keyless entry Free Text')"
                            class="mb-3.5 sm:mb-0"
                            :disabled="!!form.keyless_entry"
                        />
                    </div>

                    <label for="air_conditioning">
                        {{ __("Air conditioning") }}
                    </label>
                    <div class="flex col-span-2 gap-2">
                        <Select
                            v-model="form.air_conditioning"
                            :name="'air_conditioning'"
                            :options="Airconditioning"
                            :placeholder="__('Air conditioning')"
                            class="w-full mb-3.5 sm:mb-0"
                            :disabled="!!form.air_conditioning_free_text"
                        />

                        <Input
                            v-model="form.air_conditioning_free_text"
                            :name="'air_conditioning_free_text'"
                            type="text"
                            :placeholder="__('Air conditioning Free Text')"
                            :disabled="!!form.air_conditioning"
                            class="mb-3.5 sm:mb-0"
                        />
                    </div>

                    <label for="pdc">PDC</label>
                    <div class="flex col-span-2 gap-2">
                        <Select
                            v-model="form.pdc"
                            :name="'pdc'"
                            :options="PDC"
                            placeholder="PDC"
                            class="w-full"
                            :disabled="!!form.pdc_free_text"
                        />
                        <Input
                            v-model="form.pdc_free_text"
                            :name="'pdc_free_text'"
                            type="text"
                            :placeholder="__('PDC Free Text')"
                            class="mb-3.5 sm:mb-0"
                            :disabled="!!form.pdc"
                        />
                    </div>

                    <div class="flex col-span-3 gap-2">
                        <Input
                            v-model="form.highlight_1"
                            :name="'highlight_1'"
                            type="text"
                            :placeholder="__('Highlight 1')"
                            class="mb-3.5 sm:mb-0"
                        />

                        <Input
                            v-model="form.highlight_2"
                            :name="'highlight_2'"
                            type="text"
                            :placeholder="__('Highlight 2')"
                            class="mb-3.5 sm:mb-0"
                        />

                        <Input
                            v-model="form.highlight_3"
                            :name="'highlight_3'"
                            type="text"
                            :placeholder="__('Highlight 3')"
                            class="mb-3.5 sm:mb-0"
                        />
                    </div>
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-3 xl:pl-4 sm:gap-y-2 items-center"
                >
                    <label for="second_wheels">
                        {{ __("Second wheels") }}
                    </label>
                    <div class="flex col-span-2 gap-2">
                        <Select
                            v-model="form.second_wheels"
                            :name="'second_wheels'"
                            :options="SecondWheels"
                            :placeholder="__('Second wheels')"
                            class="w-full mb-3.5 sm:mb-0"
                            :disabled="!!form.second_wheels_free_text"
                        />

                        <Input
                            v-model="form.second_wheels_free_text"
                            :name="'second_wheels_free_text'"
                            type="text"
                            :placeholder="__('Second wheels Free Text')"
                            class="mb-3.5 sm:mb-0"
                            :disabled="form.second_wheels != null"
                        />
                    </div>

                    <label for="camera">{{ __("Camera") }}</label>
                    <div class="flex col-span-2 gap-2">
                        <Select
                            v-model="form.camera"
                            :name="'camera'"
                            :options="Camera"
                            :placeholder="__('Camera')"
                            class="w-full"
                            :disabled="!!form.camera_free_text"
                        />
                        <Input
                            v-model="form.camera_free_text"
                            :name="'camera_free_text'"
                            type="text"
                            :placeholder="__('Camera Free Text')"
                            class="mb-3.5 sm:mb-0"
                            :disabled="!!form.camera"
                        />
                    </div>

                    <label for="tow_bar">{{ __("Tow bar") }}</label>
                    <div class="flex col-span-2 gap-2">
                        <Select
                            v-model="form.tow_bar"
                            :name="'tow_bar'"
                            :options="TowBar"
                            :placeholder="__('Tow bar')"
                            class="w-full"
                            :disabled="!!form.tow_bar_free_text"
                        />
                        <Input
                            v-model="form.tow_bar_free_text"
                            :name="'tow_bar_free_text'"
                            type="text"
                            :placeholder="__('Tow bar Free Text')"
                            class="mb-3.5 sm:mb-0"
                            :disabled="!!form.tow_bar"
                        />
                    </div>

                    <label for="sports_seat">
                        {{ __("Sports seat") }}
                    </label>
                    <div class="flex col-span-2 gap-2">
                        <Select
                            v-model="form.sports_seat"
                            :name="'sports_seat'"
                            :options="SportsSeat"
                            :placeholder="__('Sports seat')"
                            class="w-full"
                            :disabled="!!form.sports_seat_free_text"
                        />
                        <Input
                            v-model="form.sports_seat_free_text"
                            :name="'sports_seat_free_text'"
                            type="text"
                            :placeholder="__('Sports seat Free Text')"
                            class="mb-3.5 sm:mb-0"
                            :disabled="form.sports_seat != null"
                        />
                    </div>

                    <label for="seats_electrically_adjustable">
                        {{ __("Seats electrically adjustable") }}
                    </label>
                    <div class="flex col-span-2 gap-2">
                        <Select
                            v-model="form.seats_electrically_adjustable"
                            :name="'seats_electrically_adjustable'"
                            :options="SeatsElectricallyAdjustable"
                            :placeholder="__('Seats electrically adjustable')"
                            class="w-full"
                            :disabled="
                                !!form.seats_electrically_adjustable_free_text
                            "
                        />
                        <Input
                            v-model="
                                form.seats_electrically_adjustable_free_text
                            "
                            :name="'seats_electrically_adjustable_free_text'"
                            type="text"
                            :placeholder="
                                __('Seats electrically adjustable Free Text')
                            "
                            class="mb-3.5 sm:mb-0"
                            :disabled="
                                form.seats_electrically_adjustable != null
                            "
                        />
                    </div>

                    <label for="seat_heating">
                        {{ __("Seat heating") }}
                    </label>
                    <div class="flex col-span-2 gap-2">
                        <Select
                            v-model="form.seat_heating"
                            :name="'seat_heating'"
                            :options="SeatHeating"
                            :placeholder="__('Seat heating')"
                            class="w-full mb-3.5 sm:mb-0"
                            :disabled="!!form.seat_heating_free_text"
                        />
                        <Input
                            v-model="form.seat_heating_free_text"
                            :name="'seat_heating_free_text'"
                            type="text"
                            :placeholder="__('Seat heating Free Text')"
                            class="mb-3.5 sm:mb-0"
                            :disabled="!!form.seat_heating"
                        />
                    </div>

                    <label for="seat_massage">
                        {{ __("Seat massage") }}
                    </label>
                    <div class="flex col-span-2 gap-2">
                        <Select
                            v-model="form.seat_massage"
                            :name="'seat_massage'"
                            :options="SeatMassage"
                            :placeholder="__('Seat massage')"
                            class="w-full mb-3.5 sm:mb-0"
                            :disabled="!!form.seat_massage_free_text"
                        />
                        <Input
                            v-model="form.seat_massage_free_text"
                            :name="'seat_massage_free_text'"
                            type="text"
                            :placeholder="__('Seat massage Free Text')"
                            class="mb-3.5 sm:mb-0"
                            :disabled="!!form.seat_massage"
                        />
                    </div>

                    <label for="optics">{{ __("Optics") }}</label>
                    <div class="flex col-span-2 gap-2">
                        <Select
                            v-model="form.optics"
                            :name="'optics'"
                            :options="Optics"
                            :placeholder="__('Optics')"
                            class="w-full mb-3.5 sm:mb-0"
                            :disabled="!!form.optics_free_text"
                        />
                        <Input
                            v-model="form.optics_free_text"
                            :name="'optics_free_text'"
                            type="text"
                            :placeholder="__('Optics Free Text')"
                            class="mb-3.5 sm:mb-0"
                            :disabled="!!form.optics"
                        />
                    </div>

                    <label for="tinted_windows">
                        {{ __("Tinted windows") }}
                    </label>
                    <div class="flex col-span-2 gap-2">
                        <Select
                            v-model="form.tinted_windows"
                            :name="'tinted_windows'"
                            :options="TintedWindows"
                            :placeholder="__('Tinted windows')"
                            class="w-full mb-3.5 sm:mb-0"
                            :disabled="!!form.tinted_windows_free_text"
                        />
                        <Input
                            v-model="form.tinted_windows_free_text"
                            :name="'tinted_windows_free_text'"
                            type="text"
                            :placeholder="__('Tinted windows Free Text')"
                            class="mb-3.5 sm:mb-0"
                            :disabled="!!form.tinted_windows"
                        />
                    </div>

                    <label for="sports_package">
                        {{ __("Sports package") }}
                    </label>
                    <div class="flex col-span-2 gap-2">
                        <Select
                            v-model="form.sports_package"
                            :name="'sports_package'"
                            :options="SportsPackage"
                            :placeholder="__('Sports package')"
                            class="w-full mb-3.5 sm:mb-0"
                            :disabled="!!form.sports_package_free_text"
                        />
                        <Input
                            v-model="form.sports_package_free_text"
                            :name="'sports_package_free_text'"
                            type="text"
                            :placeholder="__('Sports package Free Text')"
                            class="mb-3.5 sm:mb-0"
                            :disabled="!!form.sports_package"
                        />
                    </div>

                    <div class="flex col-span-3 gap-2">
                        <Input
                            v-model="form.highlight_4"
                            :name="'highlight_4'"
                            type="text"
                            :placeholder="__('Highlight 4')"
                            class="mb-3.5 sm:mb-0"
                        />

                        <Input
                            v-model="form.highlight_5"
                            :name="'highlight_5'"
                            type="text"
                            :placeholder="__('Highlight 5')"
                            class="mb-3.5 sm:mb-0"
                        />

                        <Input
                            v-model="form.highlight_6"
                            :name="'highlight_6'"
                            type="text"
                            :placeholder="__('Highlight 6')"
                            class="mb-3.5 sm:mb-0"
                        />
                    </div>
                </div>
            </div>
        </Accordion>
    </Section>
</template>
