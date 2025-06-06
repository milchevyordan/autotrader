<script setup lang="ts">
import { InertiaForm, useForm, usePage } from "@inertiajs/vue3";
import { ref } from "vue";

import Accordion from "@/components/Accordion.vue";
import DatePicker from "@/components/html/DatePicker.vue";
import Input from "@/components/html/Input.vue";
import RadioButtonToggle from "@/components/html/RadioButtonToggle.vue";
import Modal from "@/components/Modal.vue";
import OwnerSelect from "@/components/OwnerSelect.vue";
import Select from "@/components/Select.vue";
import { Multiselect } from "@/data-table/types";
import { Locale } from "@/enums/Locale";
import { TransportableType } from "@/enums/TransportableType";
import { TransportOrderFileType } from "@/enums/TransportOrderFileType";
import { TransportOrderStatus } from "@/enums/TransportOrderStatus";
import { TransportType } from "@/enums/TransportType";
import {
    Company,
    CompanyAddress,
    DatabaseFile,
    Enum,
    OwnerProps,
    SelectInput,
    TransportOrderForm,
    User,
} from "@/types";
import {
    convertCurrencyToUnits,
    convertUnitsToCurrency,
    dateToLocaleString,
    downLoadFile,
    downLoadFiles,
    findEnumKeyByValue,
    findLastFileStartingWith,
    formatPriceOnBlur,
    formatPriceOnFocus,
    padWithZeros,
    replaceEnumUnderscores,
} from "@/utils.js";

const props = defineProps<{
    form: InertiaForm<TransportOrderForm>;
    formDisabled?: boolean;
    transportOrder?: TransportOrderForm;
    ownerProps: OwnerProps;
    transporters?: Multiselect<User>;
    ownLogisticsAddresses?: Multiselect<CompanyAddress>;
    pickUpAddresses?: Multiselect<CompanyAddress>;
    deliveryAddresses?: Multiselect<CompanyAddress>;
    companies: Multiselect<Company>;
    generatedPdf?: Array<DatabaseFile>;
}>();

const emit = defineEmits(["form-updated"]);

const reset = ref<{
    transportCompany: boolean;
    transportPerson: boolean;
    pickUpLocation: boolean;
    deliveryLocation: boolean;
}>({
    transportCompany: false,
    transportPerson: false,
    pickUpLocation: false,
    deliveryLocation: false,
});

const handleValueUpdated = async (input: SelectInput): Promise<void> => {
    switch (input.name) {
        case "transport_company_id":
            reset.value.transportCompany = false;
            reset.value.transportPerson = true;
            break;

        case "transport_company_use":
            if (input.value) {
                break;
            }

            reset.value.transportCompany = true;
            reset.value.transportPerson = true;
            break;

        case "pick_up_company_id":
            reset.value.pickUpLocation = true;
            break;

        case "delivery_company_id":
            reset.value.deliveryLocation = true;
            break;

        default:
            reset.value.transportCompany = false;
            reset.value.transportPerson = false;
            reset.value.pickUpLocation = false;
            reset.value.deliveryLocation = false;
            break;
    }

    emit("form-updated", input);
};

const generateForm = useForm<{
    _method: string;
    id: number;
    type: Enum<typeof TransportOrderFileType>;
    locale: Enum<typeof Locale>;
}>({
    _method: "patch",
    id: props.transportOrder?.id,
    type: null!,
    locale: usePage().props.locale,
});

const generate = async () => {
    await new Promise<void>((resolve, reject) => {
        generateForm.post(
            route("transport-orders.generate-file", generateForm.id),
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    resolve();
                },
                onError: () => {
                    reject();
                },
            }
        );
        closeGenerateFileModal();
    });

    switch (generateForm.type) {
        case TransportOrderFileType.Pick_Up_Authorization:
            const pickUpFile = findLastFileStartingWith(
                props.generatedPdf,
                "pick-up-authorization"
            );

            if (pickUpFile) {
                downLoadFile((pickUpFile as DatabaseFile).unique_name);
            }
            break;
        case TransportOrderFileType.Transport_Request:
            const transportRequestFile = findLastFileStartingWith(
                props.generatedPdf,
                "transport-request"
            );

            if (transportRequestFile) {
                downLoadFile(
                    (transportRequestFile as DatabaseFile).unique_name
                );
            }
            break;
        case TransportOrderFileType.Stickervel:
            let lastFiles = findLastFileStartingWith(
                props.generatedPdf,
                "vehicle-",
                props.form.transportableIds.length
            );

            (lastFiles as DatabaseFile[]).forEach((file) => {
                if (file) {
                    downLoadFiles(file.unique_name);
                }
            });

            break;
    }

    generateForm.reset();
};

