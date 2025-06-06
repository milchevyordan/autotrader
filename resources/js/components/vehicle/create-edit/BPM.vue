<script setup lang="ts">
import Accordion from "@/components/Accordion.vue";
import Input from "@/components/html/Input.vue";
import InputIcon from "@/components/html/InputIcon.vue";
import Section from "@/components/html/Section.vue";
import { formatPriceOnBlur, formatPriceOnFocus } from "@/utils";

defineProps<{
    form: any;
}>();
</script>

<template>
    <Section classes="p-4 pb-0 mt-4 relative">
        <Accordion>
            <template #head>
                <div class="font-semibold text-xl sm:text-2xl mb-4">
                    {{ __("BPM Table") }}
                </div>
            </template>

            <div class="grid lg:grid-cols-1 xl:grid-cols-2 gap-4 pb-4">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:border-r border-[#E9E7E7] xl:pr-8 sm:gap-y-2 items-center"
                >
                    <label
                        for="gross_bpm_new"
                        :title="
                            __(
                                'Calculation based on CO2, fuel and first admission date'
                            )
                        "
                        >{{ __("Gross BPM new (based on CO2)") }}</label
                    >
                    <Input
                        v-model="form.gross_bpm_new"
                        :name="'gross_bpm_new'"
                        type="text"
                        class="mb-3.5 sm:mb-0"
                        :placeholder="__('Gross BPM new')"
                        @focus="formatPriceOnFocus(form, 'gross_bpm_new')"
                        @blur="formatPriceOnBlur(form, 'gross_bpm_new')"
                    />

                    <label
                        for="depreciation_percentage"
                        :title="
                            __(
                                'Percentage according to depreciation table 1st admission compared to today\'s date'
                            )
                        "
                        >{{ __("Depreciation percentage") }}</label
                    >
                    <InputIcon
                        v-model="form.depreciation_percentage"
                        :name="'depreciation_percentage'"
                        type="number"
                        min="0"
                        max="100"
                        class="mb-3.5 sm:mb-0"
                        :placeholder="__('Depreciation percentage')"
                    >
                        <template #secondIcon>
                            <div class="pr-5">%</div>
                        </template>
                    </InputIcon>

                    <label
                        for="rest_bpm_as_per_table"
                        :title="
                            __(
                                'Calculation from gross BPM, and depreciation table, date of first admission - date today (capture per date of declaration?)'
                            )
                        "
                        >{{ __("Rest BPM as per Table") }}</label
                    >
                    <Input
                        v-model="form.rest_bpm_as_per_table"
                        :name="'rest_bpm_as_per_table'"
                        type="text"
                        class="mb-3.5 sm:mb-0"
                        :placeholder="__('Rest BPM as per Table')"
                        @focus="
                            formatPriceOnFocus(form, 'rest_bpm_as_per_table')
                        "
                        @blur="formatPriceOnBlur(form, 'rest_bpm_as_per_table')"
                    />

                    <label
                        for="calculation_bpm_in_so"
                        :title="
                            __(
                                'Captured from calculation when capturing/directing? SO'
                            )
                        "
                        >{{ __("Calculation BPM in SO") }}</label
                    >
                    <Input
                        v-model="form.calculation_bpm_in_so"
                        :name="'calculation_bpm_in_so'"
                        type="text"
                        class="mb-3.5 sm:mb-0"
                        :placeholder="__('Calculation BPM in SO')"
                        @focus="
                            formatPriceOnFocus(form, 'calculation_bpm_in_so')
                        "
                        @blur="formatPriceOnBlur(form, 'calculation_bpm_in_so')"
                    />

                    <label
                        for="bpm_declared"
                        :title="__('input from workflow during declaration')"
                        >{{ __("BPM declared") }}</label
                    >
                    <Input
                        v-model="form.bpm_declared"
                        :name="'bpm_declared'"
                        type="text"
                        class="mb-3.5 sm:mb-0"
                        :placeholder="__('BPM declared')"
                        @focus="formatPriceOnFocus(form, 'bpm_declared')"
                        @blur="formatPriceOnBlur(form, 'bpm_declared')"
                    />
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:pl-4 sm:gap-y-2 items-center"
                >
                    <label
                        for="gross_bpm_recalculated_based_on_declaration"
                        :title="
                            __(
                                'recalculate back from declaration date to first admission date with reversed depreciation table'
                            )
                        "
                        >{{
                            __("Gross BPM recalculated based on declaration")
                        }}</label
                    >
                    <Input
                        v-model="
                            form.gross_bpm_recalculated_based_on_declaration
                        "
                        :name="'gross_bpm_recalculated_based_on_declaration'"
                        type="text"
                        class="mb-3.5 sm:mb-0"
                        :placeholder="
                            __('Gross BPM recalculated based on declaration')
                        "
                        @focus="
                            formatPriceOnFocus(
                                form,
                                'gross_bpm_recalculated_based_on_declaration'
                            )
                        "
                        @blur="
                            formatPriceOnBlur(
                                form,
                                'gross_bpm_recalculated_based_on_declaration'
                            )
                        "
                    />

                    <label
                        for="gross_bpm_on_registration"
                        :title="
                            __(
                                'from RDW/VWE with vehicle registration data retrieval, after input of vehicle registration number'
                            )
                        "
                        >{{ __("Gross BPM on registration") }}</label
                    >
                    <Input
                        v-model="form.gross_bpm_on_registration"
                        :name="'gross_bpm_on_registration'"
                        type="text"
                        class="mb-3.5 sm:mb-0"
                        :placeholder="__('Gross BPM on registration')"
                        @focus="
                            formatPriceOnFocus(
                                form,
                                'gross_bpm_on_registration'
                            )
                        "
                        @blur="
                            formatPriceOnBlur(form, 'gross_bpm_on_registration')
                        "
                    />

                    <label
                        for="rest_bpm_to_date"
                        :title="
                            __(
                                'calculated with the gross BPM on registration, written down to today'
                            )
                        "
                        >{{ __("Rest BPM to date") }}</label
                    >
                    <Input
                        v-model="form.rest_bpm_to_date"
                        :name="'rest_bpm_to_date'"
                        type="text"
                        class="mb-3.5 sm:mb-0"
                        :placeholder="__('Rest BPM to date')"
                        @focus="formatPriceOnFocus(form, 'rest_bpm_to_date')"
                        @blur="formatPriceOnBlur(form, 'rest_bpm_to_date')"
                    />

                    <label
                        for="invoice_bpm"
                        :title="
                            __('Fixed from calculation when turning invoice')
                        "
                        >{{ __("Invoice BPM") }}</label
                    >
                    <Input
                        v-model="form.invoice_bpm"
                        :name="'invoice_bpm'"
                        type="text"
                        class="mb-3.5 sm:mb-0"
                        :placeholder="__('Invoice BPM')"
                        @focus="formatPriceOnFocus(form, 'invoice_bpm')"
                        @blur="formatPriceOnBlur(form, 'invoice_bpm')"
                    />

                    <label
                        for="bpm_post_change_amount"
                        :title="__('input from workflow if post-charge')"
                        >{{ __("BPM post charge amount") }}</label
                    >
                    <Input
                        v-model="form.bpm_post_change_amount"
                        :name="'bpm_post_change_amount'"
                        type="text"
                        class="mb-3.5 sm:mb-0"
                        :placeholder="__('BPM post charge amount')"
                        @focus="
                            formatPriceOnFocus(form, 'bpm_post_change_amount')
                        "
                        @blur="
                            formatPriceOnBlur(form, 'bpm_post_change_amount')
                        "
                    />
                </div>
            </div>
        </Accordion>
    </Section>
</template>
