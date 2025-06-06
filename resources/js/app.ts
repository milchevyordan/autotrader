import "./bootstrap";
import "../css/app.css";
import "../fonts/bunny.css";

import { createApp, h, DefineComponent } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "ziggy-js";
import { globalInputErrors, flashMessages } from "@/globals";
import TranslationPlugin from "./plugins/translation-plugin";
import roles from "./plugins/roles";
import permissions from "./plugins/permissions";

const appName =
    window.document.getElementsByTagName("title")[0]?.innerText || "Laravel";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>("./pages/**/*.vue")
        ),

    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(TranslationPlugin, props.initialPage.props.$translations)
            .use(roles)
            .use(permissions)
            .provide("globalInputErrors", globalInputErrors)
            .provide("flashMessages", flashMessages)
            .mount(el);
    },

    progress: {
        color: "#A4CAFE",
        showSpinner: true,
    },
});
