import { App } from "vue";

export default {
    install: (app: App, options: any) => {
        app.config.globalProperties.__ = (
            key: string,
            replacements: Record<string, string> = {}
        ) => {
            let translation = options[key] || key;

            if (replacements) {
                Object.keys(replacements).forEach((r: string) => {
                    translation = translation.replace(`:${r}`, String(replacements[r]));
                });
            }

            return translation;
        };
    },
};
