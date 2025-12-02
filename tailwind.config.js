import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Tajawal', 'ui-sans-serif', 'system-ui'],
            },
            colors: {
                primary: '#FFD600',
                secondary: '#000000',
            },
            borderRadius: {
                'lg': '1rem',
                'xl': '1.5rem',
            },
        },
    },

    plugins: [forms],
};
