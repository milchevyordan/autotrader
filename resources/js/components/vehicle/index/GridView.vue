<script setup lang="ts">
import { Link, router, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import { watch } from "vue";

import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Modal from "@/components/Modal.vue";
import Pagination from "@/data-table/components/Pagination.vue";
import { Paginator } from "@/data-table/types";
import { ExteriorColour } from "@/enums/ExteriorColour";
import { FuelType } from "@/enums/FuelType";
import { InteriorColour } from "@/enums/InteriorColour";
import { VehicleBody } from "@/enums/VehicleBody";
import IconBottomArrowAccordion from "@/icons/BottomArrowAccordion.vue";
import IconBuildingFigma from "@/icons/BuildingFigma.vue";
import IconCalendarFigma from "@/icons/CalendarFigma.vue";
import IconCarFigma from "@/icons/CarFigma.vue";
import IconCarFront from "@/icons/CarFront.vue";
import IconDocument from "@/icons/Document.vue";
import IconEngine from "@/icons/Engine.vue";
import IconIdCard from "@/icons/IdCard.vue";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import IconTrash from "@/icons/Trash.vue";
import { baseFormRules } from "@/rules/base-form-rules";
import { VehicleGroup } from "@/types";
import colorClasses, {
    dateToLocaleString,
    findEnumKeyByValue,
    getImage,
    replaceEnumUnderscores,
} from "@/utils.js";
import { validate } from "@/validations";

const emit = defineEmits(["updateForm", "deleteForm"]);

const props = defineProps<{
    paginator: Paginator;
    vehicleGroups: VehicleGroup[];
}>();

watch(
    () => props.paginator,
    () => {
        loadGroupIds = [];
        expandedGroupIds.value = [];
    }
);

const deleteModalShown = ref(false);

const deleteForm = useForm<{
    id: null | number;
}>({
    id: 0,
});

const showDeleteModal = (id: number) => {
    deleteModalShown.value = true;
    deleteForm.id = id;
};

const closeDeleteModal = () => {
    deleteModalShown.value = false;
    deleteForm.reset();
};

const handleDelete = () => {
    validate(deleteForm, baseFormRules);

    deleteForm.delete(route("vehicles.destroy", deleteForm.id as number), {
        preserveScroll: true,
    });
    closeDeleteModal();
};

const handleButtonClick = (vehicleGroup: VehicleGroup, type: string) => {
    if (type == "update") {
        emit("updateForm", vehicleGroup.id, vehicleGroup.name);
    } else {
        emit("deleteForm", vehicleGroup.id, vehicleGroup.name);
    }
};

const expandedGroupIds = ref<number[]>([]);
let loadGroupIds: number[] = [];

const toggleExpanded = (groupId: number) => {
    const index = expandedGroupIds.value.indexOf(groupId);
    expandedGroupIds.value =
        index === -1
            ? [...expandedGroupIds.value, groupId]
            : expandedGroupIds.value.filter((x) => x !== groupId);

    if (!loadGroupIds.includes(groupId)) {
        loadGroupIds.push(groupId);

        loadAll();
    }
};

const loadAll = () => {
    router.reload({
        data: {
            loadGroupIds: loadGroupIds,
        },
        only: ["vehicleGroups"],
    });
};

const validCleanColors = ["Black", "White"];
</script>

<template>
    <div
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-1 justify-evenly xl:block gap-4 xl:space-y-4 pb-4"
    >
        <div
            v-for="(vehicleGroup, groupIndex) in vehicleGroups"
            :key="groupIndex"
            class="py-1"
        >
            <div
                class="flex items-center justify-between py-2.5 bg-white shadow-md border rounded-t-lg border-[#E9E7E7] px-4"
            >
                <div class="flex items-center gap-2">
                    <div
                        class="p-2 rounded-md element-center bg-[#008FE3] text-white text-sm font-semibold"
                    >
                        {{ vehicleGroup.name }}
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        v-if="$can('edit-vehicle-group')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        @click="handleButtonClick(vehicleGroup, 'update')"
                    >
                        <IconPencilSquare classes="w-5 h-5 text-[#909090]" />
                    </button>
                    <button
                        v-if="$can('delete-vehicle-group')"
                        class="hidden xl:block border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        @click="handleButtonClick(vehicleGroup, 'delete')"
                    >
                        <IconTrash classes="w-5 h-5 text-[#909090]" />
                    </button>
                    <IconBottomArrowAccordion
                        v-show="vehicleGroup.vehicles?.length > 1"
                        class="w-5 h-5 transition-all duration-500 text-gray-500 cursor-pointer"
                        :class="{
                            'rotate-180': expandedGroupIds.includes(
                                vehicleGroup.id
                            ),
                        }"
                        @click="toggleExpanded(vehicleGroup.id)"
                    />
                </div>
            </div>

            <div v-if="vehicleGroup.vehicles?.length">
                <div
                    v-for="(vehicle, index) in vehicleGroup.vehicles"
                    v-show="
                        expandedGroupIds.includes(vehicleGroup.id) ||
                        index === 0
                    "
                    :key="index"
                    class="min-w-fit max-w-full xl:max-w-none xl:w-full h-fit bg-white border border-t-0 border-[#E9E7E7] p-4 xl:flex"
                >
                    <img
                        :src="getImage(vehicle.image_path)"
                        class="w-full h-56 xl:w-32 xl:h-20 object-cover rounded-md"
                        alt="vehicle image"
                    />

                    <div class="xl:w-[42%] 2xl:w-[38%] xl:ml-5">
                        <div class="xl:flex items-center text-sm">
                            <div
                                class="mt-2 xl:mt-0 text-base xl:text-sm font-medium xl:w-[55%] 2xl:w-[46%]"
                            >
                                {{ vehicle.make?.name }}

                                {{ vehicle.vehicle_model?.name }}

                                <span class="text-slate-500">
                                    {{ vehicle.variant?.name }}
                                </span>
                            </div>

                            <div class="flex items-center mt-2 xl:mt-0">
                                <div
                                    v-if="
                                        findEnumKeyByValue(
                                            ExteriorColour,
                                            vehicle.specific_exterior_color
                                        ) !== undefined
                                    "
                                    :class="['w-6', 'h-6', colorClasses[findEnumKeyByValue(ExteriorColour, vehicle.specific_exterior_color) as keyof typeof ExteriorColour], 'shadow-lg', 'rounded-l']"
                                />

                                <div
                                    v-if="
                                        findEnumKeyByValue(
                                            InteriorColour,
                                            vehicle.specific_interior_color
                                        ) !== undefined
                                    "
                                    :class="`w-6 h-6 ${colorClasses[findEnumKeyByValue(InteriorColour, vehicle.specific_interior_color) as keyof typeof InteriorColour]} shadow-lg rounded-r`"
                                />

                                <div
                                    :class="
                                        findEnumKeyByValue(
                                            VehicleBody,
                                            vehicle?.body
                                        ) === 'Hatchback'
                                            ? 'text-[#E50000] border-[#E50000]'
                                            : findEnumKeyByValue(
                                                  VehicleBody,
                                                  vehicle?.body
                                              ) === 'Sedan'
                                            ? 'text-[green] border-[green]'
                                            : findEnumKeyByValue(
                                                  VehicleBody,
                                                  vehicle?.body
                                              ) === 'SUV'
                                            ? 'text-amber-500 border-amber-500'
                                            : findEnumKeyByValue(
                                                  VehicleBody,
                                                  vehicle?.body
                                              ) === 'Van'
                                            ? 'text-blue-500 border-blue-500'
                                            : findEnumKeyByValue(
                                                  VehicleBody,
                                                  vehicle?.body
                                              ) === 'Station'
                                            ? 'text-pink-500 border-pink-500'
                                            : 'text-[#9747FF] border-[#9747FF]'
                                    "
                                    class="border py-0.5 px-2 rounded ml-4 xl:ml-5 2xl:ml-6"
                                >
                                    {{
                                        findEnumKeyByValue(
                                            VehicleBody,
                                            vehicle.body
                                        )
                                    }}
                                </div>
                            </div>
                        </div>

                        <div
                            class="grid grid-cols-2 xl:grid-cols-3 gap-1 md:gap-0 mt-4 xl:mt-2"
                        >
                            <div
                                class="flex gap-1 text-sm items-center font-medium text-slate-700"
                            >
                                <IconEngine
                                    classes="w-4 h-4 md:w-5 md:h-5 flex-shrink-0 text-[#909090]"
                                />
                                {{ vehicle.engine?.name }}
                            </div>

                            <div
                                class="flex gap-1 text-sm items-center xl:-ml-3 2xl:-ml-6 font-medium text-slate-700"
                            >
                                <IconCarFront classes="flex-shrink-0" />
                                {{ vehicle.kilometers }} km.
                            </div>

                            <div
                                class="flex gap-1 text-sm items-center xl:-ml-3 2xl:-ml-10 font-medium text-slate-700"
                            >
                                <IconIdCard class="flex-shrink-0" />
                                {{ vehicle.creator.full_name }}
                            </div>

                            <div
                                class="flex gap-1 text-sm items-center font-medium text-slate-700"
                            >
                                <IconCarFigma
                                    classes="w-4 h-4 md:w-5 md:h-5 flex-shrink-0 text-[#909090]"
                                />
                                {{
                                    findEnumKeyByValue(
                                        VehicleBody,
                                        vehicle.body
                                    )
                                }}
                            </div>

                            <div
                                class="flex gap-1 text-sm items-center xl:-ml-3 2xl:-ml-6 font-medium text-slate-700"
                            >
                                <IconBuildingFigma
                                    classes="w-4 h-4 md:w-5 md:h-5 flex-shrink-0 text-[#909090]"
                                />
                                {{
                                    replaceEnumUnderscores(
                                        findEnumKeyByValue(
                                            FuelType,
                                            vehicle.fuel
                                        )
                                    )
                                }}
                            </div>

                            <div
                                class="flex gap-1 text-sm items-center xl:-ml-3 2xl:-ml-10 font-medium text-slate-700"
                            >
                                <IconCalendarFigma class="flex-shrink-0" />
                                {{
                                    dateToLocaleString(
                                        vehicle.first_registration_date
                                    )
                                }}
                            </div>
                        </div>
                    </div>

                    <div
                        class="w-full xl:w-[34%] 2xl:w-[30%] mt-3 xl:mt-2.5 2xl:mt-3 text-center text-[#676666] xl:text-sm"
                    >
                        {{ vehicle.identification_code }}
                    </div>

                    <div
                        class="xl:w-[26%] 2xl:w-[24%] items-center xl:mr-6 gap-2 mt-3"
                    >
                        <div
                            class="grid grid-cols-3 xl:flex items-center justify-center xl:justify-end gap-2"
                        >
                            <Link
                                v-if="$can('edit-vehicle')"
                                class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                :href="route('vehicles.edit', vehicle.id)"
                            >
                                <IconPencilSquare
                                    classes="w-5 h-5 text-[#909090]"
                                />
                            </Link>

                            <Link
                                v-if="$can('view-workflow') && vehicle.workflow"
                                :href="
                                    route('workflows.show', vehicle.workflow.id)
                                "
                                class="border border-[#008FE3] bg-[#008FE3] text-white rounded-md p-1 active:scale-90 transition"
                            >
                                <IconDocument classes="w-5 h-5" />
                            </Link>

                            <button
                                v-if="$can('delete-vehicle')"
                                :title="__('Delete vehicle')"
                                class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                @click="showDeleteModal(vehicle.id)"
                            >
                                <IconTrash classes="w-5 h-5 text-[#909090]" />
                            </button>
                        </div>
                    </div>
                </div>
                <div
                    v-show="!expandedGroupIds.includes(vehicleGroup.id)"
                    class="w-full font-semibold py-2 text-center text-gray-500 bg-white border-b border-[#E9E7E7] cursor-pointer"
                    @click="toggleExpanded(vehicleGroup.id)"
                >
                    {{ __("See all") }}...
                </div>
            </div>
            <div
                v-else
                class="w-full text-lg font-semibold py-2 text-center text-gray-500 bg-white border-b border-[#E9E7E7]"
            >
                {{ __("No found data") }}
            </div>
        </div>

        <div>
            <Pagination
                v-if="vehicleGroups.length > 1 || paginator.currentPage > 1"
                :paginator="paginator"
                :prop-name="'vehicleGroups'"
            />
        </div>
    </div>

    <Modal :show="deleteModalShown" @close="closeDeleteModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Delete Vehicle #") + deleteForm?.id }}
        </div>

        <ModalSaveButtons
            :processing="deleteForm.processing"
            :save-text="__('Delete')"
            @cancel="closeDeleteModal"
            @save="handleDelete"
        />
    </Modal>
</template>
