<script setup lang="ts">
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { ref } from "vue";

import Header from "@/components/Header.vue";
import Input from "@/components/html/Input.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import RadioButtonToggle from "@/components/html/RadioButtonToggle.vue";
import Modal from "@/components/Modal.vue";
import Select from "@/components/Select.vue";
import {ExportType} from "@/data-table/enums/ExportType";
import Table from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import { ItemType } from "@/enums/ItemType";
import { UnitType } from "@/enums/UnitType";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import IconTrash from "@/icons/Trash.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { baseFormRules } from "@/rules/base-form-rules";
import { itemFormRules } from "@/rules/item-form-rules";
import { Item, ItemForm } from "@/types";
import {
    dateTimeToLocaleString,
    findEnumKeyByValue,
    formatPriceOnBlur,
    formatPriceOnFocus,
} from "@/utils.js";
import { validate } from "@/validations";
import { vatPercentage } from "@/vat-percentage";

defineProps<{
    dataTable: DataTable<Item>;
}>();

const updateRules = {
    id: {
        required: true,
        type: "number",
    },
    ...itemFormRules,
};

const modelDefaults: ItemForm = {
    id: null!,
    pivot: null!,
    unit_type: UnitType.Pcs,
    type: null!,
    is_vat: true,
    vat_percentage: usePage().props?.auth.company.vat_percentage,
    shortcode: null!,
    description: null!,
    purchase_price: null!,
    sale_price: null!,
};

const showCreateModal = ref(false);
const showUpdateModal = ref(false);
const showDeleteModal = ref(false);

const openCreateModal = () => {
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    storeForm.reset();
};

const closeUpdateModal = () => {
    showUpdateModal.value = false;
    updateForm.reset();
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deleteForm.reset();
};

const showDeleteForm = (id: number) => {
    showDeleteModal.value = true;
    deleteForm.id = id;
};

const showEditForm = (item: ItemForm) => {
    updateForm.id = item.id;
    updateForm.unit_type = item.unit_type;
    updateForm.type = item.type;
    updateForm.is_vat = item.is_vat;
    updateForm.vat_percentage = item.vat_percentage;
    updateForm.shortcode = item.shortcode;
    updateForm.description = item.description;
    updateForm.purchase_price = item.purchase_price;
    updateForm.sale_price = item.sale_price;

    showUpdateModal.value = true;
};

const storeForm = useForm<ItemForm>(modelDefaults);
const updateForm = useForm<ItemForm>(modelDefaults);
const deleteForm = useForm<ItemForm>(modelDefaults);

const store = () => {
    validate(storeForm, itemFormRules);

    storeForm.post(route("items.store"), {
        preserveScroll: true,
    });
    closeCreateModal();
};

const update = () => {
    validate(updateForm, updateRules);

    updateForm.put(route("items.update", updateForm.id as number), {
        preserveScroll: true,
        onSuccess: () => {
            closeUpdateModal();
            updateForm.reset();
        },
        onError: () => {},
    });
};

const handleDelete = () => {
    validate(deleteForm, baseFormRules);

    deleteForm.delete(route("items.destroy", deleteForm.id as number), {
        preserveScroll: true,
    });
    closeDeleteModal();
};
</script>

