<script setup lang="ts">
import { QuoteInvitationStatus } from "@/enums/QuoteInvitationStatus";

defineProps<{
    status: null | number;
}>();
</script>

<template>
    <div
        v-if="!status"
        class="w-auto rounded-lg shadow-lg fixed bg-amber-500 text-white z-50 top-20 md:top-2.5 right-2 sm:right-5 md:right-4 p-2 element-center gap-2"
    >
        {{ __("No action taken") }}
    </div>

    <div
        v-else-if="status == QuoteInvitationStatus.Closed"
        class="w-auto rounded-lg shadow-lg fixed bg-neutral-300 z-50 top-20 md:top-2.5 right-2 sm:right-5 md:right-4 p-2 element-center gap-2"
    >
        {{ __("The quote invitation has been closed") }}
    </div>

    <div
        v-else-if="status == QuoteInvitationStatus.Rejected"
        class="w-auto rounded-lg shadow-lg fixed bg-red-500 text-white z-50 top-20 md:top-2.5 right-2 sm:right-5 md:right-4 p-2 element-center gap-2"
    >
        {{ __("The quote invitation has been rejected") }}
    </div>

    <div
        v-else-if="status == QuoteInvitationStatus.Accepted"
        class="w-auto rounded-lg shadow-lg fixed bg-green-500 text-white z-50 top-20 md:top-2.5 right-2 sm:right-5 md:right-4 p-2 element-center gap-2"
    >
        {{ __("The quote invitation has been accepted") }}
    </div>
</template>
