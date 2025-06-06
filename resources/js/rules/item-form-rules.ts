export const itemFormRules = {
    unit_type: {
        required: true,
        type: "number",
    },
    type: {
        required: true,
        type: "number",
    },
    is_vat: {
        required: true,
        type: "boolean",
    },
    vat_percentage: {
        required: false,
        type: "number",
    },
    shortcode: {
        required: true,
        type: "string",
    },
    description: {
        required: false,
        minLength: 2,
        type: "string",
    },
    purchase_price: {
        required: false,
        type: "price",
        min: 0
    },
    sale_price: {
        required: false,
        type: "price",
    },
};
