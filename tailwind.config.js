import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms'; 

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './node_modules/flowbite/**/*.js', 
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'imi-blue': '#061941', 
                'imi-red': '#E50019',
                'imi-blue-700': '#0d2d6d', 
                'imi-blue-800': '#0d2251', 
                
            }
        },
    },

    plugins: [
        forms, 
        require('flowbite/plugin') 
    ],
};