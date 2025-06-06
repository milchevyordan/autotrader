<script setup lang="ts">
import { Head, router, useForm } from "@inertiajs/vue3";
import { computed } from "vue";

import Accordion from "@/components/Accordion.vue";
import Header from "@/components/Header.vue";
import Communication from "@/components/html/Communication.vue";
import Input from "@/components/html/Input.vue";
import InputFile from "@/components/html/InputFile.vue";
import InternalRemarks from "@/components/html/InternalRemarks.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import Select from "@/components/Select.vue";
import GeneralInformation from "@/components/transport-orders/GeneralInformation.vue";
import Stepper from "@/components/transport-orders/Stepper.vue";
import Table from "@/data-table/Table.vue";
import { DataTable, Multiselect } from "@/data-table/types";
import { TransportableType } from "@/enums/TransportableType";
import { TransportOrderStatus } from "@/enums/TransportOrderStatus";
import { TransportType } from "@/enums/TransportType";
import IconMinus from "@/icons/Minus.vue";
import IconPlus from "@/icons/Plus.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { $can } from "@/plugins/permissions";
import { transportOrdersFormRules } from "@/rules/transport-orders-form-rules";
import {
    CompanyAddress,
    Ownership,
    Role,
    SelectInput,
    TransportOrderFiles,
    TransportOrderForm,
    UpdateStatusForm,
    WorkflowProcess,
} from "@/types";
import { Company, User } from "@/types";
import {
    changeStatus,
    convertCollectionToMultiselectInTransportOrder,
    convertCurrencyToUnits,
    convertUnitsToCurrency,
    dateTimeToLocaleString,
    findEnumKeyByValue,
    formatPriceOnBlur,
    formatPriceOnFocus,
    replaceEnumUnderscores,
    resetOwnerId,
    transportableTypeComponentMap,
    withFlash,
} from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    transportOrder: TransportOrderForm;
    selectedTransportables: any;
    selectedTransportableIds: number[];
    files: TransportOrderFiles;
    companies: Multiselect<Company>;
    transporters: Multiselect<User>;
    dataTable: DataTable<any>;
    mainCompanyRoles?: Multiselect<Role>;
    mainCompanyUsers: Multiselect<User>;
    ownLogisticsAddresses: Multiselect<CompanyAddress>;
    pickUpAddresses?: Multiselect<CompanyAddress>;
    deliveryAddresses?: Multiselect<CompanyAddress>;
    acceptedOwnership: Ownership;
    pendingOwnerships: Ownership[];
    workflowProcesses?: Multiselect<WorkflowProcess>;
}>();

const formDisabled = computed(() => {
    return (
        props.transportOrder.status !== TransportOrderStatus.Concept &&
        !$can("super-edit")
    );
});

