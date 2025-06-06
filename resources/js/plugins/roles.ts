import { usePage } from "@inertiajs/vue3";
import { App } from "vue";

export default {
    install: (app: App) => {
        app.config.globalProperties.$hasRole = (
            rolesCheck: string | Array<string>
        ) => {
            const userRoles = usePage().props.auth.user.roles;

            return typeof rolesCheck == "string"
                ? userRoles.includes(rolesCheck)
                : userRoles.some((element) => rolesCheck.includes(element));
        };
    },
};
