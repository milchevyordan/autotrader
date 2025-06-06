export const workOrderTasksFormRules = {
    assigned_to_id: {
        required: false,
        type: "number",
    },
    work_order_id: {
        required: true,
        type: "number",
    },
    name: {
        required: true,
        type: "string",
    },
    description: {
        required: false,
        type: "string",
    },
    type: {
        required: true,
        type: "number",
    },
    status: {
        required: true,
        type: "number",
    },
    planned_date: {
        required: false,
        type: "date",
    },
    estimated_price: {
        required: true,
        type: "price",
        min: 0
    },
    actual_price: {
        complex: true,
    },
};
