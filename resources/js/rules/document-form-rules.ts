export const documentFormRules = {
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
    documentable_type: {
        required: true,
        type: "number",
    },
    paid_at: {
        required: false,
        type: "date",
    },
    payment_condition: {
        required: true,
        type: "number",
    },
    payment_condition_free_text: {
        required: false,
        type: "string",
    },
    number: {
        required: false,
        type: "string",
    },
    notes: {
        required: false,
        type: "text",
    },
    total_price_exclude_vat: {
        required: false,
        type: "price",
    },
    total_price_include_vat: {
        required: false,
        type: "price",
    },
    total_vat: {
        required: false,
        type: "price",
    },
    date: {
        required: false,
        type: "date",
    },
    internal_remarks: {
        complex: true,
    },
    lines: {
        complex: true,
    },
};
