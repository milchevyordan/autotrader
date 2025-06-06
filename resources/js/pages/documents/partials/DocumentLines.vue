<script setup lang="ts">
import { computed } from "vue";

import Accordion from "@/components/Accordion.vue";
import Input from "@/components/html/Input.vue";
import Select from "@/components/Select.vue";
import { DocumentLineType } from "@/enums/DocumentLineType";
import IconTrash from "@/icons/Trash.vue";
import { DocumentLine } from "@/types";
import {
    formatPriceOnBlur,
    formatPriceOnFocus,
    getPriceExcludeVatAndVat,
} from "@/utils";
import { vatPercentage } from "@/vat-percentage";

const props = defineProps<{
    lines: DocumentLine[];
    documentableIds: number[];
    formDisabled?: boolean;
}>();

const cloneAdditionalLineRow = () => {
    const emptyLineRow = {
        id: null!,
        name: null!,
        type: DocumentLineType.Other,
        vat_percentage: null!,
        price_exclude_vat: null!,
        vat: null!,
        price_include_vat: null!,
        documentable_id: null!,
    };

    props.lines.push(emptyLineRow);
};

const deleteLine = (rowIndex: number) => {
    props.lines.splice(rowIndex, 1);
};

const formatLinePriceOnBlur = (line: DocumentLine) => {
    formatPriceOnBlur(line, "price_include_vat");

    const prices = getPriceExcludeVatAndVat(
        line.price_include_vat,
        line.vat_percentage
    );

    line.price_exclude_vat = prices.price_exclude_vat;
    line.vat = prices.vat;
};

const mappedDocumentableIds = computed(() =>
    Object.fromEntries(props.documentableIds.map((id) => [id, id]))
);
</script>

<template>
    <div
        class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
    >
        <Accordion>
            <template #head>
                <div class="font-semibold text-xl sm:text-2xl mb-4">
                    {{ __("Invoice Lines") }}
                </div>
            </template>

            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs uppercase text-black bg-[#F0F0F0]">
                    <tr>
                        <th v-if="!formDisabled" class="px-6 py-3 border-r">
                            {{ __("Action") }}
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Name") }}
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Type") }}
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Invoiceable") }} #
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("VAT %") }}
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Price exclude VAT ") }}
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("VAT") }}
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Price include VAT") }}
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="(line, rowIndex) in lines"
                        v-if="lines.length > 0"
                        :key="line.id"
                        class="bg-white border-b border-[#E9E7E7]"
                    >
                        <td
                            v-if="!formDisabled"
                            class="whitespace-nowrap px-6 py-2"
                        >
                            <button
                                class="bg-[#E50000] rounded p-2 scale-90 transition hover:bg-red-500 text-white"
                                @click="deleteLine(rowIndex)"
                            >
                                <IconTrash />
                            </button>
                        </td>
                        <td class="whitespace-nowrap px-6 py-2">
                            <Input
                                v-model="line.name"
                                :name="`document_lines[${rowIndex}][name]`"
                                type="text"
                                :placeholder="__('Name')"
                                :disabled="formDisabled"
                                class="mb-3.5 sm:mb-0"
                            />
                        </td>

                        <td class="whitespace-nowrap px-6 py-2">
                            <Select
                                :id="`document_lines[${rowIndex}][type]`"
                                v-model="line.type"
                                :name="`document_lines[${rowIndex}][type]`"
                                :options="DocumentLineType"
                                :disabled="formDisabled"
                                :placeholder="__('VAT %')"
                                class="w-full mb-3.5 sm:mb-0"
                            />
                        </td>

                        <td class="whitespace-nowrap px-6 py-2">
                            <Select
                                :id="`document_lines[${rowIndex}][documentable_id]`"
                                :key="`${documentableIds}`"
                                v-model="line.documentable_id"
                                :name="`document_lines[${rowIndex}][documentable_id]`"
                                :options="mappedDocumentableIds"
                                :disabled="formDisabled"
                                :placeholder="__('Invoiceable') + ' #'"
                                class="w-full mb-3.5 sm:mb-0"
                            />
                        </td>

                        <td class="whitespace-nowrap px-6 py-2">
                            <Select
                                :id="`document_lines[${rowIndex}][vat_percentage]`"
                                v-model="line.vat_percentage"
                                :name="`document_lines[${rowIndex}][vat_percentage]`"
                                :options="vatPercentage"
                                :disabled="formDisabled"
                                :placeholder="__('VAT %')"
                                class="w-full mb-3.5 sm:mb-0"
                                @select="formatLinePriceOnBlur(line)"
                                @remove="formatLinePriceOnBlur(line)"
                            />
                        </td>

                        <td class="whitespace-nowrap px-6 py-2">
                            <Input
                                :model-value="line.price_exclude_vat"
                                :name="`document_lines[${rowIndex}][price_exclude_vat]`"
                                :placeholder="__('Price Exclude Vat')"
                                :disabled="true"
                                type="text"
                                class="mb-3.5 sm:mb-0"
                            />
                        </td>

                        <td class="whitespace-nowrap px-6 py-2">
                            <Input
                                :model-value="line.vat"
                                :name="`document_lines[${rowIndex}][vat]`"
                                :placeholder="__('Vat')"
                                :disabled="true"
                                class="mb-3.5 sm:mb-0"
                                type="text"
                            />
                        </td>

                        <td class="whitespace-nowrap px-6 py-2">
                            <Input
                                v-model="line.price_include_vat"
                                :name="`document_lines[${rowIndex}][price_include_vat]`"
                                :placeholder="__('Price Include Vat')"
                                :disabled="formDisabled"
                                class="mb-3.5 sm:mb-0"
                                type="text"
                                @focus="
                                    formatPriceOnFocus(
                                        line,
                                        'price_include_vat'
                                    )
                                "
                                @blur="formatLinePriceOnBlur(line)"
                            />
                        </td>
                    </tr>

                    <tr v-else>
                        <td
                            class="bg-white text-center py-5 text-lg font-semibold border-b border-[#E9E7E7]"
                            colspan="8"
                        >
                            {{ __("No found data") }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <button
                v-if="!formDisabled"
                class="bg-[#00A793] text-white rounded px-5 py-2 mt-4 font-light active:scale-95 transition hover:bg-emerald-500 cursor-pointer"
                @click="cloneAdditionalLineRow"
            >
                {{ __("Add row") }}
            </button>
        </Accordion>
    </div>
</template>
