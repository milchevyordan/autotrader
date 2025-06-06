export const roleFormRules = {
    name: {
        required: true,
        minLength: 2,
        type: "string",
    },
    permissions: {
        required: false,
        type: "array",
    },
};
