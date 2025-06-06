export const workOrdersFormRules = {
    owner_id: {
        required: true,
        type: "number",
    },
    vehicleable_id: {
        required: true,
        type: "number",
    },
    type: {
        required: true,
        type: "number",
    },
    total_price: {
        required: false,
        type: "price",
        min: 0,
    },
    internal_remarks: {
        complex: true,
    },
};
