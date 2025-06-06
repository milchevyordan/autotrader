export const transportOrdersFormRules = {
    owner_id: {
        required: true,
        type: "number",
    },
    transport_company_id: {
        required: false,
        with: 'transport_company_use',
        type: "number",
    },
    transporter_id: {
        required: false,
        type: "number",
    },
    status: {
        required: false,
        type: "number",
        default: 1,
    },
    transport_company_use: {
        required: true,
        type: "boolean",
    },
    vehicle_type: {
        required: true,
        type: "number",
    },
    transport_type: {
        required: true,
        type: "number",
    },
    pick_up_company_id: {
        required: false,
        type: "number",
    },
    pick_up_location_id: {
        required: false,
        type: "number",
    },
    pick_up_location_free_text: {
        required: false,
        type: "string",
        maxLength: 500,
    },
    pick_up_notes: {
        required: false,
        type: "string",
    },
    pick_up_after_date: {
        required: false,
        type: "date",
        max: "deliver_before_date",
    },
    delivery_company_id: {
        required: false,
        type: "number",
    },
    delivery_location_id: {
        required: false,
        type: "number",
    },
    delivery_location_free_text: {
        required: false,
        type: "string",
        maxLength: 500,
    },
    delivery_notes: {
        required: false,
        type: "string",
    },
    deliver_before_date: {
        required: false,
        type: "date",
        min: "pick_up_after_date",
    },
    planned_delivery_date: {
        required: false,
        type: "date",
        min: "pick_up_after_date",
        max: "deliver_before_date",
    },
    total_transport_price: {
        required: false,
        type: "string",
    },
    internal_remarks: {
        complex: true,
    },
};
