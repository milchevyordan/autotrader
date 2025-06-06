<script setup lang="ts">
import { InertiaForm } from "@inertiajs/vue3";

import Input from "@/components/html/Input.vue";
import Select from "@/components/Select.vue";
import { PaymentCondition } from "@/enums/PaymentCondition";

defineProps<{
    form: InertiaForm<any>;
    required?: boolean;
    formDisabled?: boolean;
}>();
</script>

<template>
    <label for="payment_condition">
        {{ __("Payment condition") }}
        <span v-if="required" class="text-red-500">*</span>
    </label>
    <Select
        :key="form.payment_condition"
        v-model="form.payment_condition"
        :name="'payment_condition'"
        :options="PaymentCondition"
        :placeholder="__('Payment condition')"
        class="w-full"
        :disabled="formDisabled"
    />

    <label
        v-if="
            form.payment_condition ==
            PaymentCondition.See_additional_information
        "
        for="payment_condition_free_text"
    >
        {{ __("Payment condition additional information") }}
    </label>
    <Input
        v-if="
            form.payment_condition ==
            PaymentCondition.See_additional_information
        "
        v-model="form.payment_condition_free_text"
        :name="'payment_condition_free_text'"
        type="text"
        :placeholder="__('Payment condition additional information')"
        class="mb-3.5 sm:mb-0"
        :disabled="formDisabled"
    />
</template>
