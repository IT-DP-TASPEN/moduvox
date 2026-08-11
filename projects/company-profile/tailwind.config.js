/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                headline: ['Manrope', 'Inter', 'sans-serif'],
                body: ['Inter', 'sans-serif'],
            },
            colors: {
                // Core Industrial Palette
                primary: '#B91C1C', // Industrial Red
                'primary-dark': '#991B1B',
                'on-primary': '#FFFFFF',
                
                surface: '#0F172A', // Deep Navy Surface
                'on-surface': '#F8FAFC',
                'on-surface-variant': '#94A3B8',
                
                background: '#F1F5F9', // Industrial Light Gray
                'on-background': '#0F172A',
                
                outline: '#CBD5E1',
                'outline-variant': '#E2E8F0',
            },
            borderRadius: {
                none: '0',
                sm: '0.0625rem',
                DEFAULT: '0.125rem',
                md: '0.25rem',
                lg: '0.375rem',
                full: '9999px',
            },
        },
    },
    plugins: [require('@tailwindcss/forms')],
};
