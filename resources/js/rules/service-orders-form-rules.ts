export const serviceOrdersFormRules = {
    owner_id: {
        required: true,
        type: "number",
    },
    customer_company_id: {
        required: true,
        type: "number",
    },
    customer_id: {
        required: false,
        type: "number",
    },
    seller_id: {
        required: true,
        type: "number",
    },
    service_level_id: {
        required: false,
        type: "number",
    },
    type_of_service: {
        required: true,
        type: "number",
    },
    payment_condition: {
        required: true,
        type: "number",
    },
    payment_condition_free_text: {
        required: false,
        type: "string",
    },
    discount: {
        required: false,
        type: "price",
    },
    internal_remarks: {
        complex: true,
    },
    additional_services: {
        complex: true,
    },
    items: {
        complex: true,
    },
};
