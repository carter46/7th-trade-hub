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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: '#0B6A39',
                accent: '#16A34A',
                dark: '#0F172A',
                glass: 'rgba(30, 41, 59, 0.7)',
                'background-dark': '#0F172A',
                'background-light': '#f8f6f6',
                'card-dark': '#1E293B',
                'text-primary': '#F1F5F9',
                'text-secondary': '#94A3B8',
                'surface-dark': '#1E293B',
                'border-dark': '#334155',
            },
            backgroundImage: {
                'gradient-primary': 'linear-gradient(135deg, #0B6A39 0%, #053b1f 100%)',
            },
            backdropBlur: {
                xs: '2px',
            },
        },
    },

    plugins: [forms],
};
