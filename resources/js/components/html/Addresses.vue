<script setup lang="ts">
import { InertiaForm } from "@inertiajs/vue3";

import Accordion from "@/components/Accordion.vue";
import Input from "@/components/html/Input.vue";
import Select from "@/components/Select.vue";
import { CompanyAddressType } from "@/enums/CompanyAddressType";
import IconTrash from "@/icons/Trash.vue";
import { CompanyAddress, CompanyForm } from "@/types";

const props = defineProps<{
    addresses: CompanyAddress[];
    form?: InertiaForm<CompanyForm>;
}>();

const cloneAdditionalAddressRow = () => {
    const emptyAddressRow = {
        id: null!,
        company_id: props.form?.id ?? null!,
        type: null!,
        address: null!,
        remarks: null!,
    };

    props.addresses.push(emptyAddressRow);
};

const deleteAddress = (rowIndex: number) => {
    props.addresses.splice(rowIndex, 1);
};
</script>

<template>
    <div
        class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
    >
        <Accordion>
            <template #head>
                <div class="font-semibold text-xl sm:text-2xl mb-4">
                    {{ __("Addresses") }}
                </div>
            </template>

            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs uppercase text-black bg-[#F0F0F0]">
                    <tr>
                        <th class="px-6 py-3 border-r">
                            {{ __("Action") }}
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Type") }}<span class="text-red-500"> *</span>
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Address")
                            }}<span class="text-red-500"> *</span>
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Remarks") }}
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="(item, rowIndex) in addresses"
                        v-if="addresses.length > 0"
                        :key="item.id"
                        class="bg-white border-b border-[#E9E7E7]"
                    >
                        <td class="whitespace-nowrap px-6 py-3.5">
                            <button
                                class="bg-[#E50000] rounded p-2 scale-90 transition hover:bg-red-500 text-white"
                                @click="deleteAddress(rowIndex)"
                            >
                                <IconTrash />
                            </button>
                        </td>
                        <td class="whitespace-nowrap px-6 py-3.5">
                            <Select
                                :id="`company_addresses[${rowIndex}][type]`"
                                :key="`${rowIndex}-${item.type}`"
                                v-model="item.type"
                                :name="`company_addresses[${rowIndex}][type]`"
                                :options="CompanyAddressType"
                                :placeholder="__('Type')"
                                class="w-full mb-3.5 sm:mb-0"
                            />
                        </td>
                        <td class="whitespace-nowrap px-6 py-3.5">
                            <Input
                                v-model="item.address"
                                :name="`company_addresses[${rowIndex}][address]`"
                                :placeholder="__('Address')"
                                type="text"
                                class="mb-3.5 sm:mb-0"
                            />
                        </td>
                        <td class="whitespace-nowrap px-6 py-3.5">
                            <Input
                                v-model="item.remarks"
                                :name="`company_addresses[${rowIndex}][remarks]`"
                                :placeholder="__('Remarks')"
                                type="text"
                                class="mb-3.5 sm:mb-0"
                            />
                        </td>
                    </tr>

                    <tr v-else>
                        <td
                            class="bg-white text-center py-5 text-lg font-semibold border-b border-[#E9E7E7]"
                            colspan="5"
                        >
                            {{ __("No found data") }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <button
                class="bg-[#00A793] text-white rounded px-5 py-2 mt-4 font-light active:scale-95 transition hover:bg-emerald-500 cursor-pointer"
                @click="cloneAdditionalAddressRow"
            >
                {{ __("Add row") }}
            </button>
        </Accordion>
    </div>
</template>
