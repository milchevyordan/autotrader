<script setup lang="ts">
import { Head, useForm, Link, usePage } from "@inertiajs/vue3";
import { onMounted, ref, watch } from "vue";

import Accordion from "@/components/Accordion.vue";
import Header from "@/components/Header.vue";
import InputFile from "@/components/html/InputFile.vue";
import InternalRemarks from "@/components/html/InternalRemarks.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import GeneralInformation from "@/components/pre-orders/GeneralInformation.vue";
import SummaryFinancialInformation from "@/components/pre-orders/SummaryFinancialInformation.vue";
import Table from "@/data-table/Table.vue";
import { DataTable, Multiselect } from "@/data-table/types";
import { Currency } from "@/enums/Currency";
import { SupplierOrIntermediary } from "@/enums/SupplierOrIntermediary";
import IconMinus from "@/icons/Minus.vue";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import IconPlus from "@/icons/Plus.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { preOrdersFormRules } from "@/rules/pre-orders-form-rules";
import { Company, PreOrderForm, User } from "@/types";
import { PreOrderVehicle, Role } from "@/types";
import { calculatePreOrderPrices, dateTimeToLocaleString } from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    companies: Multiselect<Company>;
    queryVehicleId: number;
    defaultCurrencies: Record<number, Currency>;
    dataTable: DataTable<PreOrderVehicle>;
    suppliers?: Multiselect<User>;
    intermediaries?: Multiselect<User>;
    purchasers: Multiselect<User>;
    mainCompanyRoles?: Multiselect<Role>;
    mainCompanyUsers: Multiselect<User>;
}>();

const preOrderVehicle = ref<PreOrderVehicle>(null!);

const tableData = ref<DataTable<PreOrderVehicle>>(props.dataTable);

const form = useForm<PreOrderForm>({
    id: null!,
    owner_id: usePage().props.auth.user.id,
    status: null!,
    supplier_company_id: null!,
    supplier_id: null!,
    intermediary_company_id: null!,
    intermediary_id: null!,
    purchaser_id: null!,
    pre_order_vehicle_id: null!,
    type: null!,
    document_from_type: SupplierOrIntermediary.Supplier,
    currency_po: null!,
    vat_percentage: usePage().props?.auth.company.vat_percentage,
    down_payment_amount: null!,
    contact_notes: null!,
    transport_included: false,
    vat_deposit: false,
    amount_of_vehicles: 1,
    down_payment: false,
    total_purchase_price: null!,
    total_purchase_price_eur: null!,
    total_fee_intermediate_supplier: null!,
    total_purchase_price_exclude_vat: null!,
    total_vat: null!,
    total_bpm: null!,
    total_purchase_price_include_vat: null!,
    currency_rate: null!,

    viesFiles: [],
    creditCheckFiles: [],
    contractUnsignedFiles: [],
    contractSignedFiles: [],
    files: [],

    internal_remark_user_ids: [],
    internal_remark_role_ids: [],
    internal_remark: null!,
});

watch(
    () => props.dataTable,
    (newValue) => {
        tableData.value.data = newValue.data;
        tableData.value.paginator = newValue.paginator;
    }
);

onMounted(() => {
    if (props.queryVehicleId) {
        const selectedVehicle = tableData.value.data.find(
            (preOrderVehicle: PreOrderVehicle) =>
                preOrderVehicle.id == Number(props.queryVehicleId)
        );
        if (selectedVehicle) {
            addVehicle(selectedVehicle);
        }
    }
});

const addVehicle = (item: PreOrderVehicle) => {
    const clonedItem = JSON.parse(JSON.stringify(item)); // Ensure it's serializable
    preOrderVehicle.value = clonedItem;
    form.pre_order_vehicle_id = clonedItem.id;
    tableData.value.data = [clonedItem] as PreOrderVehicle[];
    calculatePreOrderPrices(form, preOrderVehicle?.value.calculation);
};

const removeVehicle = () => {
    preOrderVehicle.value = null!;
    form.pre_order_vehicle_id = null!;
    tableData.value.data = props.dataTable.data as PreOrderVehicle[];
    tableData.value.paginator = props.dataTable.paginator;
    calculatePreOrderPrices(form, preOrderVehicle?.value.calculation);
};

