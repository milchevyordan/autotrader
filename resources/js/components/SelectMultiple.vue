<script setup lang="ts">
import { computed, inject, Ref, ref } from "vue";
import { watch } from "vue";
import { onMounted } from "vue";
import Multiselect from "vue-multiselect";

import "../../css/multiselect.css";
import { GlobalInputErrors, Option, Enum } from "@/types";
import { enumToOptions, replaceUnderscores } from "@/utils";

const emit = defineEmits(["select", "remove"]);
const props = withDefaults(
    defineProps<{
        name: string;
        placeholder: string;
        options: Option[] | Enum<any>;
        selectedOptionsValues?: number[];
        reset?: boolean;
        searchable?: boolean;
        disabled?: boolean;
        capitalize?: boolean;
        id?: string;
    }>(),
    {
        multiple: false,
        searchable: true,
        disabled: false,
        capitalize: false,
    }
);

const model = defineModel<null | Array<string | number | null>>();
const optionObjects = ref<Option[]>([]);
const selectedOptionObjects = ref<Option[] | Enum<any>>(null!);
const inputErrors = inject<Ref<GlobalInputErrors>>("globalInputErrors");
const errorMessage = computed(() => {
    const errorMessages = inputErrors?.value?.errorMessages ?? {};
    const propName = props.name;
    return propName ? errorMessages[propName] || null : null;
});

onMounted(() => {
    initOptions();
    initSelectedOptions();
});

watch(
    () => props.options,
    () => {
        initOptions();
    }
);

watch(
    () => props.reset,
    (reset) => {
        if (reset) {
            resetSelect();
        }
    }
);
const select = (option: Option) => {
    model.value = [...(model.value || []), option.value];
    emit("select", {
        name: props.name,
        value: model.value,
    });
};
const remove = (option: Option) => {
    model.value = model.value?.filter((val: string | number | null) => val !== option.value);

    emit("remove", {
        name: props.name,
        value: option,
    });
};
const initOptions = () => {
    optionObjects.value = enumToOptions(
        replaceUnderscores(props.options),
        props.capitalize
    );
};
const initSelectedOptions = () => {
    const selectedValues = props.selectedOptionsValues ?? model.value;
    if (!Array.isArray(selectedValues)) {
        selectedOptionObjects.value = [];
        return;
    }
    selectedOptionObjects.value = optionObjects.value.filter(
        (item) => item.value !== null && selectedValues.includes(item.value)
    );
};
const resetSelect = () => {
    model.value = [];
    selectedOptionObjects.value = [];
};
</script>
<template>
    <div class="w-2/6">
        <Multiselect
            :id="id"
            v-model="selectedOptionObjects"
            :options="optionObjects"
            label="name"
            track-by="value"
            autocomplete="off"
            :multiple="true"
            :searchable="searchable"
            :disabled="disabled"
            :allow-empty="true"
            :placeholder="__('Select') + ' ' + placeholder"
            :class="{ 'border border-red-500 rounded-md': errorMessage }"
            :deselect-label="__('Press enter to remove')"
            :select-label="__('Press enter to select')"
            :selected-label="__('Selected')"
            @select="select"
            @remove="remove"
        />
        <div
            v-if="errorMessage"
            class="text-red-500 text-sm mt-0.5 ml-0.5"
        >
            {{ __(errorMessage) }}
        </div>
    </div>
</template>
