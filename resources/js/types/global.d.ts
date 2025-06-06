import { PageProps as InertiaPageProps } from "@inertiajs/core";
import { AxiosInstance } from "axios";
import { route as routeFn } from "ziggy-js";
import { PageProps as AppPageProps } from "./";
import { trans } from "laravel-vue-i18n";

// Existing declarations...

export declare function permissions(key: string): boolean;
export declare function hasRole(key: string | Array<string>): boolean;

declare global {
    interface Window {
        axios: AxiosInstance;
        $translations: { [key: string]: string };
    }

    var route: typeof routeFn;
    var __: typeof trans;
    var Ziggy: ZiggyConfig;
}

declare module "vue" {
    interface ComponentCustomProperties {
        route: typeof ziggyRoute;
        __: typeof __;
        $can: typeof permissions;
        $hasRole: typeof hasRole;
    }
}

declare module "@inertiajs/core" {
    interface PageProps extends InertiaPageProps, AppPageProps {}
}
