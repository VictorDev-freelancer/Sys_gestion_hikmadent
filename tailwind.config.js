import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    safelist: [
        // Colores dinámicos usados en el AreaDashboard (Kanban + Calendario)
        ...['blue', 'amber', 'emerald', 'yellow', 'green', 'red', 'indigo', 'gray'].flatMap(color => [
            `bg-${color}-50`, `bg-${color}-100`, `bg-${color}-200`, `bg-${color}-500`,
            `text-${color}-300`, `text-${color}-400`, `text-${color}-600`, `text-${color}-700`, `text-${color}-800`,
            `border-${color}-200`, `border-${color}-300`, `border-${color}-500`,
            `border-l-4`,
            `hover:bg-${color}-200`, `hover:bg-${color}-300`,
            `hover:border-${color}-300`,
        ]),
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms, typography],
};
