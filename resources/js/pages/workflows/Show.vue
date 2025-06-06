<script setup lang="ts">
import { Head } from "@inertiajs/vue3";

import Header from "@/components/Header.vue";
import Gallery from "@/components/html/Gallery.vue";
import CreateModules from "@/components/vehicle/create-edit/CreateModules.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import Document from "@/pages/workflows/partials/Document.vue";
import Modules from "@/pages/workflows/partials/Modules.vue";
import Process from "@/pages/workflows/partials/Process.vue";
import MainSectionServiceVehicle from "@/pages/workflows/partials/service-vehicle/MainSection.vue";
import MainSectionVehicle from "@/pages/workflows/partials/vehicle/MainSection.vue";
import { ServiceVehicle, Vehicle, Workflow } from "@/types";

const props = defineProps<{
    workflow: Workflow;
    transferVehicleToken?: string;
}>();

const title =
    props.workflow.vehicle.make?.name +
    " " +
    props.workflow.vehicle.vehicle_model?.name;
</script>

<template>
    <Head :title="title" />

    <AppLayout>
        <Header :text="__('Workflow')" />

        <MainSectionVehicle
            v-if="workflow.vehicleableType == 'App\\Models\\Vehicle'"
            :data="(workflow.vehicle as Vehicle)"
        />

        <MainSectionServiceVehicle
            v-else-if="
                workflow.vehicleableType == 'App\\Models\\ServiceVehicle'
            "
            :data="(workflow.vehicle as ServiceVehicle)"
        />

        <Document :workflow="workflow" />

        <Process :workflow="workflow" />

        <Modules
            :purchase-order="(workflow.vehicle as Vehicle).purchase_order"
            :sales-order="(workflow.vehicle as Vehicle).sales_order"
            :service-order="workflow.vehicle.service_order"
            :transport-orders="workflow.vehicle.transport_orders"
            :work-order="workflow.vehicle.work_order"
            :documents="workflow.vehicle.documents"
            :quotes="(workflow.vehicle as Vehicle).quotes"
        />

        <div
            v-if="workflow.vehicleableType == 'App\\Models\\Vehicle'"
            class="grid sm:grid-cols-1 xl:grid-cols-2 gap-5 gap-y-0"
        >
            <Gallery
                :title="__('Internal Images')"
                :images="workflow.images?.internalImages"
            />

            <Gallery
                :title="__('External Images')"
                :images="workflow.images?.externalImages"
            />

            <CreateModules
                :vehicle="workflow.vehicle"
                :purchase-order="(workflow.vehicle as Vehicle).purchase_order"
                :sales-order="(workflow.vehicle as Vehicle).sales_order"
                :work-order="workflow.vehicle.work_order"
                :transport-orders="workflow.vehicle.transport_orders"
                :documents="workflow.vehicle.documents"
                :disabled-workflow-create="true"
                :transfer-vehicle-token="transferVehicleToken"
            />
        </div>

        <Gallery
            v-else
            :title="__('Service Order Images')"
            class="gap-5 gap-y-0"
            :images="workflow.vehicle?.service_order?.images"
        />
    </AppLayout>
</template>
