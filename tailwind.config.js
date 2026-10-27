import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './app/**/*.php', // clases generadas desde PHP (p. ej. Setting::headline)
    ],

    theme: {
        extend: {
            fontFamily: {
                // Sans humanista redondeada, de peso medio (estilo ECOS)
                sans: ['Cabin', '"Nunito Sans"', ...defaultTheme.fontFamily.sans],
                display: ['Cabin', '"Nunito Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Paleta inspirada en los artes ECOS. Los nombres "ink" y "lion" se conservan para no tocar cada vista:
                // ink = tinta violeta profunda (textos y superficies oscuras), lion = violeta de acento.
                ink: {
                    DEFAULT: '#1a1230',
                    50: '#f8f6fc', 100: '#efeaf8', 200: '#ddd3ef', 300: '#c1b1e0', 400: '#9a85c4',
                    500: '#7a62a8', 600: '#5f4a8a', 700: '#4a376f', 800: '#372856', 900: '#261b3e', 950: '#1a1230',
                },
                lion: {
                    50: '#faf5ff', 100: '#f3e8ff', 200: '#e9d5ff', 300: '#d8b4fe', 400: '#c084fc',
                    500: '#a855f7', 600: '#9333ea', 700: '#7e22ce', 800: '#6b21a8', 900: '#581c87',
                },
                volt: { 400: '#8f9ff0', 500: '#6b7fe0', 600: '#5566c9' },   // azul periwinkle
                aqua: { 400: '#8dd6c5', 500: '#6cc5b0', 600: '#4fa892' },   // menta
                coral: { 400: '#ff8f8b', 500: '#ff6b66', 600: '#e5524d' },
                naranja: { 400: '#f99a52', 500: '#f7791e', 600: '#dc6410' },
                magenta: { 400: '#ef8ff3', 500: '#e865ee', 600: '#cf4bd6' },
                lavanda: '#e4d3f6',
                tarjeta: '#eef0f0',
            },
            borderRadius: { ecos: '18px' },
            backgroundImage: {
                'grid-light': 'linear-gradient(to right, rgb(255 255 255 / .05) 1px, transparent 1px), linear-gradient(to bottom, rgb(255 255 255 / .05) 1px, transparent 1px)',
            },
            keyframes: {
                blob: {
                    '0%,100%': { transform: 'translate(0,0) scale(1)' },
                    '33%': { transform: 'translate(40px,-60px) scale(1.1)' },
                    '66%': { transform: 'translate(-30px,30px) scale(.92)' },
                },
                marquee: { from: { transform: 'translateX(0)' }, to: { transform: 'translateX(-50%)' } },
                'fade-up': { from: { opacity: 0, transform: 'translateY(24px)' }, to: { opacity: 1, transform: 'none' } },
                pop: { '0%': { transform: 'scale(1)' }, '40%': { transform: 'scale(1.35)' }, '100%': { transform: 'scale(1)' } },
                shine: { from: { backgroundPosition: '200% 0' }, to: { backgroundPosition: '-200% 0' } },
            },
            animation: {
                blob: 'blob 18s ease-in-out infinite',
                marquee: 'marquee 40s linear infinite',
                'fade-up': 'fade-up .7s cubic-bezier(.2,.7,.2,1) both',
                pop: 'pop .45s ease-out',
                shine: 'shine 6s linear infinite',
            },
        },
    },

    plugins: [forms],
};