<template>
    <Head :title="__('Item')" />

    <AppLayout>
        <Header :text="__('Items')" />

        <Table
            :data-table="dataTable"
            :per-page-options="[5, 10, 15, 20, 50]"
            :global-search="true"
            :advanced-filters="false"
            :show-trashed="true"
            :export-types="[ExportType.Csv]"
        >
            <template #additionalContent>
                <div class="w-full flex gap-2">
                    <button
                        v-if="$can('create-item')"
                        class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                        @click="openCreateModal"
                    >
                        {{ __("Create") }} {{ __("Item") }}
                    </button>
                </div>
            </template>

            <template #cell(type)="{ value, item }">
                <div class="flex gap-1.5">
                    {{ findEnumKeyByValue(ItemType, value) }}
                </div>
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
                    <button
                        v-if="$can('edit-item')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        :title="__('Edit item')"
                        @click="showEditForm(item)"
                    >
                        <IconPencilSquare classes="w-4 h-4 text-[#909090]" />
                    </button>

                    <button
                        v-if="$can('delete-item')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        :title="__('Delete item')"
                        @click="showDeleteForm(item.id)"
                    >
                        <IconTrash classes="w-4 h-4 text-[#909090]" />
                    </button>
                </div>
            </template>
        </Table>

        <Modal :show="showCreateModal" @close="closeCreateModal">
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{ __("Create Item") }}
            </div>

            <div class="p-3.5">
                <label for="create_unit_type">
                    {{ __("Unit Type") }}
                    <span class="text-red-500"> *</span>
                </label>
                <Select
                    id="create_unit_type"
                    v-model="storeForm.unit_type"
                    :name="'unit_type'"
                    :options="UnitType"
                    :placeholder="__('Unit Type')"
                    class="w-full mb-3.5"
                />

                <label for="create_type">
                    {{ __("Type") }}
                    <span class="text-red-500"> *</span>
                </label>
                <Select
                    id="create_type"
                    v-model="storeForm.type"
                    :name="'type'"
                    :options="ItemType"
                    :placeholder="__('Type')"
                    class="w-full mb-3.5"
                />

                <label for="create_shortcode">
                    {{ __("Shortcode") }}
                    <span class="text-red-500"> *</span>
                </label>
                <Input
                    id="create_shortcode"
                    v-model="storeForm.shortcode"
                    :name="'shortcode'"
                    type="text"
                    :placeholder="__('Shortcode')"
                    class="mb-3.5"
                />

                <label for="create_description">
                    {{ __("Description") }}
                </label>
                <Input
                    id="create_description"
                    v-model="storeForm.description"
                    :name="'description'"
                    type="text"
                    :placeholder="__('Description')"
                    class="mb-3.5"
                />

                <label for="create_vat_percentage">VAT %</label>
                <Select
                    id="create_vat_percentage"
                    :key="storeForm.vat_percentage"
                    v-model="storeForm.vat_percentage"
                    :name="'vat_percentage'"
                    :options="vatPercentage"
                    placeholder="VAT"
                    class="w-full mb-3.5"
                />

                <label for="create_is_vat">{{ __("Pricing Type") }}</label>
                <RadioButtonToggle
                    v-model="storeForm.is_vat"
                    name="is_vat"
                    :left-button-label="__('VAT')"
                    :right-button-label="__('Margin')"
                />

                <label for="create_purchase_price">
                    {{ __("Purchase Price") }}
                </label>
                <Input
                    id="create_purchase_price"
                    v-model="storeForm.purchase_price"
                    :name="'purchase_price'"
                    type="text"
                    class="mb-3.5"
                    :placeholder="__('Standard Purchase Price')"
                    @blur="formatPriceOnBlur(storeForm, 'purchase_price')"
                    @focus="formatPriceOnFocus(storeForm, 'purchase_price')"
                />

                <label for="create_sale_price">
                    {{ __("Sale Price") }}
                </label>
                <Input
                    id="create_sale_price"
                    v-model="storeForm.sale_price"
                    :name="'sale_price'"
                    type="text"
                    class="mb-3.5"
                    :placeholder="__('Sale Price')"
                    @blur="formatPriceOnBlur(storeForm, 'sale_price')"
                    @focus="formatPriceOnFocus(storeForm, 'sale_price')"
                />
            </div>

            <ModalSaveButtons
                :processing="storeForm.processing"
                :save-text="__('Add')"
                @cancel="closeCreateModal"
                @save="store"
            />
        </Modal>

        <Modal :show="showUpdateModal" @close="closeUpdateModal">
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{ __("Edit Item") }}
            </div>

            <div class="p-3.5">
                <label for="update_unit_type">
                    {{ __("Unit Type") }}
                    <span class="text-red-500"> *</span>
                </label>
                <Select
                    id="update_unit_type"
                    v-model="updateForm.unit_type"
                    :name="'unit_type'"
                    :options="UnitType"
                    :placeholder="__('Unit Type')"
                    class="w-full mb-3.5"
                />

                <label for="update_type">
                    {{ __("Type") }}
                    <span class="text-red-500"> *</span>
                </label>
                <Select
                    id="update_type"
                    v-model="updateForm.type"
                    :name="'type'"
                    :options="ItemType"
                    :placeholder="__('Type')"
                    class="w-full mb-3.5"
                />

                <label for="update_shortcode">
                    {{ __("Shortcode") }}
                    <span class="text-red-500"> *</span>
                </label>
                <Input
                    id="update_shortcode"
                    v-model="updateForm.shortcode"
                    :name="'shortcode'"
                    type="text"
                    :placeholder="__('Shortcode')"
                    class="mb-3.5"
                />

                <label for="update_description">
                    {{ __("Description") }}
                </label>
                <Input
                    id="update_description"
                    v-model="updateForm.description"
                    :name="'description'"
                    type="text"
                    :placeholder="__('Description')"
                    class="mb-3.5"
                />

                <label for="update_vat_percentage">VAT %</label>
                <Select
                    id="update_vat_percentage"
                    :key="updateForm.vat_percentage"
                    v-model="updateForm.vat_percentage"
                    :name="'vat_percentage'"
                    :options="vatPercentage"
                    placeholder="VAT"
                    class="w-full mb-3.5"
                />

                <label for="update_is_vat">{{ __("Pricing Type") }}</label>
                <RadioButtonToggle
                    v-model="updateForm.is_vat"
                    name="is_vat"
                    :left-button-label="__('VAT')"
                    :right-button-label="__('Margin')"
                />

                <label for="update_purchase_price">
                    {{ __("Purchase Price") }}
                </label>
                <Input
                    id="update_purchase_price"
                    v-model="updateForm.purchase_price"
                    :name="'purchase_price'"
                    type="text"
                    class="mb-3.5"
                    :placeholder="__('Purchase Price')"
                    @blur="formatPriceOnBlur(updateForm, 'purchase_price')"
                    @focus="formatPriceOnFocus(updateForm, 'purchase_price')"
                />

                <label for="update_purchase_price">
                    {{ __("Sale Price") }}
                </label>
                <Input
                    id="update_sale_price"
                    v-model="updateForm.sale_price"
                    :name="'sale_price'"
                    type="text"
                    class="mb-3.5"
                    :placeholder="__('Sale Price')"
                    @blur="formatPriceOnBlur(updateForm, 'sale_price')"
                    @focus="formatPriceOnFocus(updateForm, 'sale_price')"
                />
            </div>

            <ModalSaveButtons
                :processing="updateForm.processing"
                :save-text="__('Update')"
                @cancel="closeUpdateModal"
                @save="update"
            />
        </Modal>

        <Modal :show="showDeleteModal" @close="closeDeleteModal">
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium break-words"
            >
                {{ __("Delete item #") + deleteForm?.id }} ?
            </div>

            <ModalSaveButtons
                :processing="deleteForm.processing"
                :save-text="__('Delete')"
                @cancel="closeDeleteModal"
                @save="handleDelete"
            />
        </Modal>
    </AppLayout>
</template>
