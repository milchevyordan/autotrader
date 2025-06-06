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
                    {{ __("Email Text") }}
                </div>
            </template>

            <Textarea
                v-model="form.email_text"
                :name="'email_text'"
                :disabled="formDisabled"
                placeholder="Email Text"
                classes="mb-4"
            />
        </Accordion>
    </Section>
</template>
