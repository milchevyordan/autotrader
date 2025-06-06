import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    safelist: [
        'bg-amber-100',
        'bg-blue-500',
        'bg-amber-900',
        'bg-yellow-700',
        'bg-yellow-400',
        'bg-gray-500',
        'bg-green-500',
        'bg-red-500',
        'bg-black',
        'bg-gray-300',
        'bg-purple-500',
        'bg-white',
        'bg-orange-500',
        'bg-yellow-500'
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
