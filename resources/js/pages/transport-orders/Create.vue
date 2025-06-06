<script setup lang="ts">
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import { computed, onMounted } from "vue";

import Accordion from "@/components/Accordion.vue";
import Header from "@/components/Header.vue";
import Input from "@/components/html/Input.vue";
import InputFile from "@/components/html/InputFile.vue";
import InternalRemarks from "@/components/html/InternalRemarks.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import Select from "@/components/Select.vue";
import GeneralInformation from "@/components/transport-orders/GeneralInformation.vue";
import Table from "@/data-table/Table.vue";
import { DataTable, Multiselect } from "@/data-table/types";
import { TransportableType } from "@/enums/TransportableType";
import { TransportType } from "@/enums/TransportType";
import IconMinus from "@/icons/Minus.vue";
import IconPlus from "@/icons/Plus.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { transportOrdersFormRules } from "@/rules/transport-orders-form-rules";
import {
    Company,
    CompanyAddress,
    Role,
    SelectInput,
    TransportOrderForm,
    User,
    Vehicle,
    WorkflowProcess,
} from "@/types";
import {
    convertCollectionToMultiselectInTransportOrder,
    convertCurrencyToUnits,
    convertUnitsToCurrency,
    dateTimeToLocaleString,
    findEnumKeyByValue,
    formatPriceOnBlur,
    formatPriceOnFocus,
    replaceEnumUnderscores,
    transportableTypeComponentMap,
} from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    companies: Multiselect<Company>;
    transporters?: Multiselect<User>;
    dataTable: DataTable<any>;
    queryVehicleIds: Array<number>;
    mainCompanyRoles?: Multiselect<Role>;
    mainCompanyUsers: Multiselect<User>;
    ownLogisticsAddresses: Multiselect<CompanyAddress>;
    pickUpAddresses?: Multiselect<CompanyAddress>;
    deliveryAddresses?: Multiselect<CompanyAddress>;
    workflowProcesses?: Multiselect<WorkflowProcess>;
}>();

const form = useForm<TransportOrderForm>({
    id: null!,
    owner_id: usePage().props.auth.user.id,
    status: null!,
    transport_company_use: false,
    transport_company_id: null!,
    transporter_id: null!,
    transport_type: null!,
    vehicle_type: null!,
    pick_up_company_id: null!,
    pick_up_location_id: null!,
    pick_up_location_free_text: null!,
    pick_up_notes: null!,
    pick_up_after_date: null!,
    delivery_company_id: null!,
    delivery_location_id: null!,
    delivery_location_free_text: null!,
    delivery_notes: null!,
    deliver_before_date: null!,
    planned_delivery_date: null!,
    total_transport_price: null!,

    transportInvoiceFiles: [],
    cmrWaybillFiles: [],
    files: [],

    transportables: [],
    transportableIds: [],
    internal_remark_user_ids: [],
    internal_remark_role_ids: [],
    internal_remark: null!,
});

const updateForm = async (newFormValue: SelectInput) => {
    form[newFormValue.name] = newFormValue.value;

    switch (newFormValue.name) {
        case "transport_company_id":
            await new Promise((resolve, reject) => {
                router.reload({
                    data: { transport_company_id: newFormValue.value },
                    only: ["transporters"],
                    onSuccess: resolve,
                    onError: reject,
                });
            });
            break;

        case "transport_type":
        case "vehicle_type":
            if (!(form.vehicle_type && form.transport_type)) {
                break;
            }

            await new Promise((resolve, reject) => {
                router.reload({
                    data: {
                        transport_type: form.transport_type,
                        vehicle_type: form.vehicle_type,
                    },
                    only: ["dataTable"],
                    onSuccess: resolve,
                    onError: reject,
                });
            });

            if (form.transport_type !== TransportType.Other) {
                removeAllTransportables();
            }

            if (form.transport_type == TransportType.Inbound) {
                form.transport_company_use = true;
            }

            break;

        case "pick_up_company_id":
            await new Promise((resolve, reject) => {
                router.reload({
                    data: { pick_up_company_id: newFormValue.value },
                    only: ["pickUpAddresses"],
                    onSuccess: resolve,
                    onError: reject,
                });
            });
            break;

        case "delivery_company_id":
            await new Promise((resolve, reject) => {
                router.reload({
                    data: { delivery_company_id: newFormValue.value },
                    only: ["deliveryAddresses"],
                    onSuccess: resolve,
                    onError: reject,
                });
            });
            break;
    }
};