const showGenerateFileModal = ref<boolean>(false);

const openGenerateFileModal = (type: Enum<typeof TransportOrderFileType>) => {
    generateForm.type = type;
    showGenerateFileModal.value = true;
};

const closeGenerateFileModal = () => {
    showGenerateFileModal.value = false;
};

const distributeTotalTransportPrice = () => {
    const totalInUnits = Number(
        convertCurrencyToUnits(props.form.total_transport_price)
    );

    const transportablesCount = Object.keys(props.form.transportables).length;

    if (transportablesCount > 0) {
        const evenPrice = totalInUnits / transportablesCount;

        Object.values(props.form.transportables).forEach((obj: any) => {
            obj.price = convertUnitsToCurrency(evenPrice);
        });
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

            <div
                class="my-4 flex flex-col sm:flex-row items-start sm:items-center sm:justify-end gap-3 sm:gap-5"
            >
                <button
                    v-if="transportOrder?.id"
                    class="rounded px-4 py-1.5 active:scale-95 bg-[#00A793] hover:bg-emerald-500 h-8 text-white text-xs"
                    @click="
                        openGenerateFileModal(
                            TransportOrderFileType.Transport_Request
                        )
                    "
                >
                    {{ __("Generate") }} {{ __("Transport Request") }}
                </button>
                <button
                    v-if="
                        transportOrder?.id &&
                        transportOrder.vehicle_type !=
                            TransportableType.Pre_order_vehicles
                    "
                    class="rounded px-4 py-1.5 active:scale-95 bg-[#00A793] hover:bg-emerald-500 h-8 text-white text-xs"
                    @click="
                        openGenerateFileModal(TransportOrderFileType.Stickervel)
                    "
                >
                    {{ __("Generate") }} {{ __("Stickervel") }}
                </button>
                <button
                    v-if="
                        transportOrder?.id &&
                        transportOrder?.status < TransportOrderStatus.Picked_up
                    "
                    class="rounded px-4 py-1.5 active:scale-95 bg-[#00A793] hover:bg-emerald-500 h-8 text-white text-xs"
                    @click="
                        openGenerateFileModal(
                            TransportOrderFileType.Pick_Up_Authorization
                        )
                    "
                >
                    {{ __("Generate") }} {{ __("Pick Up Authorization") }}
                </button>
            </div>

            <template #collapsedContent>
                <div
                    class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-y-3 xl:gap-y-0"
                >
                    <div class="border-r-2 border-[#E9E7E7] border-dashed">
                        <div class="font-medium text-[#676666]">
                            {{ __("Transport order number") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ form.id }}
                        </div>
                    </div>

                    <div
                        class="ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Type of transport") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{
                                findEnumKeyByValue(
                                    TransportType,
                                    form.transport_type
                                )
                            }}
                        </div>
                    </div>

                    <div
                        class="md:ml-12 border-r-2 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Creation date") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ dateToLocaleString(transportOrder?.created_at) }}
                        </div>
                    </div>

                    <div
                        class="ml-12 border-r-2 xl:border-0 border-[#E9E7E7] border-dashed"
                    >
                        <div class="font-medium text-[#676666]">
                            {{ __("Amount of vehicles") }}
                        </div>

                        <div class="font-medium text-lg">
                            {{ form.transportableIds.length }}
                        </div>
                    </div>
                </div>
            </template>

            <div class="grid lg:grid-cols-1 xl:grid-cols-2 gap-4">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:border-r border-[#E9E7E7] xl:pr-8 sm:gap-y-2 items-center"
                >
                    <label for="id">
                        {{ __("Transport order number") }}
                    </label>

                    <Input
                        :model-value="
                            props.transportOrder?.id
                                ? padWithZeros(props.transportOrder?.id, 6)
                                : ''
                        "
                        :name="'id'"
                        type="text"
                        :placeholder="__('Transport order number')"
                        :disabled="true"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="total_transport_price">
                        {{ __("Total Transport Price") }}
                    </label>
                    <Input
                        v-model="form.total_transport_price"
                        :name="'total_transport_price'"
                        type="text"
                        :disabled="formDisabled"
                        :placeholder="__('Total Transport Price')"
                        class="mb-3.5 sm:mb-0"
                        @focus="
                            formatPriceOnFocus(form, 'total_transport_price')
                        "
                        @blur="formatPriceOnBlur(form, 'total_transport_price')"
                        @change="distributeTotalTransportPrice"
                    />

                    <label for="vehicle_type">
                        {{ __("Vehicle Type") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        :key="form.vehicle_type"
                        v-model="form.vehicle_type"
                        :name="'vehicle_type'"
                        :options="TransportableType"
                        :placeholder="__('Vehicle Type')"
                        class="w-full"
                        :disabled="
                            formDisabled ||
                            !!(form.vehicle_type && form.transport_type)
                        "
                        @select="handleValueUpdated"
                    />

                    <label for="transport_type">
                        {{ __("Transport Type") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <Select
                        :key="form.transport_type"
                        v-model="form.transport_type"
                        :name="'transport_type'"
                        :options="TransportType"
                        :placeholder="__('Transport Type')"
                        class="w-full"
                        :disabled="
                            formDisabled ||
                            !!(form.vehicle_type && form.transport_type)
                        "
                        @select="handleValueUpdated"
                    />

                    <label for="transport_company_use">
                        {{ __("Transport company use") }}
                        <span class="text-red-500"> *</span>
                    </label>
                    <RadioButtonToggle
                        :key="form.transport_company_use"
                        v-model="form.transport_company_use"
                        name="transport_company_use"
                        :disabled="formDisabled"
                        @change="handleValueUpdated"
                    />

                    <label for="transport_company_id">
                        {{ __("Transport Company") }}
                        <span
                            v-if="form.transport_company_use"
                            class="text-red-500"
                            >*</span
                        >
                    </label>
                    <Select
                        v-model="form.transport_company_id"
                        :name="'transport_company_id'"
                        :options="companies"
                        :placeholder="__('Transport Company')"
                        :reset="reset.transportCompany"
                        :disabled="formDisabled || !form.transport_company_use"
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleValueUpdated"
                        @remove="reset.transportPerson = true"
                    />

                    <label for="transporter_id">
                        {{ __("Contact person") }}
                    </label>
                    <Select
                        v-model="form.transporter_id"
                        :name="'transporter_id'"
                        :options="transporters"
                        :reset="reset.transportPerson"
                        :placeholder="__('Contact person transport company')"
                        class="w-full mb-3.5 sm:mb-0"
                        :disabled="formDisabled || !form.transport_company_use"
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

                    <label
                        v-if="form.transport_type == TransportType.Other"
                        for="pick_up_company_id"
                    >
                        {{ __("Pick up company") }}
                    </label>
                    <Select
                        v-if="form.transport_type == TransportType.Other"
                        v-model="form.pick_up_company_id"
                        :name="'pick_up_company_id'"
                        :options="companies"
                        :disabled="formDisabled"
                        :placeholder="__('Pick up company')"
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleValueUpdated"
                    />

                    <label
                        v-if="form.transport_type != TransportType.Inbound"
                        for="pick_up_location_id"
                    >
                        {{ __("Pick up location select") }}
                    </label>
                    <Select
                        v-if="form.transport_type != TransportType.Inbound"
                        v-model="form.pick_up_location_id"
                        :name="'pick_up_location_id'"
                        :options="
                            Object.keys(pickUpAddresses ?? {}).length
                                ? pickUpAddresses
                                : ownLogisticsAddresses
                        "
                        :reset="reset.pickUpLocation"
                        :disabled="formDisabled"
                        :placeholder="__('Pick up location select')"
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleValueUpdated"
                    />

                    <label
                        v-if="form.transport_type != TransportType.Inbound"
                        for="pick_up_location_free_text"
                    >
                        {{ __("Pick up location free text") }}
                    </label>
                    <Input
                        v-if="form.transport_type != TransportType.Inbound"
                        v-model="form.pick_up_location_free_text"
                        :name="'pick_up_location_free_text'"
                        type="text"
                        :disabled="formDisabled"
                        :placeholder="__('Pick up location free text')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="pick_up_notes">
                        {{ __("Pick up notes") }}
                    </label>
                    <Input
                        v-model="form.pick_up_notes"
                        :name="'pick_up_notes'"
                        type="text"
                        :disabled="formDisabled"
                        :placeholder="__('Pick up notes')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="pick_up_after_date">
                        {{ __("Pick up after") }}
                    </label>
                    <DatePicker
                        v-model="form.pick_up_after_date"
                        :name="'pick_up_after_date'"
                        class="mb-3.5 sm:mb-0"
                        :disabled="formDisabled"
                        :max="form.deliver_before_date"
                    />

                    <label
                        v-if="form.transport_type == TransportType.Other"
                        for="delivery_company_id"
                    >
                        {{ __("Delivery company") }}
                    </label>
                    <Select
                        v-if="form.transport_type == TransportType.Other"
                        v-model="form.delivery_company_id"
                        :name="'delivery_company_id'"
                        :options="companies"
                        :disabled="formDisabled"
                        :placeholder="__('Delivery company')"
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleValueUpdated"
                    />

                    <label
                        v-if="form.transport_type != TransportType.Outbound"
                        for="delivery_location_id"
                    >
                        {{ __("Delivery location select") }}
                    </label>
                    <Select
                        v-if="form.transport_type != TransportType.Outbound"
                        v-model="form.delivery_location_id"
                        :name="'delivery_location_id'"
                        :options="
                            Object.keys(deliveryAddresses ?? {}).length
                                ? deliveryAddresses
                                : ownLogisticsAddresses
                        "
                        :reset="reset.deliveryLocation"
                        :disabled="formDisabled"
                        :placeholder="__('Delivery location select')"
                        class="w-full mb-3.5 sm:mb-0"
                        @select="handleValueUpdated"
                    />

                    <label
                        v-if="form.transport_type != TransportType.Outbound"
                        for="delivery_location_free_text"
                    >
                        {{ __("Delivery location free text") }}
                    </label>
                    <Input
                        v-if="form.transport_type != TransportType.Outbound"
                        v-model="form.delivery_location_free_text"
                        :name="'delivery_location_free_text'"
                        type="text"
                        :disabled="formDisabled"
                        :placeholder="__('Delivery location free text')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="delivery_notes">
                        {{ __("Delivery notes") }}
                    </label>
                    <Input
                        v-model="form.delivery_notes"
                        :name="'delivery_notes'"
                        type="text"
                        :disabled="formDisabled"
                        :placeholder="__('Delivery notes')"
                        class="mb-3.5 sm:mb-0"
                    />

                    <label for="deliver_before_date">
                        {{ __("Deliver before") }}
                    </label>
                    <DatePicker
                        v-model="form.deliver_before_date"
                        :name="'deliver_before_date'"
                        class="mb-3.5 sm:mb-0"
                        :disabled="formDisabled"
                        :min="form.pick_up_after_date"
                    />

                    <label for="planned_delivery_date">
                        {{ __("Planned delivery date") }}
                    </label>
                    <DatePicker
                        v-model="form.planned_delivery_date"
                        :name="'planned_delivery_date'"
                        class="mb-3.5 sm:mb-0"
                        :disabled="formDisabled"
                        :min="form.pick_up_after_date"
                        :max="form.deliver_before_date"
                    />
                </div>
            </div>
        </Accordion>
    </div>

    <Modal :show="showGenerateFileModal" @close="closeGenerateFileModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Generate") }}
            {{
                replaceEnumUnderscores(
                    findEnumKeyByValue(
                        TransportOrderFileType,
                        generateForm.type
                    )
                )
            }}
        </div>

        <hr />

        <div class="container p-4">
            <label for="locale">
                {{ __("Language") }}
                <span class="text-red-500"> *</span>
            </label>
            <Select
                v-model="generateForm.locale"
                :name="'locale'"
                :options="Locale"
                :capitalize="true"
                :placeholder="__('Language')"
                class="w-full mb-3.5 sm:mb-0"
            />
        </div>

        <div
            class="flex justify-center border border-[#E9E7E7] px-3.5 p-3 text-xl font-medium d-flex w-100"
        >
            <button
                :disabled="generateForm.processing"
                class="bg-[#008FE3] text-white px-12 py-2 mx-1 rounded hover:opacity-80 active:scale-95 transition"
                @click="generate"
            >
                {{ __("Download") }}
            </button>

            <button
                class="bg-[#F0F0F0] px-12 py-2 mx-2 rounded hover:opacity-80 active:scale-95 transition"
                @click="closeGenerateFileModal"
            >
                {{ __("Close") }}
            </button>
        </div>
    </Modal>
</template>
