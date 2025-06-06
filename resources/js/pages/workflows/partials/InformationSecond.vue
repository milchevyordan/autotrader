<script setup lang="ts">
import Section from "@/components/html/Section.vue";
</script>

<template>
    <Section classes="h-fit">
        <div class="p-4 border-b border-[#E9E7E7] font-bold">
            PO7893831 - OTB - MARGIN 135.985,32 EUR
        </div>

        <div
            class="flex items-center justify-between px-4 py-1.5 border-b border-[#E9E7E70]"
        >
            <div class="text-[#6D6D73]">
                {{ __("Delivery Point") }}
            </div>
            <div>Freser GmbH</div>
        </div>

        <div
            class="flex items-center justify-between px-4 py-1.5 border-b border-[#E9E7E70]"
        >
            <div class="text-[#6D6D73]">
                {{ __("Intermediate Supplier") }}
            </div>
            <div>ECC GmbH</div>
        </div>

        <div
            class="flex items-center justify-between px-4 py-1.5 border-b border-[#E9E7E70]"
        >
            <div class="text-[#6D6D73]">
                {{ __("Registration") }}
            </div>
            <div>10000 km</div>
        </div>

        <div
            class="flex items-center justify-between px-4 py-1.5 border-b border-[#E9E7E70]"
        >
            <div class="text-[#6D6D73]">
                {{ __("Taxie") }}
            </div>
            <div>
                <input
                    type="checkbox"
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded"
                />
            </div>
        </div>

        <div
            class="flex items-center justify-between px-4 py-1.5 border-b border-[#E9E7E70]"
        >
            <div class="text-[#6D6D73]">{{ __("Import") }} CarFlow</div>
            <div>
                <input
                    type="checkbox"
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded"
                />
            </div>
        </div>

        <div
            class="flex items-center justify-between px-4 py-1.5 border-b border-[#E9E7E70]"
        >
            <div class="text-[#6D6D73]">
                {{ __("Transport supplier") }}
            </div>
            <div>
                <input
                    type="checkbox"
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded"
                />
            </div>
        </div>

        <div class="px-4 py-3 flex items-center justify-between">
            <div class="text-[#6D6D73]">1/1</div>
            <button
                type="button"
                class="border border-[#008FE3] text-[#008FE3] px-3 py-0.5 rounded-md hover:opacity-60 active:scale-95 transition"
            >
                {{ __("Go to") }}
            </button>
        </div>
    </Section>
</template>
