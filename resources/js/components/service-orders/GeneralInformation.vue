<script setup lang="ts">
import { InertiaForm, router } from "@inertiajs/vue3";
import { ref } from "vue";

import Accordion from "@/components/Accordion.vue";
import Input from "@/components/html/Input.vue";
import PaymentCondition from "@/components/html/PaymentCondition.vue";
import OwnerSelect from "@/components/OwnerSelect.vue";
import Select from "@/components/Select.vue";
import { Multiselect } from "@/data-table/types";
import { ImportOrOriginType } from "@/enums/ImportOrOriginType";
import {
    Company,
    OwnerProps,
    SelectInput,
    ServiceLevel,
    ServiceLevelDefaults,
    ServiceOrder,
    ServiceOrderForm,
    User,
} from "@/types";
import { findKeyByValue, padWithZeros } from "@/utils.js";

const props = defineProps<{
    form: InertiaForm<ServiceOrderForm>;
    formDisabled?: boolean;
    serviceLevelDefaults?: ServiceLevelDefaults;
    serviceOrder?: ServiceOrder;
    companies: Multiselect<Company>;
    customers?: Multiselect<User>;
    ownerProps: OwnerProps;
    serviceLevels: Multiselect<ServiceLevel>;
    resetServiceLevels?: boolean;
}>();

const reset = ref<{
    customer: boolean;
    serviceLevel: boolean;
}>({
    customer: false,
    serviceLevel: false,
});

const handleValueUpdated = async (input: SelectInput): Promise<void> => {
    switch (input.name) {
        case "customer_company_id":
            await new Promise((resolve, reject) => {
                router.reload({
                    data: { customer_company_id: input.value },
                    only: ["customers", "serviceLevels", "resetServiceLevels"],
                    onSuccess: resolve,
                    onError: reject,
                });
            });

            reset.value.customer = true;
            if (props.resetServiceLevels) {
                reset.value.serviceLevel = true;
            }
            break;

        case "service_level_id":
            await new Promise((resolve, reject) => {
                router.reload({
                    data: { service_level_id: input.value },
                    only: ["levelServices", "items", "serviceLevelDefaults"],
                    onSuccess: resolve,
                    onError: reject,
                });
            });

            props.form.payment_condition =
                props.serviceLevelDefaults?.payment_condition;
            props.form.payment_condition_free_text =
                props.serviceLevelDefaults?.payment_condition_free_text;
            props.form.type_of_service =
                props.serviceLevelDefaults?.type_of_sale;
            reset.value.customer = false;
            reset.value.serviceLevel = false;

            break;

        default:
            reset.value.customer = false;
            reset.value.serviceLevel = false;
            break;
    }
};
</script>

<template>
    <div
        class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
    >
        <Accordion>
            <template #head>
                <div class="font-semibold text-xl sm:text-2xl mb-4">
                    {{ __("General Information") }}: {{ form.id }}
                </div>
            </template>

            <template #collapsedContent>
                <div
                    class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-y-3 xl:gap-y-0"
                >
                    <div class="border-r-2 border-[#E9E7E7] border-dashed">
                        <div class="font-medium text-[#676666]">
                            {{ __("Service order number") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ form.id }}
                        </div>
                    </div>

                    <div
                        v-if="serviceOrder?.customer"
                        class="md:ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Customer") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{
                                findKeyByValue(
                                    companies,
                                    serviceOrder?.customer?.company_id
                                )
                            }}
                        </div>
                    </div>

                    <div
                        class="md:ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Contact Person Customer") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ props.serviceOrder?.customer?.full_name }}
                        </div>
                    </div>

                    <div
                        v-if="serviceOrder?.seller"
                        class="ml-12 md:ml-0 xl:ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Sales Person") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ serviceOrder.seller?.full_name }}
                        </div>
                    </div>
                </div>
            </template>

            <div class="grid lg:grid-cols-1 xl:grid-cols-2 gap-4">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:border-r border-[#E9E7E7] xl:pr-8 sm:gap-y-2 items-center"
                >
                    <label for="id">
                        {{ __("Service order number") }}
                    </label>
                    <Input
                        :name="'id'"
                        type="text"
                        :model-value="
                            props.serviceOrder?.id
                                ? padWithZeros(props.serviceOrder?.id, 6)
                                : ''
                        "
                        :placeholder="__('Service order number')"
                        disabled
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="customer_company_id">
                        {{ __("Customer Company") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        v-model="form.customer_company_id"
                        :name="'customer_company_id'"
                        :options="companies"
                        :disabled="formDisabled"
                        :placeholder="__('Customer')"
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleValueUpdated"
                        @remove="reset.customer = true"
                    />

                    <label for="customer_id">
                        {{ __("Contact person customer") }}
                    </label>
                    <Select
                        v-model="form.customer_id"
                        :name="'customer_id'"
                        :options="customers"
                        :reset="reset.customer"
                        :placeholder="__('Contact person customer')"
                        class="w-full mb-3.5 sm:mb-0"
                        :disabled="formDisabled"
                        @select="handleValueUpdated"
                    />

                    <label for="seller_id">
                        {{ __("Sales Person") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        v-model="form.seller_id"
                        :name="'seller_id'"
                        :options="ownerProps.mainCompanyUsers"
                        :placeholder="__('Sales Person')"
                        class="w-full mb-3.5 sm:mb-0"
                        :disabled="formDisabled"
                        @select="handleValueUpdated"
                    />
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:pl-4 sm:gap-y-2 items-center"
                >
                    <OwnerSelect
                        v-if="$can('create-ownership')"
                        v-model="form.owner_id"
                        :users="ownerProps.mainCompanyUsers"
                        :pending-ownerships="ownerProps.pendingOwnerships"
                        :disabled="formDisabled"
                    />

                    <label for="type_of_service">
                        {{ __("Type of service") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        id="type_of_service"
                        :key="form.type_of_service"
                        v-model="form.type_of_service"
                        :name="'type_of_service'"
                        :options="ImportOrOriginType"
                        :placeholder="__('Type of service')"
                        class="w-full"
                        :disabled="formDisabled"
                    />

                    <PaymentCondition
                        :form="form"
                        :form-disabled="formDisabled"
                        :required="true"
                    />

                    <label for="service_level_id">
                        {{ __("Service Level") }}
                    </label>
                    <Select
                        v-model="form.service_level_id"
                        :name="'service_level_id'"
                        :options="serviceLevels"
                        :placeholder="__('Service Level')"
                        class="w-full"
                        :reset="reset.serviceLevel"
                        :disabled="formDisabled"
                        @select="handleValueUpdated"
                    />
                </div>
            </div>
        </Accordion>
    </div>
</template>
