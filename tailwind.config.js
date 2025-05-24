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
        'bg-gray-200', 'bg-gray-300', 'bg-gray-400', 'bg-gray-500',

        // Blue
        'bg-blue-200', 'bg-blue-300', 'bg-blue-400', 'bg-blue-500',
      
        // Green
        'bg-green-200', 'bg-green-300', 'bg-green-400', 'bg-green-500',
      
        // Red
        'bg-red-200', 'bg-red-300', 'bg-red-400', 'bg-red-500',
      
        // Yellow
        'bg-yellow-200', 'bg-yellow-300', 'bg-yellow-400', 'bg-yellow-500',
      
        // Pink
        'bg-pink-200', 'bg-pink-300', 'bg-pink-400', 'bg-pink-500',
      
        // Purple
        'bg-purple-200', 'bg-purple-300', 'bg-purple-400', 'bg-purple-500',
      
        // Rose
        'bg-rose-200', 'bg-rose-300', 'bg-rose-400', 'bg-rose-500',
      
        // Emerald
        'bg-emerald-200', 'bg-emerald-300', 'bg-emerald-400', 'bg-emerald-500',
      
        // Sky
        'bg-sky-200', 'bg-sky-300', 'bg-sky-400', 'bg-sky-500',
      
        // Cyan
        'bg-cyan-200', 'bg-cyan-300', 'bg-cyan-400', 'bg-cyan-500',
      
        // Indigo
        'bg-indigo-200', 'bg-indigo-300', 'bg-indigo-400', 'bg-indigo-500',

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
