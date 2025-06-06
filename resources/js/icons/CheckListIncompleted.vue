<script setup lang="ts">
withDefaults(
    defineProps<{
        classes?: string;
        stroke?: string;
        color?: string;
    }>(),
    {
        classes: "w-10 h-10 flex-shrink-0",
        stroke: "1.5",
        color: "#000000",
    }
);
</script>

<template>
    <svg
        :fill="color"
        :class="classes"
        :stroke="stroke"
        viewBox="0 0 512 512"
    >
        <g>
            <g>
                <g>
                    <path d="M360,0H152v79.999h208V0z M344,64H168V16h176V64z" />
                    <polygon
                        points="368,24 368,40 408,40 408,496 104,496 104,40 144,40 144,24 88,24 88,512 424,512 424,24"
                    />
                    <rect
                        x="184"
                        y="32"
                        width="16"
                        height="16"
                    />
                    <rect
                        x="312"
                        y="32"
                        width="16"
                        height="16"
                    />
                    <polygon
                        points="376,464 136,464 136,72 144,72 144,56 120,56 120,480 392,480 392,56 368,56 368,72 376,72 "
                    />
                    <polygon
                        points="210.344,130.343 184,156.686 173.658,146.342 162.342,157.656 184,179.314 221.656,141.656"
                    />
                    <rect
                        x="248"
                        y="136"
                        width="56"
                        height="16"
                    />
                    <rect
                        x="248"
                        y="168"
                        width="104"
                        height="16"
                    />
                    <rect
                        x="248"
                        y="248"
                        width="56"
                        height="16"
                    />
                    <rect
                        x="248"
                        y="280"
                        width="104"
                        height="16"
                    />
                    <rect
                        x="248"
                        y="360"
                        width="56"
                        height="16"
                    />
                    <rect
                        x="248"
                        y="392"
                        width="104"
                        height="16"
                    />
                    <path
                        d="M160,296h64v-64h-64V296z M176,248h32v32h-32V248z"
                    />
                    <path
                        d="M160,408h64v-64h-64V408z M176,360h32v32h-32V360z"
                    />
                </g>
            </g>
        </g>
    </svg>
</template>
