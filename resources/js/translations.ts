import { usePage } from "@inertiajs/vue3";
type Translations = Record<string, string>;

export function __(
    key: string,
    replacements: { [key: string]: string } = {}
): string {
    const translations = (usePage().props.$translations as Translations) || {};

    let translation = translations[key] || key;

    Object.keys(replacements).forEach((r) => {
        translation = translation.replace(`:${r}`, replacements[r]);
    });

    return translation;
}
