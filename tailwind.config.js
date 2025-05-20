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
        // 色系（背景）
        'bg-gray-100', 'bg-gray-200', 'bg-gray-300', 'bg-gray-400',
        'bg-blue-100', 'bg-blue-200', 'bg-blue-300', 'bg-blue-400',
        'bg-green-100', 'bg-green-200', 'bg-green-300',
        'bg-red-100', 'bg-red-200', 'bg-red-300',
        'bg-yellow-100', 'bg-yellow-200', 'bg-yellow-300',
        'bg-cyan-200', 'bg-cyan-500', 'bg-blue-500', 'bg-gray-400',

        // 丸み系
        'rounded-none', 'rounded-sm', 'rounded', 'rounded-md',
        'rounded-lg', 'rounded-xl', 'rounded-2xl', 'rounded-3xl',
        'rounded-full', 'rounded-s-full', 'rounded-e-full',

        // テキスト
        'text-xs', 'text-sm', 'text-base', 'text-lg', 'text-xl', 'text-2xl',
        'text-gray-500', 'text-gray-700', 'text-blue-500', 'text-red-500',

        // ボーダー
        'border', 'border-0', 'border-2', 'border-gray-300', 'border-red-500',

        // リング（枠線）
        'ring-1', 'ring-2', 'ring-4', 'ring-offset-1', 'ring-offset-2',
        'ring-blue-500', 'ring-green-500', 'ring-red-500',
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
