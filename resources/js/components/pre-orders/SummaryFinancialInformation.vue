<script setup lang="ts">
import Accordion from "@/components/Accordion.vue";
import Input from "@/components/html/Input.vue";
import InputIcon from "@/components/html/InputIcon.vue";
import IconEuro from "@/icons/currency/Euro.vue";
import { PreOrderForm, PreOrderVehicle } from "@/types";

defineProps<{
    form: PreOrderForm;
    preOrderVehicle?: PreOrderVehicle;
}>();
</script>

<template>
    <div
        class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
    >
        <Accordion>
            <template #head>
                <div class="font-semibold text-xl sm:text-2xl mb-4">
                    {{ __("Summary Financial Information") }}
                </div>
            </template>
            <div class="grid lg:grid-cols-1 xl:grid-cols-2 gap-4">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:border-r border-[#E9E7E7] xl:pr-8 sm:gap-y-2 items-center h-fit"
                >
                    <label for="total_purchase_price">
                        {{ __("Total purchase price") }}
                    </label>
                    <Input
                        v-model="form.total_purchase_price"
                        :name="'total_purchase_price'"
                        :disabled="true"
                        type="text"
                        :placeholder="__('Total purchase price')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="total_purchase_price_eur">
                        {{ __("Total purchase price in EUR") }}
                    </label>
                    <InputIcon
                        v-model="form.total_purchase_price_eur"
                        :name="'total_purchase_price_eur'"
                        disabled="true"
                        type="text"
                        :placeholder="__('Total purchase price in EUR')"
                        class="mb-3.5 sm:mb-0"
                    >
                        <template #secondIcon>
                            <IconEuro stroke="1.2" class="text-[#909090]" />
                        </template>
                    </InputIcon>

                    <label for="currency_rate">
                        {{ __("Currency rate") }}
                    </label>
                    <div class="flex justify-end">
                        <div class="w-2/5">
                            <Input
                                v-model="form.currency_rate"
                                :name="'currency_rate'"
                                type="number"
                                min="0"
                                :placeholder="__('Currency Rate')"
                                class="mb-3.5 sm:mb-0"
                                @input="$emit('calculate-prices')"
                            />
                        </div>
                    </div>

                    <label for="total_fee_intermediate_supplier">
                        {{ __("Total fee intermediate supplier") }}
                    </label>
                    <Input
                        v-model="form.total_fee_intermediate_supplier"
                        :name="'total_fee_intermediate_supplier'"
                        :disabled="true"
                        type="text"
                        :placeholder="__('Total fee intermediate supplier')"
                    />
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:pl-4 sm:gap-y-2 items-center"
                >
                    <label for="total_purchase_price">
                        {{ __("Total purchase price exclude VAT") }}
                    </label>
                    <Input
                        v-model="form.total_purchase_price_exclude_vat"
                        :name="'total_purchase_price_exclude_vat'"
                        :disabled="true"
                        type="text"
                        :placeholder="__('Total purchase price exclude VAT')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="total_vat">{{ __("Total VAT") }}</label>
                    <Input
                        v-model="form.total_vat"
                        :name="'total_vat'"
                        :disabled="true"
                        type="text"
                        :placeholder="__('Total VAT')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="total_bpm">{{ __("Total BPM") }}</label>
                    <Input
                        v-model="form.total_bpm"
                        :name="'total_bpm'"
                        :disabled="true"
                        type="text"
                        :placeholder="__('Total BPM')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="total_purchase_price_include_vat">
                        {{ __("Total purchase price in/in") }}
                    </label>
                    <Input
                        v-model="form.total_purchase_price_include_vat"
                        :name="'total_purchase_price_include_vat'"
                        :disabled="true"
                        type="text"
                        :placeholder="__('Total purchase price in/in')"
                        class="mb-3.5 sm:mb-0"
                    />
                </div>
            </div>
        </Accordion>
    </div>
</template>
