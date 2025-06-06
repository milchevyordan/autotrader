<script setup lang="ts">
import Accordion from "@/components/Accordion.vue";
import Section from "@/components/html/Section.vue";
import Textarea from "@/components/html/Textarea.vue";

defineProps<{
    form: any;
    formDisabled?: boolean;
}>();
</script>

<template>
    <Section classes="p-4 pb-0 mt-4 relative">
        <Accordion>
            <template #head>
                <div class="font-semibold text-xl sm:text-2xl mb-4">
                    {{ __("Additional Information And Conditions") }}
                </div>
            </template>

            <Textarea
                v-model="form.additional_info_conditions"
                :name="'additional_info_conditions'"
                placeholder="Additional Information And Conditions"
                classes="mb-4"
                :disabled="formDisabled"
            />
        </Accordion>
    </Section>
</template>
