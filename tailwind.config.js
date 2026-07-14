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
                display: ['Poppins', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            maxWidth: {
                'content-sm': '42rem',
                'content-md': '56rem',
                'content': '80rem',
                'content-lg': '96rem',
                'content-full': '100%',
                'form': '32rem',
                'auth': '28rem',
                'marketing': '80rem',
            },
            spacing: {
                'section': '1.5rem',
            },
            colors: {
                primary: '#0B6A39',
                accent: '#16A34A',
                success: '#16A34A',
                warning: '#F59E0B',
                danger: '#EF4444',
                surface: '#0F172A',
                card: {
                    DEFAULT: 'rgba(30, 41, 59, 0.6)',
                    solid: '#1E293B',
                },
                muted: '#334155',
                elevated: '#1E293B',
                'text-primary': '#F1F5F9',
                'text-secondary': '#94A3B8',
                'text-muted': '#64748B',
                'border-default': '#334155',
                'border-subtle': 'rgba(255, 255, 255, 0.1)',
                dark: '#0F172A',
                glass: 'rgba(30, 41, 59, 0.7)',
                'background-dark': '#0F172A',
                'background-light': '#f8f6f6',
                'card-dark': '#1E293B',
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
