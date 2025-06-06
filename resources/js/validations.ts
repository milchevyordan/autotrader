import {
    clearFailedKeysAndErrorMessages,
    clearFlashMessages,
    errorMessages,
    failedKeys,
    setFlashMessages,
    setGlobalInputErrors,
} from "./globals";
import { Ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import { validateAdvancedField } from "@/advanced-validations";
import { strToNum } from "@/utils";
import {Rule, Rules} from "@/types";

interface PasswordRuleObject extends Rule {
    requireUppercase?: boolean;
    requireLowercase?: boolean;
    requireSymbol?: boolean;
    requireNumber?: boolean;
    requireLetter?: boolean;
}

export function validate(obj: Record<string, any>, rules: Rules): void {
    let failed = false;

    if (obj.processing) {
        throw new Error("saving");
    }

    for (const key in rules) {
        const rule = rules[key];
        const value = obj[key];

        if (rule.complex) {
            validateAdvancedField(key, value, rule, obj);
        } else {
            validateField(key, value, rule, obj);
        }
    }

    failed = failedKeys.value.length > 0;

    if (failed) {
        setGlobalInputErrors(failedKeys.value, errorMessages.value);

        setFlashMessages({
            error: "Validation failed, Please check the fields marked with red!",
        });

        console.error(errorMessages.value);

        clearFailedKeysAndErrorMessages();

        throw new Error("message");
    }

    clearFlashMessages();
}

export function validateField(
    key: string,
    value: any,
    rule: Rule,
    obj: Record<string, any>
): void {
    const maxIntegerValue = usePage().props?.config.validation.rule
        .maxIntegerValue as number;
    const maxStringLength = usePage().props?.config.validation.rule
        .maxStringLength as number;

    if (
        rule.required &&
        (value === undefined || value === null || value === "")
        // || value === false
    ) {
        failedKeys.value.push(key);
        errorMessages.value[key] = `This field is required.`;
    } else {
        if (value !== undefined && value !== null && value !== "") {
            if (typeof rule === "object") {
                if (rule.type) {
                    switch (rule.type) {
                        case "string":
                        case "date":
                        case "price":
                            if (typeof value !== "string") {
                                failedKeys.value.push(key);
                                errorMessages.value[
                                    key
                                ] = `This field must be a string.`;
                            } else {
                                if (
                                    !rule.maxLength &&
                                    value.length > maxStringLength
                                ) {
                                    failedKeys.value.push(key);
                                    errorMessages.value[
                                        key
                                    ] = `This field must have a maximum length of ${maxStringLength}.`;
                                }
                            }
                            break;

                        case "text":
                            if (typeof value !== "string") {
                                failedKeys.value.push(key);
                                errorMessages.value[
                                    key
                                ] = `This field must be a string.`;
                            }
                            break;

                        case "number":
                            if (isNaN(Number(value))) {
                                failedKeys.value.push(key);
                                errorMessages.value[
                                    key
                                ] = `This field must be a number.`;
                            } else {
                                if (
                                    !rule.max &&
                                    !rule.minLength &&
                                    value > maxIntegerValue
                                ) {
                                    failedKeys.value.push(key);
                                    errorMessages.value[
                                        key
                                    ] = `This field must be less than or equal to ${maxIntegerValue}.`;
                                }
                            }

                            break;

                        case "boolean":
                            if (typeof value !== "boolean") {
                                failedKeys.value.push(key);
                                errorMessages.value[
                                    key
                                ] = `This field must be a boolean.`;
                            }

                            break;

                        case "array":
                            // if (!Array.isArray(value)) {
                            //     failedKeys.value.push(key);
                            //     errorMessages.value[key] = `This field must be an array.`;
                            // }
                            break;

                        case "email":
                            if (
                                typeof value !== "string" ||
                                !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
                            ) {
                                failedKeys.value.push(key);
                                errorMessages.value[
                                    key
                                ] = `This field must be a valid email address.`;
                            }
                            break;

                        case "password":
                            if (typeof value !== "string") {
                                failedKeys.value.push(key);
                                errorMessages.value[
                                    key
                                ] = `This field must be a string.`;
                            } else {
                                const passwordRule = rule as PasswordRuleObject;
                                if (
                                    passwordRule.requireUppercase &&
                                    !/[A-Z]/.test(value)
                                ) {
                                    failedKeys.value.push(key);
                                    errorMessages.value[
                                        key
                                    ] = `This field must contain at least one number, alongside letters, symbols, and mixed case.`;
                                }
                                if (
                                    passwordRule.requireLowercase &&
                                    !/[a-z]/.test(value)
                                ) {
                                    failedKeys.value.push(key);
                                    errorMessages.value[
                                        key
                                    ] = `This field must contain at least one number, alongside letters, symbols, and mixed case.`;
                                }
                                if (
                                    passwordRule.requireSymbol &&
                                    !/[\s\p{S}\p{P}]/gu.test(value)
                                ) {
                                    failedKeys.value.push(key);
                                    errorMessages.value[
                                        key
                                    ] = `This field must contain at least one number, alongside letters, symbols, and mixed case.`;
                                }
                                if (
                                    passwordRule.requireNumber &&
                                    !/\p{N}/gu.test(value)
                                ) {
                                    failedKeys.value.push(key);
                                    errorMessages.value[
                                        key
                                    ] = `This field must contain at least one number, alongside letters, symbols, and mixed case.`;
                                }
                                if (
                                    passwordRule.requireLetter &&
                                    !/\p{L}/gu.test(value)
                                ) {
                                    failedKeys.value.push(key);
                                    errorMessages.value[
                                        key
                                    ] = `This field must contain at least one number, alongside letters, symbols, and mixed case.`;
                                }
                            }
                            break;

                        default:
                            break;
                    }
                }

                if (rule.minLength && typeof value === "string") {
                    const minLength = rule.minLength;
                    if (value.length < minLength) {
                        failedKeys.value.push(key);
                        errorMessages.value[
                            key
                        ] = `This field must have a minimum length of ${minLength}.`;
                    }
                }

                if (rule.maxLength && typeof value === "string") {
                    const maxLength = rule.maxLength;
                    if (value.length > maxLength) {
                        failedKeys.value.push(key);
                        errorMessages.value[
                            key
                        ] = `This field must have a maximum length of ${maxLength}.`;
                    }
                }

                if (
                    rule.min !== null &&
                    rule.min !== undefined &&
                    (typeof value === "number" || typeof value === "string")
                ) {
                    let minValue: number | string | undefined;

                    switch (rule.type) {
                        case "date":
                            minValue =
                                typeof rule.min === "string"
                                    ? obj[rule.min as string]
                                    : (rule.min as Ref<string>).value;
                            break;

                        case "price":
                            value = strToNum(value as string);
                            minValue = rule.min as number;

                            break;

                        default:
                            minValue = rule.min as number;
                            break;
                    }

                    if (minValue !== undefined && value < minValue) {
                        failedKeys.value.push(key);
                        errorMessages.value[
                            key
                        ] = `This field must be greater than or equal to ${minValue}.`;
                    }
                }

                if (
                    rule.max &&
                    (typeof value === "number" || typeof value === "string")
                ) {
                    let maxValue: number | string | undefined;

                    if (rule.type === "date") {
                        maxValue =
                            typeof rule.max === "string"
                                ? obj[rule.max as string]
                                : (rule.max as Ref<string>).value;
                    } else {
                        maxValue = rule.max as number;
                    }

                    if (maxValue && value > maxValue) {
                        failedKeys.value.push(key);
                        errorMessages.value[
                            key
                        ] = `This field must be less than or equal to ${maxValue}.`;
                    }
                }
            }
        }

        if (rule.with) {
            const withValue = obj[rule.with];

            if (withValue && rule["type"]) {
                validateField(
                    key,
                    value,
                    {
                        required: true,
                        type: rule["type"],
                    },
                    obj
                );
            }
        }
    }
}
