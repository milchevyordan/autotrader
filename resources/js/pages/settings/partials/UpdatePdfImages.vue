<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";

import InputImage from "@/components/html/InputImage.vue";
import PrimaryButton from "@/components/html/PrimaryButton.vue";
import { CompanyPdfAssets, CompanyPdfAssetsFormData } from "@/types";

defineProps<{
    companyPdfAssets: CompanyPdfAssets;
}>();

const form = useForm<CompanyPdfAssetsFormData>({
    logo: [],
    pdf_signature_image: [],
    pdf_header_pre_purchase_sales_order_image: [],
    pdf_header_documents_image: [],
    pdf_header_quote_transport_and_declaration_image: [],
    pdf_sticker_image: [],
    pdf_footer_image: [],
});

const submit = () => {
    form.post(route("settings.logo.update"), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <div class="mt-6 w-full">
        <form @submit.prevent="submit">
            <h3 class="text-md font-semibold my-4">
                {{ __("The logo used for the company") }}
            </h3>
            <InputImage
                v-model="form.logo"
                :images="companyPdfAssets.logo"
                :text="__('Company logo')"
                :single-image="true"
                text-classes="py-14 h-36"
            />

            <hr class="my-2" />

            <h3 class="text-md font-semibold my-4">
                {{ __("The images used for the PDF files") }}
            </h3>

            <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-5 gap-y-8">
                <InputImage
                    v-model="form.pdf_signature_image"
                    :images="companyPdfAssets.pdf_signature_image"
                    :text="__('Payment')"
                    :single-image="true"
                    text-classes="py-14 h-36"
                />

                <InputImage
                    v-model="form.pdf_header_pre_purchase_sales_order_image"
                    :images="
                        companyPdfAssets.pdf_header_pre_purchase_sales_order_image
                    "
                    :text="__('Pre / Purchase / Sales orders')"
                    :single-image="true"
                    text-classes="py-14 h-36"
                />

                <InputImage
                    v-model="form.pdf_header_documents_image"
                    :images="companyPdfAssets.pdf_header_documents_image"
                    :text="__('Header - Invoice')"
                    :single-image="true"
                    text-classes="py-14 h-36"
                />

                <InputImage
                    v-model="
                        form.pdf_header_quote_transport_and_declaration_image
                    "
                    :images="
                        companyPdfAssets.pdf_header_quote_transport_and_declaration_image
                    "
                    :text="__('Header - Quote / Transport / Declaration')"
                    :single-image="true"
                    text-classes="py-14 h-36"
                />

                <InputImage
                    v-model="form.pdf_sticker_image"
                    :images="companyPdfAssets.pdf_sticker_image"
                    :text="__('Stickervel - Transport Order')"
                    :single-image="true"
                    text-classes="py-14 h-36"
                />

                <InputImage
                    v-model="form.pdf_footer_image"
                    :images="companyPdfAssets.pdf_footer_image"
                    :text="__('Pdf Footer')"
                    :single-image="true"
                    text-classes="py-14 h-36"
                />
            </div>
            <div class="flex justify-end items-center gap-4 mt-4">
                <PrimaryButton :disabled="form.processing">
                    Save
                </PrimaryButton>
            </div>
        </form>

        <div class="flex justify-end items-center gap-4 mt-4">
            <Transition enter-from-class="opacity-0" leave-to-class="opacity-0">
                <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">
                    {{ __("Saved.") }}
                </p>
            </Transition>
        </div>
    </div>
</template>
