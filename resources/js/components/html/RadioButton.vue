<script setup lang="ts">

const props = defineProps<{
    id: string;
    label: string;
    name: string;
    value?: string;
    checked: boolean;
    classes?: string;
}>();

const emit = defineEmits(["click"]);

const handleClick = () => {
    emit("click");
};
</script>

<template>
    <div>
        <input
            :id="props.id"
            type="radio"
            :value="props.value"
            :name="props.name"
            :checked="props.checked"
            class="peer hidden"
        />
        <label
            :for="props.id"
            :class="[
                'relative flex shadow-md border px-4 py-1.5 cursor-pointer transition-all active:scale-95',
                props.classes,
                props.checked
                    ? 'bg-[#008FE3] text-white border-blue-200'
                    : 'bg-white',
            ]"
            @click="handleClick"
        >
            {{ props.label }}
        </label>
    </div>
</template>
