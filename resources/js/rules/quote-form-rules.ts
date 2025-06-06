export const quoteFormRules = {
    owner_id: {
        required: true,
        type: "number",
    },
    type: {
        required: false,
        type: "number",
        default: 1,
    },
    seller_id: {
        required: false,
        type: "number",
    },
    name: {
        required: false,
        type: "string",
    },
    service_level_id: {
        required: false,
        type: "number",
    },
    delivery_week: {
        complex: true,
        type: "week_range",
    },
    payment_condition: {
        required: false,
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
    discount_in_output: {
        required: true,
        type: "boolean",
    },
    transport_included: {
        required: true,
        type: "boolean",
    },
    type_of_sale: {
        required: false,
        type: "number",
    },
    damage: {
        required: false,
        type: "number",
    },
    down_payment: {
        required: true,
        type: "boolean",
    },
    down_payment_amount: {
        required: false,
        type: "price",
        with: "down_payment",
    },
    email_text: {
        required: false,
        type: "string",
    },
    additional_info_conditions: {
        required: false,
        type: "text",
    },
    total_purchase_price: {
        required: false,
        type: "price",
        min: 0,
    },
    total_fee_intermediate_supplier: {
        required: false,
        type: "price",
    },
    total_sales_price_exclude_vat: {
        required: false,
        type: "price",
    },
    total_quote_price_exclude_vat: {
        required: false,
        type: "price",
    },
    total_registration_fees: {
        required: false,
        type: "price",
    },
    total_quote_price: {
        required: false,
        type: "price",
    },
    total_vat: {
        required: false,
        type: "price",
    },
    total_sales_price_include_vat: {
        required: false,
        type: "price",
    },
    total_bpm: {
        required: false,
        type: "price",
    },
    is_brutto: {
        required: true,
        type: "boolean",
    },
    calculation_on_quote: {
        required: true,
        type: "boolean",
    },
    internal_remarks: {
        complex: true,
    },
    items: {
        complex: true,
    },
    additional_services: {
        complex: true,
    },
};
