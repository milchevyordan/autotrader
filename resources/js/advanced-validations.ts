import { ServiceLevelType } from "@/enums/ServiceLevelType";
import { errorMessages, failedKeys } from "@/globals";
import { validateField } from "@/validations";
import { WorkOrderTaskStatus } from "@/enums/WorkOrderTaskStatus";
import {Rule} from "@/types";

export function validateAdvancedField(
    key: string,
    value: any,
    rule: Rule,
    obj: Record<string, any>
) {
    if (rule.type) {
        switch (rule.type) {
            case "week_range":
                if (typeof value !== "object") {
                    failedKeys.value.push(key);
                    errorMessages.value[
                        key
                        ] = `This field must be an object.`;
                } else {
                    if ('from' in value && 'to' in value) {
                        return;
                    }

                    failedKeys.value.push(key);
                    errorMessages.value[
                        key
                        ] = `This field must have a be a week range.`;
                }
                break;
        }
    } else {
        switch (key) {
            case "internal_remarks":
                const userIds = obj["internal_remark_user_ids"] ?? [];
                const roleIds = obj["internal_remark_role_ids"] ?? [];
                const internalRemark = obj["internal_remark"];

                if (
                    userIds.length !== 0 ||
                    roleIds.length !== 0 ||
                    internalRemark
                ) {
                    if (userIds.length === 0 && roleIds.length === 0) {
                        failedKeys.value.push("internal_remark_user_ids");
                        errorMessages.value[
                            "internal_remark_user_ids"
                        ] = `This field is required.`;
                        failedKeys.value.push("internal_remark_role_ids");
                        errorMessages.value[
                            "internal_remark_role_ids"
                        ] = `This field is required.`;
                    } else {
                        validateField(
                            `internal_remark`,
                            internalRemark,
                            {
                                required: true,
                                type: "text",
                            },
                            obj
                        );
                    }
                }
                break;

            case "actual_price":
                validateField(
                    `actual_price`,
                    obj["actual_price"],
                    {
                        required: obj["status"] == WorkOrderTaskStatus.Completed,
                        type: "price",
                        min: 0,
                    },
                    obj
                );
                break;

            default:
                if (!Array.isArray(value)) {
                    failedKeys.value.push(key);
                    errorMessages.value[key] = `This field must be an array.`;
                } else {
                    switch (key) {
                        case "additional_services":
                            value.forEach((service_level_service, index) => {
                                validateField(
                                    `additional_services[${index}][name]`,
                                    service_level_service.name,
                                    {
                                        required: true,
                                        type: "string",
                                    },
                                    obj
                                );
                                validateField(
                                    `additional_services[${index}][purchase_price]`,
                                    service_level_service.purchase_price,
                                    {
                                        required: false,
                                        type: "price",
                                        min: 0,
                                    },
                                    obj
                                );
                                validateField(
                                    `additional_services[${index}][sale_price]`,
                                    service_level_service.sale_price,
                                    {
                                        required: false,
                                        type: "price",
                                    },
                                    obj
                                );
                                validateField(
                                    `additional_services[${index}][in_output]`,
                                    service_level_service.in_output,
                                    {
                                        required: true,
                                        type: "boolean",
                                    },
                                    obj
                                );
                            });

                            break;

                        case "addresses":
                            value.forEach((address, index) => {
                                validateField(
                                    `company_addresses[${index}][type]`,
                                    address.type,
                                    {
                                        required: true,
                                        type: "number",
                                    },
                                    obj
                                );
                                validateField(
                                    `company_addresses[${index}][address]`,
                                    address.address,
                                    {
                                        required: true,
                                        type: "string",
                                    },
                                    obj
                                );
                                validateField(
                                    `company_addresses[${index}][remarks]`,
                                    address.remarks,
                                    {
                                        required: false,
                                        type: "string",
                                    },
                                    obj
                                );
                            });

                            break;

                        case "items":
                            value.forEach((item, index) => {
                                validateField(
                                    `items[${index}][sale_price]`,
                                    item.sale_price,
                                    {
                                        required: false,
                                        type: "price",
                                    },
                                    obj
                                );
                                validateField(
                                    `items[${index}][in_output]`,
                                    item.in_output,
                                    {
                                        required: true,
                                        type: "boolean",
                                    },
                                    obj
                                );
                            });

                            break;

                        case "companies":
                            if (
                                obj["type"] == ServiceLevelType.Client &&
                                value.length == 0
                            ) {
                                failedKeys.value.push(key);
                                errorMessages.value[
                                    key
                                ] = `Companies are required if service level is type Client`;
                            }

                            break;

                        case "lines":
                            value.forEach((item, index) => {
                                validateField(
                                    `document_lines[${index}][name]`,
                                    item.name,
                                    {
                                        required: true,
                                        type: "string",
                                        maxLength: 500,
                                    },
                                    obj
                                );
                                validateField(
                                    `document_lines[${index}][vat_percentage]`,
                                    item.vat_percentage,
                                    {
                                        required: false,
                                        type: "number",
                                    },
                                    obj
                                );
                                validateField(
                                    `document_lines[${index}][price_exclude_vat]`,
                                    item.price_exclude_vat,
                                    {
                                        required: false,
                                        type: "price",
                                    },
                                    obj
                                );
                                validateField(
                                    `document_lines[${index}][vat]`,
                                    item.vat,
                                    {
                                        required: false,
                                        type: "price",
                                    },
                                    obj
                                );
                                validateField(
                                    `document_lines[${index}][price_include_vat]`,
                                    item.price_include_vat,
                                    {
                                        required: true,
                                        type: "price",
                                    },
                                    obj
                                );
                            });

                            break;
                    }
                }
        }
    }
}
