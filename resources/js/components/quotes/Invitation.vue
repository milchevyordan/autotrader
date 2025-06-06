<script setup lang="ts">
import Section from "@/components/html/Section.vue";
import { QuoteInvitationStatus } from "@/enums/QuoteInvitationStatus";
import { QuoteInvitation } from "@/types";
import { dateTimeToLocaleString, findEnumKeyByValue } from "@/utils";

defineProps<{
    invitations: QuoteInvitation[];
}>();

const statusColor = (status: number) => {
    switch (status) {
        case QuoteInvitationStatus.Concept:
            return "bg-slate-100 text-slate-800";

        case QuoteInvitationStatus.Closed:
            return "bg-yellow-100 text-yellow-800";

        case QuoteInvitationStatus.Accepted:
            return "bg-green-100 text-green-800";

        case QuoteInvitationStatus.Rejected:
            return "bg-red-100 text-red-800";
    }
};
</script>

<template>
    <Section classes="p-4 pb-0 mt-4 relative">
        <div class="font-semibold text-xl sm:text-2xl mb-4">
            {{ __("Quote Invitations") }}
        </div>

        <div
            v-for="(invitation, index) in invitations"
            :key="index"
            class="bg-white border border-[#E9E7E7] rounded-lg p-4 my-4 relative shadow-md"
        >
            <div
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-center"
            >
                <div
                    class="col-span-1 sm:col-span-2 lg:col-span-1 flex items-center"
                >
                    <button
                        class="text-sm font-medium px-2.5 py-0.5 rounded whitespace-nowrap"
                        :class="statusColor(invitation.status)"
                    >
                        {{
                            findEnumKeyByValue(
                                QuoteInvitationStatus,
                                invitation.status
                            )
                        }}
                    </button>
                </div>

                <div class="truncate">
                    <span class="font-semibold sm:hidden">Name: </span>
                    {{ invitation.customer.full_name }}
                </div>

                <div class="truncate">
                    <span class="font-semibold sm:hidden">Email: </span>
                    {{ invitation.customer.email }}
                </div>

                <div class="truncate">
                    <span class="font-semibold sm:hidden">Mobile: </span>
                    {{ invitation.customer.mobile }}
                </div>

                <div class="truncate">
                    <span class="font-semibold sm:hidden">Date: </span>
                    {{ dateTimeToLocaleString(invitation.created_at) }}
                </div>
            </div>
        </div>
    </Section>
</template>