const addTransportable = (id: number) => {
    const transportableObject = {
        [id]: {
            transportable_id: id,
            location_id: null!,
            location_free_text: null!,
            price: null,
        },
    };

    form.transportables = { ...form.transportables, ...transportableObject };
    form.transportableIds.push(id);
};

const calculateTotalTransportPrice = () => {
    form.total_transport_price = convertUnitsToCurrency(
        Object.values(form.transportables).reduce(
            (sum: number, obj: any) =>
                sum + (Number(convertCurrencyToUnits(obj.price)) || 0),
            0
        )
    );
};

const removeAllTransportables = () => {
    form.transportableIds = [];
    form.transportables = [];
    form.total_transport_price = null!;
};

const removeTransportable = (id: number) => {
    const formIndex = form.transportableIds.indexOf(id);

    if (formIndex !== -1) {
        form.transportableIds.splice(formIndex, 1);
    }

    if (form.transportables.hasOwnProperty(id)) {
        delete form.transportables[id];
    }

    calculateTotalTransportPrice();
};

const save = () => {
    validate(form, transportOrdersFormRules);

    return new Promise<void>((resolve, reject) => {
        form.post(route("transport-orders.store"), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                form.reset();
                resolve();
            },
            onError: () => {
                reject(new Error("Error, during update"));
            },
        });
    });
};

onMounted(() => {
    props.queryVehicleIds.forEach((vehicleId) => {
        const selectedVehicle = props.dataTable.data.find(
            (vehicle: Vehicle) => vehicle.id === vehicleId
        );

        if (selectedVehicle) {
            addTransportable(selectedVehicle.id);
        }
    });

    form.transport_type = Number(
        new URLSearchParams(window.location.search).get("transport_type")
    );
    form.vehicle_type = Number(
        new URLSearchParams(window.location.search).get("vehicle_type")
    );
    if (form.transport_type == TransportType.Inbound) {
        form.transport_company_use = true;
    }
});

const transportableType = computed(() =>
    findEnumKeyByValue(TransportableType, form.vehicle_type)
);
</script>

