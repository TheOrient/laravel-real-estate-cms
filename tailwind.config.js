/** @type {import('tailwindcss').Config} */
export default {
  // Dark mode controlled by class instead of media query
  darkMode: 'class',

  // Content paths for Laravel project structure
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./storage/framework/views/*.php",
    "./resources/views/**/*.blade.php",
    "./tasarim/**/*.html",  // Added to include HTML templates
  ],

  theme: {
    extend: {
      // Colors for real estate system
      colors: {
        primary: '#1E6F5C', // Real estate green
        'primary-dark': '#13493E', // Darker green for hover states
        secondary: '#F5F7F8', // Light background gray
        dark: '#0F1F1A', // Footer dark background
        text: '#1A1A1A', // Main text color
      },
      // Custom border radius
      borderRadius: {
        'none': '0px',
        'sm': '4px',
        DEFAULT: '8px',
        'md': '12px',
        'lg': '16px',
        'xl': '20px',
        '2xl': '24px',
        '3xl': '32px',
        'full': '9999px',
        'button': '8px',
      },
      // Extending theme with custom font family for real estate system
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        pacifico: ['Pacifico', 'cursive'],
      },
      // Container settings for real estate system
      container: {
        center: true,
        padding: '1.5rem',
        screens: {
          sm: '640px',
          md: '768px',
          lg: '1024px',
          xl: '1320px', // Match the max-w-[1320px] from real estate template
        },
      },
    },
  },

  plugins: [],
}