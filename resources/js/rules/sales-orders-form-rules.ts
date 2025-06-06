export const salesOrdersFormRules = {
    owner_id: {
        required: true,
        type: "number",
    },
    reference: {
        required: false,
        type: "string",
    },
    customer_company_id: {
        required: false,
        type: "number",
    },
    customer_id: {
        required: false,
        type: "number",
    },
    seller_id: {
        required: false,
        type: "number",
    },
    service_level_id: {
        required: false,
        type: "number",
    },
    status: {
        required: false,
        type: "number",
        default: 1,
    },
    type_of_sale: {
        required: false,
        type: "number",
    },
    transport_included: {
        required: true,
        type: "boolean",
    },
    vat_deposit: {
        required: true,
        type: "boolean",
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
    total_sales_price: {
        required: false,
        type: "price",
    },
    total_fee_intermediate_supplier: {
        required: false,
        type: "price",
    },
    total_sales_price_exclude_vat: {
        required: false,
        type: "price",
    },
    total_vat: {
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
    calculation_on_sales_order: {
        required: true,
        type: "boolean",
    },
    total_sales_price_include_vat: {
        required: false,
        type: "price",
    },
    currency_rate: {
        required: false,
        type: "number",
        min: 0,
    },
    currency: {
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
    damage: {
        required: false,
        type: "number",
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
