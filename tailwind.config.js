import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    safelist: [
        'bg-red-200', 'bg-orange-200', 'bg-amber-200', 'bg-yellow-200',
        'bg-lime-200', 'bg-green-200', 'bg-emerald-200', 'bg-teal-200',
        'bg-cyan-200', 'bg-sky-200', 'bg-blue-200', 'bg-indigo-200',
        'bg-violet-200', 'bg-purple-200', 'bg-fuchsia-200',
        'bg-pink-200', 'bg-rose-200',
        'bg-cyan-500',
        'bg-blue-500',
        'bg-gray-400',
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
