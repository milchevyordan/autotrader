<script setup lang="ts">
import { computed, inject, Ref, ref } from "vue";
import { watch } from "vue";
import { onMounted } from "vue";
import Multiselect from "vue-multiselect";

import { __ } from "@/translations";

import "../../css/multiselect.css";

import { GlobalInputErrors, Option, Enum } from "@/types";
import { enumToOptions, replaceUnderscores } from "@/utils";

const emit = defineEmits(["select", "remove", "refresh"]);

const props = withDefaults(
    defineProps<{
        name: string;
        placeholder: string;
        options: Option[] | Enum<any>;
        selectedOptionValue?: number;
        reset?: boolean;
        refresh?: boolean;
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

const model = defineModel<null | string | number>();

const optionObjects = ref<Option[]>([]);

const selectedOptionObject = ref<Option[] | Enum<any>>(null!);

const inputErrors = inject<Ref<GlobalInputErrors>>("globalInputErrors");

const errorMessage = computed(() => {
    const errorMessages = inputErrors?.value?.errorMessages ?? {};
    const propName = props.name;

    return propName ? errorMessages[propName] || null : null;
});

onMounted(() => {
    initOptions(props.options);
    initSelectedOption();
});

watch(
    () => model.value,
    (modelValue) => {
        initSelectedOption();
    }
);

watch(
    () => props.options,
    (options) => {
        initOptions(options);
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

watch(
    () => props.refresh,
    (refresh) => {
        if (refresh) {
            handleRefresh();
        }
    }
);

const handleRefresh = () => {
    initOptions(props.options);
    initSelectedOption();
};

const select = (option: Option) => {
    model.value = option.value;

    emit("select", {
        name: props.name,
        value: option.value,
    });
};

const remove = () => {
    model.value = null;

    emit("remove", {
        name: props.name,
        value: null,
    });
};

const initOptions = (options: Option[] | Enum<any>) => {
    if (Array.isArray(options)) {
        optionObjects.value = options.map((option) => ({
            ...option,
            label: __(option.label),
        }));
    } else {
        const returnType =
            typeof options === "object" &&
            Object.values(options).some((val) => typeof val === "number")
                ? "number"
                : "string";

        optionObjects.value = enumToOptions(
            replaceUnderscores(options),
            props.capitalize,
            returnType
        ).map((option) => ({
            ...option,
            name: __(option.name),
        }));
    }
};

const placeholderText = ref<string>(props.placeholder);

const initSelectedOption = () => {
    selectedOptionObject.value =
        optionObjects.value.find(
            (item) => item.value == (props.selectedOptionValue ?? model.value)
        ) || null;

    if (
        !optionObjects.value.length &&
        (props.selectedOptionValue || model.value)
    ) {
        placeholderText.value = __("Not available");
    }
};

const resetSelect = () => {
    model.value = null;
    selectedOptionObject.value = null;
};
</script>

<template>
    <div class="w-2/6">
        <Multiselect
            :id="id"
            v-model="selectedOptionObject"
            :options="optionObjects"
            label="name"
            track-by="value"
            autocomplete="off"
            :multiple="false"
            :searchable="searchable"
            :disabled="disabled"
            :allow-empty="true"
            :placeholder="placeholderText"
            :class="{ 'border border-red-500 rounded-md': errorMessage }"
            :deselect-label="__('Press enter to remove')"
            :select-label="__('Press enter to select')"
            :selected-label="__('Selected')"
            @select="select"
            @remove="remove"
        />

        <div v-if="errorMessage" class="text-red-500 text-sm mt-0.5 ml-0.5">
            {{ __(errorMessage) }}
        </div>
    </div>
</template>
