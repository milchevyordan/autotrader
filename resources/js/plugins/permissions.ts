import { App } from "vue";
import { usePage } from "@inertiajs/vue3";

export const $can = (permission: string) => {
    const permissions = usePage().props.auth.user.permissions;
    return permissions.includes(permission);
};

export default {
    install: (app: App) => {
        app.config.globalProperties.$can = $can;
    },
};