const save = () => {
    validate(form, preOrdersFormRules);

    form.post(route("pre-orders.store"), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {},
    });
};
</script>

<template>
    <Head :title="__('Pre Order')" />

    <AppLayout>
        <Header :text="__('Pre Order')" />

        <div
            class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
        >
            <Accordion>
                <template #head>
                    <div class="font-semibold text-xl sm:text-2xl mb-4">
                        {{ __("Vehicles") }}
                    </div>
                </template>

                <Table
                    :data-table="tableData"
                    :per-page-options="[5, 10, 15, 20, 50]"
                    :global-search="true"
                    :advanced-filters="true"
                    :selected-row-indexes="[form.pre_order_vehicle_id]"
                    :selected-row-column="'id'"
                >
                    <template #cell(make.name)="{ value, item }">
                        {{ item.make?.name }}
                    </template>

                    <template #cell(vehicleModel.name)="{ value, item }">
                        {{ item.vehicle_model?.name }}
                    </template>

                    <template #cell(engine.name)="{ value, item }">
                        {{ item.engine?.name }}
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
                        <div class="flex gap-1.5">
                            <div
                                v-if="form.pre_order_vehicle_id == item.id"
                                class="flex gap-1.5"
                            >
                                <button
                                    class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                    @click="removeVehicle()"
                                >
                                    <IconMinus
                                        classes="w-4 h-4 text-slate-600"
                                    />
                                </button>
                            </div>
                            <div v-else>
                                <button
                                    class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                    @click="addVehicle(item)"
                                >
                                    <IconPlus
                                        classes="w-4 h-4 text-slate-600"
                                    />
                                </button>
                            </div>
                            <Link
                                v-if="$can('edit-pre-order-vehicle')"
                                :href="
                                    route('pre-order-vehicles.edit', item.id)
                                "
                                class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                            >
                                <IconPencilSquare
                                    classes="w-4 h-4 text-slate-600"
                                />
                            </Link>
                        </div>
                    </template>
                </Table>
            </Accordion>
        </div>

        <div class="flex justify-center">
            <div class="w-full max-w-[1721px]">
                <GeneralInformation
                    :form="form"
                    :default-currencies="defaultCurrencies"
                    :companies="companies"
                    :owner-props="{
                        mainCompanyUsers: mainCompanyUsers,
                    }"
                    :suppliers="suppliers"
                    :intermediaries="intermediaries"
                    :purchasers="purchasers"
                    @calculate-prices="
                        calculatePreOrderPrices(
                            form,
                            preOrderVehicle?.calculation
                        )
                    "
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
                            id="upload-vies"
                            v-model="form.viesFiles"
                            :files="[]"
                            :text="__('Upload VIES files')"
                            text-classes="py-14"
                        />

                        <InputFile
                            id="credit-check-supplier"
                            v-model="form.creditCheckFiles"
                            :files="[]"
                            :text="__('Upload credit check supplier')"
                            text-classes="py-14"
                        />

                        <InputFile
                            id="order-contract-unsigned"
                            v-model="form.contractUnsignedFiles"
                            :files="[]"
                            :text="__('Upload Pre-order contract unsigned')"
                            text-classes="py-14"
                        />

                        <InputFile
                            id="order-contract-signed"
                            :files="[]"
                            :disabled="true"
                            :text="__('Upload Pre-order contract signed')"
                            text-classes="py-14"
                        />

                        <InputFile
                            id="files"
                            v-model="form.files"
                            :files="[]"
                            :text="__('Pre-order files')"
                            text-classes="py-14"
                        />
                    </div>
                </div>

                <SummaryFinancialInformation
                    :pre-order-vehicle="preOrderVehicle"
                    :form="form"
                    @calculate-prices="
                        calculatePreOrderPrices(
                            form,
                            preOrderVehicle?.calculation
                        )
                    "
                />

                <InternalRemarks
                    :form="form"
                    :main-company-roles="mainCompanyRoles"
                    :main-company-users="mainCompanyUsers"
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
