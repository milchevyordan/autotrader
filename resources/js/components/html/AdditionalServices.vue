<script setup lang="ts">
import { InertiaForm } from "@inertiajs/vue3";
import { watch } from "vue";

import Accordion from "@/components/Accordion.vue";
import Input from "@/components/html/Input.vue";
import RadioButtonToggle from "@/components/html/RadioButtonToggle.vue";
import IconTrash from "@/icons/Trash.vue";
import { AdditionalService } from "@/types";
import { multiplyPrice, formatPriceOnBlur, formatPriceOnFocus } from "@/utils";

const props = defineProps<{
    form: InertiaForm<any>;
    levelServices?: AdditionalService[];
    vehiclesCount?: number;
    formDisabled?: boolean;
    hidePurchasePrice?: boolean;
    hideTotal?: boolean;
}>();

const cloneAdditionalServiceRow = () => {
    const emptyServiceRow = {
        id: null!,
        name: null!,
        purchase_price: null!,
        sale_price: null!,
        in_output: true,
        is_service_level: false,
    };
    props.form.additional_services.push(emptyServiceRow);
};

const deleteService = (rowIndex: number) => {
    props.form.additional_services.splice(rowIndex, 1);
};

watch(
    () => props.form.service_level_id,
    (newValue) => {
        if (!newValue) {
            props.form.additional_services =
                props.form.additional_services.filter(
                    (pack: AdditionalService) => !pack.is_service_level
                );
        }
    }
);

watch(
    () => props.levelServices ?? [],
    (newServices: AdditionalService[]) => {
        if (!props.formDisabled) {
            const transformedAdditionalServices =
                newServices.map((pack) => ({
                    ...pack,
                    is_service_level: pack.is_service_level ?? true,
                })) ?? [];

            if (
                JSON.stringify(transformedAdditionalServices) !==
                JSON.stringify(props.form.additional_services)
            ) {
                props.form.additional_services = transformedAdditionalServices;
            }
        }
    }
);
</script>

<template>
    <div
        class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
    >
        <Accordion>
            <template #head>
                <div class="font-semibold text-xl sm:text-2xl mb-4">
                    {{ __("Additional Products and Services") }}
                    <span v-if="vehiclesCount" class="text-lg sm:text-xl">
                        ({{ vehiclesCount }}
                        {{
                            vehiclesCount == 1 ? __("vehicle") : __("vehicles")
                        }})
                    </span>
                </div>
            </template>

            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs uppercase text-black bg-[#F0F0F0]">
                    <tr>
                        <th v-if="!formDisabled" class="px-6 py-3 border-r">
                            {{ __("Action") }}
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Name") }}
                        </th>
                        <th
                            v-if="!hidePurchasePrice"
                            class="px-6 py-3 border-r"
                        >
                            {{ __("Purchase Price") }}
                        </th>
                        <th
                            v-if="!hidePurchasePrice && !hideTotal"
                            class="px-6 py-3 border-r"
                        >
                            {{ __("Total P.Price") }}
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Sale Price") }}
                        </th>
                        <th v-if="!hideTotal" class="px-6 py-3 border-r">
                            {{ __("Total S.Price") }}
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("In output") }}
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="(pack, rowIndex) in form.additional_services"
                        v-if="form.additional_services.length > 0"
                        :key="pack.id"
                        class="bg-white border-b border-[#E9E7E7]"
                    >
                        <td
                            v-if="!formDisabled"
                            class="whitespace-nowrap px-6 py-2"
                        >
                            <button
                                class="bg-[#E50000] rounded p-2 scale-90 transition hover:bg-red-500 text-white"
                                @click="deleteService(rowIndex)"
                            >
                                <IconTrash />
                            </button>
                        </td>
                        <td class="whitespace-nowrap px-6 py-2">
                            <Input
                                v-model="pack.name"
                                :name="`additional_services[${rowIndex}][name]`"
                                type="text"
                                :placeholder="__('Name')"
                                step="0.01"
                                :disabled="formDisabled"
                                class="mb-3.5 sm:mb-0"
                            />
                        </td>

                        <td
                            v-if="!hidePurchasePrice"
                            class="whitespace-nowrap px-6 py-2"
                        >
                            <Input
                                v-model="pack.purchase_price"
                                :name="`additional_services[${rowIndex}][purchase_price]`"
                                :placeholder="__('Purchase Price')"
                                :disabled="formDisabled"
                                type="text"
                                class="mb-3.5 sm:mb-0"
                                @focus="
                                    formatPriceOnFocus(pack, 'purchase_price')
                                "
                                @blur="
                                    formatPriceOnBlur(pack, 'purchase_price')
                                "
                            />
                        </td>

                        <td
                            v-if="!hidePurchasePrice && !hideTotal"
                            class="whitespace-nowrap px-6 py-2"
                        >
                            {{
                                multiplyPrice(
                                    pack.purchase_price,
                                    vehiclesCount
                                )
                            }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-2">
                            <Input
                                v-model="pack.sale_price"
                                :name="`additional_services[${rowIndex}][sale_price]`"
                                :placeholder="__('Sale Price')"
                                :disabled="formDisabled"
                                class="mb-3.5 sm:mb-0"
                                type="text"
                                @focus="formatPriceOnFocus(pack, 'sale_price')"
                                @blur="formatPriceOnBlur(pack, 'sale_price')"
                            />
                        </td>

                        <td
                            v-if="!hideTotal"
                            class="whitespace-nowrap px-6 py-2"
                        >
                            {{ multiplyPrice(pack.sale_price, vehiclesCount) }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-2">
                            <RadioButtonToggle
                                :key="`${rowIndex}-${pack.id}`"
                                v-model="pack.in_output"
                                :name="`additional_services[${rowIndex}][in_output]`"
                                :disabled="formDisabled"
                            />
                        </td>
                    </tr>

                    <tr v-else>
                        <td
                            class="bg-white text-center py-5 text-lg font-semibold border-b border-[#E9E7E7]"
                            colspan="7"
                        >
                            {{ __("No found data") }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <button
                v-if="!formDisabled"
                class="bg-[#00A793] text-white rounded px-5 py-2 mt-4 font-light active:scale-95 transition hover:bg-emerald-500 cursor-pointer"
                @click="cloneAdditionalServiceRow"
            >
                {{ __("Add row") }}
            </button>
        </Accordion>
    </div>
</template>
