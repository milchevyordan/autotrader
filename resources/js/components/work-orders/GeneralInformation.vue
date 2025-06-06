<script setup lang="ts">
import { InertiaForm, router } from "@inertiajs/vue3";

import Accordion from "@/components/Accordion.vue";
import Input from "@/components/html/Input.vue";
import OwnerSelect from "@/components/OwnerSelect.vue";
import Select from "@/components/Select.vue";
import { WorkOrderType } from "@/enums/WorkOrderType";
import { OwnerProps, SelectInput, WorkOrder, WorkOrderForm } from "@/types";
import { padWithZeros } from "@/utils.js";

const props = defineProps<{
    form: InertiaForm<WorkOrderForm>;
    formDisabled?: boolean;
    workOrder?: WorkOrder;
    ownerProps: OwnerProps;
}>();

const handleValueUpdated = async (input: SelectInput): Promise<void> => {
    switch (input.name) {
        case "type":
            await new Promise((resolve, reject) => {
                router.reload({
                    data: { type: input.value },
                    only: ["dataTable"],
                    onSuccess: resolve,
                    onError: reject,
                });
            });

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
                            {{ __("Work order number") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ form.id }}
                        </div>
                    </div>

                    <div
                        class="md:ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Total Price") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ props.workOrder?.total_price }}
                        </div>
                    </div>

                    <div
                        class="ml-12 border-r-2 xl:border-0 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Amount of tasks") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ props.workOrder?.tasks?.length }}
                        </div>
                    </div>
                </div>
            </template>

            <div class="grid lg:grid-cols-1 xl:grid-cols-2 gap-4">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:border-r border-[#E9E7E7] xl:pr-8 sm:gap-y-2 items-center"
                >
                    <label for="id">
                        {{ __("Work order number") }}
                    </label>

                    <Input
                        :name="'id'"
                        type="text"
                        :model-value="
                            props.workOrder?.id
                                ? padWithZeros(props.workOrder?.id, 6)
                                : ''
                        "
                        :placeholder="__('Work order number')"
                        disabled
                        class="mb-3.5 sm:mb-0"
                    />

                    <OwnerSelect
                        v-if="$can('create-ownership')"
                        v-model="form.owner_id"
                        :users="ownerProps.mainCompanyUsers"
                        :pending-ownerships="ownerProps.pendingOwnerships"
                        :disabled="formDisabled"
                    />
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:pl-4 sm:gap-y-2 items-center"
                >
                    <label for="type">{{ __("Type") }}</label>
                    <Select
                        v-model="form.type"
                        :name="'type'"
                        :options="WorkOrderType"
                        :disabled="!!form.type"
                        :placeholder="__('Type')"
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleValueUpdated"
                    />

                    <label for="total_price">
                        {{ __("Total Price") }}
                    </label>
                    <Input
                        :model-value="workOrder?.total_price"
                        :name="'total_price'"
                        type="text"
                        :disabled="true"
                        :placeholder="__('Total Price')"
                        class="mb-3.5 sm:mb-0"
                    />
                </div>
            </div>
        </Accordion>
    </div>
</template>
