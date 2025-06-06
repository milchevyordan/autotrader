<script setup lang="ts">
import { InertiaForm } from "@inertiajs/vue3";
import { watch } from "vue";

import Accordion from "@/components/Accordion.vue";
import Input from "@/components/html/Input.vue";
import RadioButtonToggle from "@/components/html/RadioButtonToggle.vue";
import { ItemType } from "@/enums/ItemType";
import IconMinus from "@/icons/Minus.vue";
import IconPlus from "@/icons/Plus.vue";
import { OrderItem } from "@/types";
import {
    multiplyPrice,
    findEnumKeyByValue,
    formatPriceOnBlur,
    formatPriceOnFocus,
} from "@/utils";

const props = defineProps<{
    form: InertiaForm<any>;
    items?: OrderItem[];
    vehiclesCount?: number;
    formDisabled?: boolean;
    hideTotal?: boolean;
    hidePurchasePrice?: boolean;
}>();

const addDeliveryServiceItem = (id: number) => {
    const itemId = props.form.items.findIndex(
        (item: OrderItem) => item.id == id
    );

    props.form.items[itemId].shouldBeAdded = true;
};

const removeDeliveryServiceItem = (id: number) => {
    const itemId = props.form.items.findIndex(
        (item: OrderItem) => item.id == id
    );

    props.form.items[itemId].shouldBeAdded = false;
};

watch(
    () => props.form.service_level_id,
    (newValue) => {
        if (!newValue) {
            props.form.items = [];
        }
    }
);

watch(
    () => props.items ?? [],
    (newItems: OrderItem[]) => {
        if (!props.formDisabled) {
            const transformedNewItems =
                newItems.map((item: OrderItem) => ({
                    id: item.id,
                    shortcode: item.shortcode,
                    description: item.description,
                    type: item.type,
                    purchase_price: item.purchase_price,
                    sale_price: item.sale_price || "0",
                    in_output: !!item.in_output, //do not simplify this expression
                    item: item.item,
                    shouldBeAdded: true,
                })) ?? [];

            if (
                JSON.stringify(transformedNewItems) !==
                JSON.stringify(props.form.items)
            ) {
                props.form.items = transformedNewItems;
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
                    {{ __("Service Level Items") }}
                    <span v-if="vehiclesCount" class="text-lg sm:text-xl">
                        ({{ vehiclesCount }}
                        {{
                            vehiclesCount == 1 ? __("vehicle") : __("vehicles")
                        }})
                    </span>
                </div>
            </template>

            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs uppercase text-black bg-[#F0F0F0]">
                    <tr>
                        <th v-if="!formDisabled" class="px-6 py-3 border-r">
                            {{ __("Action") }}
                        </th>
                        <th class="px-6 py-3 border-r">#</th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Type") }}
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Shortcode") }}
                        </th>
                        <th class="px-6 py-3 border-r">
                            {{ __("Description") }}
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
                        v-for="(item, index) in form.items"
                        v-if="form.items.length"
                        :key="index"
                        class="border-b border-[#E9E7E7]"
                        :class="
                            props.form.items
                                .filter((item: OrderItem) => item.shouldBeAdded)
                                .map((item: OrderItem) => item.id)
                                .includes(item.id)
                                ? 'bg-blue-400 text-white'
                                : 'bg-white'
                        "
                    >
                        <td
                            v-if="!formDisabled"
                            class="whitespace-nowrap px-6 py-3.5"
                        >
                            <div
                                v-if="
                                    props.form.items
                                        .filter((item: OrderItem) => item.shouldBeAdded)
                                        .map((item: OrderItem) => item.id)
                                        .includes(item.id)
                                "
                                class="flex gap-1.5"
                            >
                                <button
                                    class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                    @click="removeDeliveryServiceItem(item.id)"
                                >
                                    <IconMinus
                                        classes="w-4 h-4 text-slate-600"
                                    />
                                </button>
                            </div>
                            <div v-else>
                                <button
                                    class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                    @click="addDeliveryServiceItem(item.id)"
                                >
                                    <IconPlus
                                        classes="w-4 h-4 text-slate-600"
                                    />
                                </button>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-3.5">
                            {{ item.id }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-3.5">
                            {{
                                findEnumKeyByValue(
                                    ItemType,
                                    Number(item.type ?? item.item?.type)
                                )
                            }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-3.5">
                            {{ item.shortcode ?? item.item?.shortcode }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-3.5">
                            {{ item.description ?? item.item?.description }}
                        </td>

                        <td
                            v-if="!hidePurchasePrice"
                            class="whitespace-nowrap px-6 py-3.5"
                        >
                            {{
                                item.item?.purchase_price ?? item.purchase_price
                            }}
                        </td>

                        <td
                            v-if="!hidePurchasePrice && !hideTotal"
                            class="whitespace-nowrap px-6 py-3.5"
                        >
                            {{
                                multiplyPrice(
                                    item.item?.purchase_price ??
                                        item.purchase_price,
                                    vehiclesCount
                                )
                            }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-3.5">
                            <Input
                                v-model="item.sale_price"
                                :name="`items[${index}][sale_price]`"
                                :placeholder="__('Sale Price')"
                                :disabled="formDisabled"
                                class="mb-3.5 sm:mb-0"
                                type="text"
                                @focus="formatPriceOnFocus(item, 'sale_price')"
                                @blur="formatPriceOnBlur(item, 'sale_price')"
                            />
                        </td>

                        <td
                            v-if="!hideTotal"
                            class="whitespace-nowrap px-6 py-3.5"
                        >
                            {{ multiplyPrice(item.sale_price, vehiclesCount) }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-3.5">
                            <RadioButtonToggle
                                v-model="item.in_output"
                                :name="`items[${index}][in_output]`"
                                :disabled="formDisabled"
                            />
                        </td>
                    </tr>

                    <tr v-else>
                        <td
                            class="bg-white text-center py-5 text-lg font-semibold border-b border-[#E9E7E7]"
                            colspan="9"
                        >
                            {{ __("No found data") }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </Accordion>
    </div>
</template>
