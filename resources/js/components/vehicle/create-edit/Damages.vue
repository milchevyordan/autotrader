<script setup lang="ts">
import Accordion from "@/components/Accordion.vue";
import Section from "@/components/html/Section.vue";
import Textarea from "@/components/html/Textarea.vue";

defineProps<{
    form: any;
}>();
</script>

<template>
    <Section classes="p-4 pb-0 mt-4 relative">
        <Accordion>
            <template #head>
                <div class="font-semibold text-xl sm:text-2xl mb-4">
                    {{ __("Damages") }}
                </div>
            </template>

            <Textarea
                v-model="form.damage_description"
                :name="'damage_description'"
                :placeholder="__('Damages')"
                classes="mb-4"
            />
        </Accordion>
    </Section>
</template>
