export const serviceLevelFormRules = {
    name: {
        required: true,
        type: "string",
    },
    type: {
        required: true,
        type: "number",
    },
    type_of_sale: {
        required: true,
        type: "number",
    },
    transport_included: {
        required: true,
        type: "boolean",
    },
    damage: {
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
    discount_in_output: {
        required: true,
        type: "boolean",
    },
    rdw_company_number: {
        required: false,
        type: "string",
    },
    login_autotelex: {
        required: false,
        type: "string",
    },
    api_japie: {
        required: false,
        type: "string",
    },
    bidder_name_autobid: {
        required: false,
        type: "string",
    },
    bidder_number_autobid: {
        required: false,
        type: "string",
    },
    api_vwe: {
        required: false,
        type: "string",
    },
    items: {
        required: false,
        type: "array",
    },
    companies: {
        complex: true
    },
    additional_services: {
        complex: true
    },
};
