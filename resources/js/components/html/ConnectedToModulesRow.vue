<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";

import { ConnectedToModulesType } from "@/enums/ConnectedToModulesType";
import { Enum } from "@/types";

const props = withDefaults(
    defineProps<{
        title: string;
        resource: any;
        href: string;
        hasPermission: boolean;
        type: Enum<typeof ConnectedToModulesType>;
        method?: string;
    }>(),
    {
        method: "edit",
    }
);

const order = computed(() => {
    if (!props.resource) {
        return false;
    }

    switch (props.type) {
        case ConnectedToModulesType.Single_record:
        case ConnectedToModulesType.Collection:
            return props.resource;
        case ConnectedToModulesType.Collection_first:
            return props.resource.length > 0 ? props.resource[0] : false;

        default:
            return false;
    }
});
</script>

<template>
    <div v-if="order && type === ConnectedToModulesType.Collection">
        <div
            v-for="(item, index) in order"
            :key="index"
            class="flex items-center justify-between py-3 border-b border-[#E9E7E7]"
        >
            <div class="text-[#6D6D73]">
                {{ title }}
            </div>
            <div>
                <Link
                    v-if="hasPermission && !item.deleted_at"
                    :href="route(href + '.' + method, item.id)"
                    class="hover:opacity-80 transition"
                >
                    {{ __("Go to") }} {{ title }} #{{ item.id }}
                </Link>
                <div v-else>{{ title }} #{{ item.id }}</div>
            </div>
        </div>
    </div>

    <div
        v-else-if="order"
        class="flex items-center justify-between py-3 border-b border-[#E9E7E7]"
    >
        <div class="text-[#6D6D73]">
            {{ title }}
        </div>
        <div>
            <Link
                v-if="hasPermission && !order.deleted_at"
                :href="route(href + '.' + method, order.id)"
                class="hover:opacity-80 transition"
            >
                {{ __("Go to") }} {{ title }} #{{ order.id }}
            </Link>
            <div v-else>{{ title }} #{{ order.id }}</div>
        </div>
    </div>
</template>
