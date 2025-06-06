export const serviceVehicleFormRules = {
    vehicle_type: {
        required: true,
        type: "number",
    },
    make_id: {
        required: true,
        type: "number",
    },
    vehicle_model_id: {
        required: true,
        type: "number",
    },
    variant_id: {
        required: false,
        type: "number",
    },

    co2_type: {
        required: true,
        type: "number",
    },
    co2_value: {
        required: true,
        type: "number",
    },
    kilometers: {
        required: true,
        type: "number",
    },
    nl_registration_number: {
        required: false,
        type: "string",
    },
    current_registration: {
        required: true,
        type: "number",
    },
    vin: {
        required: true,
        type: "string",
        minLength: 17,
        maxLength: 17,
    },
    first_registration_date: {
        required: true,
        type: "date",
    },
};
