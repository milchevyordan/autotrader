export const preOrdersFormRules = {
    owner_id: {
        required: true,
        type: "number",
    },
    supplier_company_id: {
        required: true,
        type: "number",
    },
    supplier_id: {
        required: false,
        type: "number",
    },
    intermediary_company_id: {
        required: false,
        type: "number",
    },
    intermediary_id: {
        required: false,
        type: "number",
    },
    pre_order_vehicle_id: {
        required: false,
        type: "number",
    },
    type: {
        required: true,
        type: "number",
    },
    transport_included: {
        required: false,
        type: "number",
    },
    vat_deposit: {
        required: false,
        type: "number",
    },
    amount_of_vehicles: {
        required: true,
        type: "number",
    },
    down_payment: {
        required: true,
        type: "boolean",
    },
    vat_percentage: {
        required: false,
        type: "number",
    },
    total_purchase_price: {
        required: false,
        type: "price",
        min: 0,
    },
    total_purchase_price_eur: {
        required: false,
        type: "price",
        min: 0,
    },
    total_fee_intermediate_supplier: {
        required: false,
        type: "price",
    },
    total_purchase_price_exclude_vat: {
        required: false,
        type: "price",
        min: 0,
    },
    total_vat: {
        required: false,
        type: "price",
    },
    total_bpm: {
        required: false,
        type: "price",
    },
    total_purchase_price_include_vat: {
        required: false,
        type: "price",
        min: 0,
    },
    currency_rate: {
        required: false,
        type: "number",
        min: 0,
    },
    vat: {
        required: false,
        type: "number",
    },
    bpm: {
        required: false,
        type: "number",
    },
    down_payment_amount: {
        required: false,
        type: "price",
        with: "down_payment",
    },
    currency_po: {
        required: true,
        type: "number",
    },
    contact_notes: {
        required: false,
        type: "text",
    },
    document_from_type: {
        required: true,
        type: "number",
    },
    purchaser_id: {
        required: true,
        type: "number",
    },
    internal_remarks: {
        complex: true,
    },
};
