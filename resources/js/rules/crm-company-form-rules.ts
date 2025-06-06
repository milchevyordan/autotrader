export const crmCompanyFormRules = {
    type: {
        required: true,
        type: "number",
    },
    company_group_id: {
        required: false,
        type: "number",
    },
    main_user_id: {
        required: false,
        type: "number",
    },
    billing_contact_id: {
        required: false,
        type: "number",
    },
    logistics_contact_id: {
        required: false,
        type: "number",
    },
    default_currency: {
        required: true,
        type: "number",
    },
    purchase_type: {
        required: false,
        type: "number",
    },
    country: {
        required: true,
        type: "number",
    },
    name: {
        required: true,
        minLength: 2,
        type: "string",
    },
    number: {
        required: false,
        type: "string",
    },
    number_addition: {
        required: false,
        type: "string",
    },
    postal_code: {
        required: true,
        type: "string",
        maxLength: 25,
    },
    city: {
        required: true,
        type: "string",
    },
    address: {
        required: true,
        type: "string",
    },
    province: {
        required: false,
        type: "string",
    },
    street: {
        required: false,
        type: "string",
    },
    address_number: {
        required: false,
        type: "string",
    },
    address_number_addition: {
        required: false,
        type: "string",
    },
    vat_number: {
        required: false,
        type: "string",
    },
    company_number_accounting_system: {
        required: false,
        type: "string",
    },
    debtor_number_accounting_system: {
        required: false,
        type: "string",
    },
    creditor_number_accounting_system: {
        required: false,
        type: "string",
    },
    website: {
        required: false,
        type: "string",
        maxLength: 550,
    },
    email: {
        required: false,
        type: "email",
    },
    bank_name: {
        required: false,
        type: "string",
        minLength: 2,
    },
    phone: {
        required: false,
        type: "string",
        maxLength: 25,
    },
    iban: {
        required: false,
        type: "string",
        minLength: 15,
        maxLength: 34,
    },
    kvk_number: {
        required: false,
        type: "number",
        minLength: 8,
    },
    swift_or_bic: {
        required: false,
        type: "string",
        minLength: 8,
        maxLength: 11,
    },
    billing_remarks: {
        required: false,
        type: "text",
    },
    logistics_times: {
        required: false,
        type: "string",
    },
    logistics_remarks: {
        required: false,
        type: "text",
    },
    addresses: {
        complex: true,
    },
};
