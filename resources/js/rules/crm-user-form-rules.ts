export const crmUserFormRules = {
    prefix: {
        required: false,
        minLength: 2,
        type: "string",
    },
    first_name: {
        required: true,
        minLength: 2,
        type: "string",
    },
    last_name: {
        required: true,
        minLength: 2,
        type: "string",
    },
    company_id: {
        required: true,
        type: "number",
    },
    email: {
        required: true,
        type: "email",
    },
    mobile: {
        required: true,
        type: "string",
        maxLength: 35,
    },
    other_phone: {
        required: false,
        type: "string",
        maxLength: 35,
    },
    gender: {
        required: true,
        type: "number",
    },
    language: {
        required: true,
        type: "number",
    },
    roles: {
        required: true,
        type: "array",
    },
};
