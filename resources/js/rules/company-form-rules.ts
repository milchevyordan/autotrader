export const companyFormRules = {
    name: {
        required: true,
        minLength: 2,
        type: "string",
    },
    email: {
        required: false,
        type: "email",
    },
    phone: {
        required: true,
        type: "string",
        maxLength: 25,
    },
    country: {
        required: true,
        type: "number",
    },
    company_group_id: {
        required: false,
        type: "number",
    },
    default_currency: {
        required: true,
        type: "number",
    },
    vat_percentage: {
        required: false,
        type: "number",
    },
    city: {
        required: true,
        type: "string",
    },
    address: {
        required: true,
        type: "string",
    },
    postal_code: {
        required: true,
        type: "string",
        maxLength: 25,
    },
    kvk_number: {
        required: false,
        type: "number",
        minLength: 8,
    },
    bank_name: {
        required: false,
        type: "string",
        minLength: 2,
    },
    vat_number: {
        required: false,
        type: "string",
    },
    iban: {
        required: false,
        type: "string",
        minLength: 15,
        maxLength: 34,
    },
    swift_or_bic: {
        required: false,
        type: "string",
        minLength: 8,
        maxLength: 11,
    },
    addresses: {
        complex: true,
    },
};
