import { computed, Ref, ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import { Flash, GlobalInputErrors } from "@/types";
import { findEnumKeyByValue } from "@/utils";
import { Currency } from "@/enums/Currency";
import { format, parseISO } from "date-fns";

declare global {
    interface String {
        toCapitalize(): string;
    }
}

// Global messages
export const globalInputErrors = ref<GlobalInputErrors>({
    errorMessages: usePage().props?.errors,
    failedKeys: [],
});

export const failedKeys: Ref<string[]> = ref([]);

export const errorMessages: Ref<Record<string, string>> = ref({});

export const setGlobalInputErrors = (
    failedKeys: string[],
    errorMessages: Record<string, string>
) => {
    globalInputErrors.value = {
        failedKeys,
        errorMessages,
    };
};

export const flashMessages = ref<Flash>();

export const setFlashMessages = (message: Flash) => {
    flashMessages.value = null!;
    flashMessages.value = message;
};

export const addFlashErrorMessage = (message: string) => {
    if (!flashMessages.value) {
        flashMessages.value = { errors: [message] };
        return;
    }

    if (!Array.isArray(flashMessages.value.errors)) {
        flashMessages.value.errors = [message];
        return;
    }

    flashMessages.value.errors.push(message);
};

export const clearFailedKeysAndErrorMessages = () => {
    failedKeys.value = [];
    errorMessages.value = {};
};

export const clearFlashMessages = () => {
    flashMessages.value = null!;
    usePage().props.flash = null!;
};

export const companyCurrency = computed(() => {
    return findEnumKeyByValue(
        Currency,
        usePage().props?.auth.company.default_currency
    );
});