const form = useForm<TransportOrderForm>({
    _method: "put",
    id: props.transportOrder.id,
    owner_id: props.acceptedOwnership?.user_id,
    status: props.transportOrder.status,
    transport_company_use: props.transportOrder.transport_company_use,
    transport_company_id: props.transportOrder.transport_company_id,
    transporter_id: props.transportOrder.transporter_id,
    transport_type: props.transportOrder.transport_type,
    vehicle_type: props.transportOrder.vehicle_type,
    pick_up_company_id: props.transportOrder.pick_up_company_id,
    pick_up_location_id: props.transportOrder.pick_up_location_id,
    pick_up_location_free_text: props.transportOrder.pick_up_location_free_text,
    pick_up_notes: props.transportOrder.pick_up_notes,
    pick_up_after_date: props.transportOrder.pick_up_after_date,
    delivery_company_id: props.transportOrder.delivery_company_id,
    delivery_location_id: props.transportOrder.delivery_location_id,
    delivery_location_free_text:
        props.transportOrder.delivery_location_free_text,
    delivery_notes: props.transportOrder.delivery_notes,
    deliver_before_date: props.transportOrder.deliver_before_date,
    planned_delivery_date: props.transportOrder.planned_delivery_date,
    total_transport_price: props.transportOrder.total_transport_price,

    transportInvoiceFiles: [],
    cmrWaybillFiles: [],
    files: [],

    transportables: props.selectedTransportables ?? [],
    transportableIds: props.selectedTransportableIds ?? [],
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

const save = async (only?: Array<string>) => {
    validate(form, transportOrdersFormRules);

    return new Promise<void>((resolve, reject) => {
        form.post(
            route("transport-orders.update", props.transportOrder.id as number),
            {
                preserveScroll: true,
                preserveState: true,
                forceFormData: true, // preserves all form data
                only: withFlash(only),
                onSuccess: () => {
                    form.reset(
                        "internal_remark",
                        "transportInvoiceFiles",
                        "cmrWaybillFiles",
                        "files"
                    );

                    resetOwnerId(form);

                    resolve();
                },
                onError: () => {
                    reject(new Error("Error, during update"));
                },
            }
        );
    });
};

const addTransportable = (id: number) => {
    const resultObject = {
        [id]: {
            transportable_id: id,
            location_id: null!,
            location_free_text: null!,
            price: null,
        },
    };

    form.transportables = { ...form.transportables, ...resultObject };
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

const transportableType = computed(() =>
    findEnumKeyByValue(TransportableType, form.vehicle_type)
);

const handleCmrWaybillUpload = async () => {
    await save();

    await changeStatus(updateStatusForm, TransportOrderStatus.Cmr_waybill);
};

const updateStatusForm = useForm<UpdateStatusForm>({
    _method: "patch",
    id: props.transportOrder.id,
    status: null!,
    route: "transport-orders",
});
</script>

<template>
    <Head :title="__('Transport Order')" />

    <AppLayout>
        <Header :text="__('Transport Order')" />

        <Stepper
            :transport-order="transportOrder"
            :selected-transportable-ids="selectedTransportableIds"
            :form-is-dirty="form.isDirty"
            :generated-pdf="files.generatedTransportRequestOrTransportOrderPdf"
            :cmr-waybill-files="files.cmrWaybillFiles"
        />

        <div
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
                        <div
                            v-if="
                                transportOrder.status ==
                                TransportOrderStatus.Concept
                            "
                            class="flex gap-1.5 min-w-[270px]"
                        >
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
                                        :item="item"
                                        :workflow-processes="workflowProcesses"
                                    />

                                    <Select
                                        v-if="
                                            form.transport_type !=
                                                TransportType.Other &&
                                            form.transportables[item.id]
                                        "
                                        :key="`${item.id}_select`"
                                        v-model="
                                            form.transportables[item.id]
                                                .location_id
                                        "
                                        :name="`transportables[${item.id}][location_id]`"
                                        :options="
                                            convertCollectionToMultiselectInTransportOrder(
                                                item
                                            )
                                        "
                                        :placeholder="
                                            form.transport_type ==
                                            TransportType.Inbound
                                                ? __('Pick Up Location Select')
                                                : __('Delivery Location Select')
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
                                        :name="`transportables[${item.id}][location_free_text]`"
                                        :placeholder="
                                            form.transport_type ==
                                            TransportType.Inbound
                                                ? __(
                                                      'Pick Up Location Free Text'
                                                  )
                                                : __(
                                                      'Delivery Location Free Text'
                                                  )
                                        "
                                        type="text"
                                    />
                                    <Input
                                        v-if="form.transportables[item.id]"
                                        v-model="
                                            form.transportables[item.id].price
                                        "
                                        :name="`transportables[${item.id}][price]`"
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
                                    :item="item"
                                    :workflow-processes="workflowProcesses"
                                />
                            </div>
                        </div>
                        <div v-else class="flex flex-col gap-1.5 min-w-[270px]">
                            <div class="flex gap-1.5">
                                <component
                                    :is="
                                        transportableTypeComponentMap[
                                            transportableType
                                        ]
                                    "
                                    v-if="transportableType"
                                    :item="item"
                                    :workflow-processes="workflowProcesses"
                                />

                                <Select
                                    v-if="
                                        form.transport_type !=
                                            TransportType.Other &&
                                        form.transportables[item.id]
                                    "
                                    :name="`transportables[${item.id}][location_id]`"
                                    :selected-option-value="
                                        form.transportables[item.id].location_id
                                    "
                                    :options="
                                        convertCollectionToMultiselectInTransportOrder(
                                            item
                                        )
                                    "
                                    :placeholder="
                                        form.transport_type ==
                                        TransportType.Inbound
                                            ? __('Pick Up Location Select')
                                            : __('Delivery Location Select')
                                    "
                                    class="w-full mb-3.5 sm:mb-0"
                                    :disabled="formDisabled"
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
                                    :name="`location_free_text[${item.id}]`"
                                    :placeholder="
                                        form.transport_type ==
                                        TransportType.Inbound
                                            ? __('Pick Up Location')
                                            : __('Delivery Location')
                                    "
                                    type="text"
                                    :disabled="formDisabled"
                                />
                                <Input
                                    v-if="form.transportables[item.id]"
                                    v-model="form.transportables[item.id].price"
                                    :name="`price[${item.id}]`"
                                    :placeholder="__('Transport price')"
                                    type="text"
                                    :disabled="formDisabled"
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
                    :form-disabled="formDisabled"
                    :transport-order="transportOrder"
                    :owner-props="{
                        mainCompanyUsers: mainCompanyUsers,
                        pendingOwnerships: pendingOwnerships,
                    }"
                    :transporters="transporters"
                    :own-logistics-addresses="ownLogisticsAddresses"
                    :pick-up-addresses="pickUpAddresses"
                    :delivery-addresses="deliveryAddresses"
                    :generated-pdf="[
                        ...files.generatedPickupAuthorizationPdf,
                        ...files.generatedTransportRequestOrTransportOrderPdf,
                        ...files.generatedStickervelPdf,
                    ]"
                    :companies="companies"
                    @form-updated="updateForm"
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
                            :files="files.transportInvoiceFiles"
                            :delete-disabled="formDisabled"
                            :text="__('Upload Transport Invoice files')"
                            text-classes="py-14"
                        />

                        <InputFile
                            id="crm-waybill-files"
                            v-model="form.cmrWaybillFiles"
                            :files="files.cmrWaybillFiles"
                            :delete-disabled="formDisabled"
                            :disabled="
                                transportOrder.status !=
                                TransportOrderStatus.Delivered
                            "
                            :text="__('Upload Cmr Waybill files')"
                            text-classes="py-14"
                            @change="handleCmrWaybillUpload"
                        />

                        <InputFile
                            id="files"
                            v-model="form.files"
                            :files="files.files"
                            :delete-disabled="formDisabled"
                            :text="__('Upload Additional files')"
                            text-classes="py-14"
                        />
                    </div>
                </div>

                <InternalRemarks
                    :internal-remarks="transportOrder.internal_remarks"
                    :form="form"
                    :main-company-roles="mainCompanyRoles"
                    :main-company-users="mainCompanyUsers"
                />

                <Communication
                    :mails="transportOrder.mails"
                    :files="[
                        ...files.generatedPickupAuthorizationPdf,
                        ...files.generatedTransportRequestOrTransportOrderPdf,
                        ...files.generatedStickervelPdf,
                    ]"
                />
            </div>
        </div>

        <ResetSaveButtons
            :processing="form.processing"
            @reset="form.reset()"
            @save="save"
        />
    </AppLayout>
</template>
