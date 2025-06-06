<script setup lang="ts">
import Section from "@/components/html/Section.vue";
</script>

<template>
    <Section classes="h-fit">
        <div class="p-4 border-b border-[#E9E7E7]">
            <div class="font-bold">
                {{ __("Logistic") }}
            </div>
        </div>

        <ul class="relative border-l border-gray-200 ml-6 my-4">
            <li class="mb-[49px] grid grid-cols-3 place-items-center">
                <div
                    class="absolute flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 bg-[#CCEDE9] rounded-full -left-4 ring-4 ring-white col-span-2"
                >
                    <div
                        class="w-5 h-5 sm:w-6 sm:h-6 bg-[#66CABE] rounded-full flex items-center justify-center"
                    >
                        <div
                            class="w-3 w-3 sm:w-4 sm:h-4 bg-[#00A793] rounded-full"
                        />
                    </div>
                </div>

                <div>
                    <div class="text-[15px]">
                        {{ __("Pick up") }}
                    </div>
                    <div class="text-[13px] text-[#676666] -mt-1">
                        Freser Gmbh
                    </div>
                </div>

                <div
                    class="text-sm text-[#676666] -ml-10 border border-[#F2F2F7] rounded-md px-2 py-1"
                >
                    28-08-2022
                </div>

                <div class="text-sm text-[#676666] -ml-10">28-08-2022</div>
            </li>

            <li class="mb-[50px] grid grid-cols-3 place-items-center">
                <div
                    class="absolute flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 bg-gray-200 rounded-full -left-4 ring-4 ring-white col-span-2"
                >
                    <div
                        class="w-5 h-5 sm:w-6 sm:h-6 bg-gray-300 rounded-full flex items-center justify-center"
                    >
                        <div
                            class="w-3 w-3 sm:w-4 sm:h-4 bg-gray-400 rounded-full"
                        />
                    </div>
                </div>

                <div>
                    <div class="text-[15px]">
                        {{ __("Warehouse") }}
                    </div>
                    <div class="text-[13px] text-[#676666] -mt-1">
                        Velddriel
                    </div>
                </div>

                <div
                    class="text-sm text-[#676666] -ml-10 border border-[#F2F2F7] rounded-md px-2 py-1"
                />

                <div class="text-sm text-[#676666] -ml-10" />
            </li>

            <li class="mb-[50px] grid grid-cols-3 place-items-center">
                <div
                    class="absolute flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 bg-gray-200 rounded-full -left-4 ring-4 ring-white col-span-2"
                >
                    <div
                        class="w-5 h-5 sm:w-6 sm:h-6 bg-gray-300 rounded-full flex items-center justify-center"
                    >
                        <div
                            class="w-3 w-3 sm:w-4 sm:h-4 bg-gray-400 rounded-full"
                        />
                    </div>
                </div>

                <div>
                    <div class="text-[15px]">
                        {{ __("Warehouse") }}
                    </div>
                    <div class="text-[13px] text-[#676666] -mt-1">CarFlow</div>
                </div>

                <div
                    class="text-sm text-[#676666] -ml-10 border border-[#F2F2F7] rounded-md px-2 py-1"
                />

                <div class="text-sm text-[#676666] -ml-10" />
            </li>

            <li class="grid grid-cols-3 place-items-center">
                <div
                    class="absolute flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 bg-gray-200 rounded-full -left-4 ring-4 ring-white col-span-2"
                >
                    <div
                        class="w-5 h-5 sm:w-6 sm:h-6 bg-gray-300 rounded-full flex items-center justify-center"
                    >
                        <div
                            class="w-3 w-3 sm:w-4 sm:h-4 bg-gray-400 rounded-full"
                        />
                    </div>
                </div>

                <div>
                    <div class="text-[15px]">
                        {{ __("Delivery") }}
                    </div>
                    <div class="text-[13px] text-[#676666] -mt-1">
                        Mass auto Gr
                    </div>
                </div>

                <div
                    class="text-sm text-[#676666] -ml-10 border border-[#F2F2F7] rounded-md px-2 py-1"
                />

                <div class="text-sm text-[#676666] -ml-10" />
            </li>
        </ul>
    </Section>
</template>