<template>
    <Head :title="__('Transport Order')" />

    <AppLayout>
        <Header :text="__('Transport Order')" />

        <div
            v-if="form.transport_type && form.vehicle_type && dataTable.data"
            class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
        >
            <Accordion>
                <template #head>
                    <div class="font-semibold text-xl sm:text-2xl mb-4">
                        {{ __("Select") }}
                        {{ replaceEnumUnderscores(transportableType) }}
                    </div>
                </template>

                <Table
                    :data-table="dataTable"
                    :per-page-options="[5, 10, 15, 20, 50]"
                    :global-search="true"
                    :selected-row-indexes="form.transportableIds"
                    :selected-row-column="'id'"
                >
                    <template #cell(variant.name)="{ value, item }">
                        {{ item.variant?.name }}
                    </template>

                    <template #cell(created_at)="{ value, item }">
                        <div class="flex gap-1.5">
                            {{ dateTimeToLocaleString(value) }}
                        </div>
                    </template>

                    <template #cell(updated_at)="{ value, item }">
                        <div class="flex gap-1.5">
                            {{ dateTimeToLocaleString(value) }}
                        </div>
                    </template>

                    <template #cell(action)="{ value, item }">
                        <div class="flex gap-1.5 min-w-[270px]">
                            <div
                                v-if="form.transportableIds.includes(item.id)"
                                class="flex flex-col gap-1.5 w-full"
                            >
                                <div class="flex gap-1.5">
                                    <button
                                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                        @click="removeTransportable(item.id)"
                                    >
                                        <IconMinus
                                            classes="w-4 h-4 text-slate-600"
                                        />
                                    </button>

                                    <component
                                        :is="
                                            transportableTypeComponentMap[
                                                transportableType
                                            ]
                                        "
                                        v-if="transportableType"
                                        :workflow-processes="workflowProcesses"
                                        :item="item"
                                    />

                                    <Select
                                        v-if="
                                            form.transport_type !=
                                                TransportType.Other &&
                                            form.transportables[item.id]
                                        "
                                        :key="item.id"
                                        v-model="
                                            form.transportables[item.id]
                                                .location_id
                                        "
                                        :name="`vehicles[${item.id}][location_id]`"
                                        :options="
                                            convertCollectionToMultiselectInTransportOrder(
                                                item
                                            )
                                        "
                                        :placeholder="
                                            form.transport_type ==
                                            TransportType.Inbound
                                                ? 'Pick up location select'
                                                : 'Delivery location select'
                                        "
                                        class="w-full mb-3.5 sm:mb-0"
                                        @select="updateForm"
                                    />
                                </div>
                                <div class="flex gap-1.5">
                                    <Input
                                        v-if="
                                            form.transport_type !=
                                                TransportType.Other &&
                                            form.transportables[item.id]
                                        "
                                        v-model="
                                            form.transportables[item.id]
                                                .location_free_text
                                        "
                                        :name="`vehicles[${item.id}][location_free_text]`"
                                        :placeholder="
                                            form.transport_type ==
                                            TransportType.Inbound
                                                ? 'Pick up location free text'
                                                : 'Delivery location free text'
                                        "
                                        type="text"
                                    />
                                    <Input
                                        v-if="form.transportables[item.id]"
                                        v-model="
                                            form.transportables[item.id].price
                                        "
                                        :name="`vehicles[${item.id}][price]`"
                                        :placeholder="__('Transport price')"
                                        type="text"
                                        @focus="
                                            formatPriceOnFocus(
                                                form.transportables[item.id],
                                                'price'
                                            )
                                        "
                                        @blur="
                                            formatPriceOnBlur(
                                                form.transportables[item.id],
                                                'price'
                                            )
                                        "
                                        @change="calculateTotalTransportPrice"
                                    />
                                </div>
                            </div>
                            <div v-else class="flex gap-1.5">
                                <button
                                    class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                    @click="addTransportable(item.id)"
                                >
                                    <IconPlus
                                        classes="w-4 h-4 text-slate-600"
                                    />
                                </button>

                                <component
                                    :is="
                                        transportableTypeComponentMap[
                                            transportableType
                                        ]
                                    "
                                    v-if="transportableType"
                                    :workflow-processes="workflowProcesses"
                                    :item="item"
                                />
                            </div>
                        </div>
                    </template>
                </Table>
            </Accordion>
        </div>

        <div class="flex justify-center">
            <div class="w-full max-w-[1721px]">
                <GeneralInformation
                    :form="form"
                    :owner-props="{
                        mainCompanyUsers: mainCompanyUsers,
                    }"
                    :transporters="transporters"
                    :own-logistics-addresses="ownLogisticsAddresses"
                    :pick-up-addresses="pickUpAddresses"
                    :delivery-addresses="deliveryAddresses"
                    :companies="companies"
                    @form-updated="updateForm"
                />

                <InternalRemarks
                    :form="form"
                    :main-company-roles="mainCompanyRoles"
                    :main-company-users="mainCompanyUsers"
                />

                <div
                    class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
                >
                    <div class="font-semibold text-xl sm:text-2xl mb-4">
                        {{ __("Document") }}
                    </div>

                    <div
                        class="grid sm:grid-cols-2 xl:grid-cols-4 gap-5 gap-y-0"
                    >
                        <InputFile
                            id="transport-invoice-files"
                            v-model="form.transportInvoiceFiles"
                            :files="[]"
                            :text="__('Upload Transport Invoice files')"
                            text-classes="py-14"
                        />

                        <InputFile
                            id="cmr-waybill-files"
                            :files="[]"
                            :disabled="true"
                            :text="__('Upload Cmr Waybill files')"
                            text-classes="py-14"
                        />

                        <InputFile
                            id="files"
                            v-model="form.files"
                            :files="[]"
                            :text="__('Upload Additional files')"
                            text-classes="py-14"
                        />
                    </div>
                </div>
            </div>
        </div>

        <ResetSaveButtons
            :processing="form.processing"
            @reset="form.reset()"
            @save="save"
        />
    </AppLayout>
</template>
