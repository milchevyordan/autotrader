<script setup lang="ts">
import { Link } from "@inertiajs/vue3";

import CreateOrViewWorkflow from "@/components/html/CreateOrViewWorkflow.vue";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import { Multiselect, Vehicle, WorkflowProcess } from "@/types";

defineProps<{
    item: Vehicle;
    workflowProcesses?: Multiselect<WorkflowProcess>;
}>();
</script>

<template>
    <Link
        v-if="$can('edit-vehicle') && !item.deleted_at"
        :href="route('vehicles.edit', item.id)"
        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition inline-flex items-center"
    >
        <IconPencilSquare classes="w-4 h-4 text-slate-600" />
    </Link>

    <CreateOrViewWorkflow
        :vehicle="item"
        type="App\Models\Vehicle"
        :workflow-processes="workflowProcesses"
    />
</template>
