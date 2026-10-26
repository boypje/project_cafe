const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                display: ['"Playfair Display"', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                // Espresso: teks, tombol utama, sidebar
                ink: {
                    50: '#F7F4F1', 100: '#EDE7E1', 200: '#DBD0C6', 300: '#BFAE9F', 400: '#9C8574',
                    500: '#7D6656', 600: '#64503F', 700: '#4E3E32', 800: '#3A2E26', 900: '#2A1F19', 950: '#1C1410',
                },
                // Karamel: aksen brand, harga, status aktif
                brand: {
                    50: '#FBF5EE', 100: '#F5E6D3', 200: '#EACBA6', 300: '#DCA971', 400: '#CC8746',
                    500: '#B86E2E', 600: '#9A5723', 700: '#7E461F', 800: '#67391D', 900: '#55301A',
                },
                cream: '#FAF7F2',
            },
            boxShadow: {
                soft: '0 1px 2px rgba(42,31,25,.04), 0 4px 16px -4px rgba(42,31,25,.08)',
                lift: '0 8px 30px -8px rgba(42,31,25,.25)',
            },
            keyframes: {
                'pop-in': { '0%': { transform: 'scale(.96)', opacity: 0 }, '100%': { transform: 'scale(1)', opacity: 1 } },
                'slide-up': { '0%': { transform: 'translateY(8px)', opacity: 0 }, '100%': { transform: 'translateY(0)', opacity: 1 } },
                bump: { '0%,100%': { transform: 'scale(1)' }, '50%': { transform: 'scale(1.15)' } },
            },
            animation: {
                'pop-in': 'pop-in .18s ease-out',
                'slide-up': 'slide-up .2s ease-out',
                bump: 'bump .25s ease-out',
            },
        },
    },

    plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
};
