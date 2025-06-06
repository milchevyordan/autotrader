<script setup lang="ts">
import IconDoc from "@/icons/Doc.vue";
import IconPdf from "@/icons/Pdf.vue";
import IconTxt from "@/icons/Txt.vue";
import IconXls from "@/icons/Xls.vue";

const props = withDefaults(
    defineProps<{
        file: string;
        classes?: string;
    }>(),
    {
        classes: "w-8 h-8 sm:group-hover:opacity-80",
        hover: false,
    }
);
</script>

<template>
    <IconTxt
        v-if="['txt'].includes(props.file?.split('.').pop() ?? '')"
        :class="`${props.classes} mx-auto`"
    />

    <IconPdf
        v-else-if="['pdf'].includes(props.file?.split('.').pop() ?? '')"
        :class="`${props.classes} mx-auto`"
    />

    <IconDoc
        v-else-if="['doc', 'docx'].includes(props.file?.split('.').pop() ?? '')"
        :class="`${props.classes} mx-auto`"
    />

    <IconXls
        v-else-if="
            ['xls', 'xlsx', 'csv'].includes(props.file?.split('.').pop() ?? '')
        "
        :class="`${props.classes} mx-auto`"
    />
</template>
