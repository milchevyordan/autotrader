<script setup lang="ts">
withDefaults(
    defineProps<{
        classes?: string;
        solid?: boolean;
    }>(),
    {
        classes: "w-6 h-6 flex-shrink-0",
        solid: false,
    }
);
</script>

<template>
    <svg
        v-if="!solid"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        :class="classes"
    >
        <g stroke-width="0" />
        <g
            stroke-linecap="round"
            stroke-linejoin="round"
        />
        <g>
            <path
                d="M18 18.86H17.24C16.44 18.86 15.68 19.17 15.12 19.73L13.41 21.42C12.63 22.19 11.36 22.19 10.58 21.42L8.87 19.73C8.31 19.17 7.54 18.86 6.75 18.86H6C4.34 18.86 3 17.53 3 15.89V4.97998C3 3.33998 4.34 2.01001 6 2.01001H18C19.66 2.01001 21 3.33998 21 4.97998V15.89C21 17.52 19.66 18.86 18 18.86Z"
                stroke="currentColor"
                stroke-width="1.5"
                stroke-miterlimit="10"
                stroke-linecap="round"
                stroke-linejoin="round"
            />
            <path
                d="M12.28 14.96C12.13 15.01 11.88 15.01 11.72 14.96C10.42 14.51 7.5 12.66 7.5 9.51001C7.5 8.12001 8.62 7 10 7C10.82 7 11.54 7.39 12 8C12.46 7.39 13.18 7 14 7C15.38 7 16.5 8.12001 16.5 9.51001C16.49 12.66 13.58 14.51 12.28 14.96Z"
                stroke="currentColor"
                stroke-width="1.5"
                stroke-linecap="round"
                stroke-linejoin="round"
            />
        </g>
    </svg>

    <svg
        v-else
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        :class="classes"
    >
        <g stroke-width="0" />
        <g
            stroke-linecap="round"
            stroke-linejoin="round"
        />
        <g>
            <path
                d="M18 2H6C4.34 2 3 3.33 3 4.97V15.88C3 17.52 4.34 18.85 6 18.85H6.76C7.56 18.85 8.32 19.16 8.88 19.72L10.59 21.41C11.37 22.18 12.64 22.18 13.42 21.41L15.13 19.72C15.69 19.16 16.46 18.85 17.25 18.85H18C19.66 18.85 21 17.52 21 15.88V4.97C21 3.33 19.66 2 18 2ZM12.28 14.96C12.13 15.01 11.88 15.01 11.72 14.96C10.42 14.51 7.5 12.66 7.5 9.51C7.51 8.12 8.62 7 10 7C10.82 7 11.54 7.39 12 8C12.46 7.39 13.18 7 14 7C15.38 7 16.5 8.12 16.5 9.51C16.49 12.66 13.58 14.51 12.28 14.96Z"
                fill="currentColor"
            />
        </g>
    </svg>
</template>
