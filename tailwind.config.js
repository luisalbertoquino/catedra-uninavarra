import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                display: ['"Space Grotesk"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Paleta de marca: ajustar aquí si Comunicaciones entrega el manual de identidad
                ink: {
                    DEFAULT: '#070a1a',
                    50: '#f4f5fb', 100: '#e6e8f5', 200: '#c9cdea', 300: '#9aa1d0', 400: '#6871ad',
                    500: '#454e8c', 600: '#333b70', 700: '#232a55', 800: '#151a3a', 900: '#0c1029', 950: '#070a1a',
                },
                lion: {
                    50: '#fffbea', 100: '#fff3c4', 200: '#ffe685', 300: '#ffd445', 400: '#fdc31b',
                    500: '#f5b70a', 600: '#d98f02', 700: '#b46606', 800: '#924e0c', 900: '#78400d',
                },
                volt: { 400: '#a78bfa', 500: '#8b5cf6', 600: '#7c3aed' },
                aqua: { 400: '#22d3ee', 500: '#06b6d4' },
            },
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
