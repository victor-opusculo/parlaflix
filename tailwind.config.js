/** @type {import('tailwindcss').Config} */
module.exports = {
  content: 
  [
    "./app/**/*.{html,php,js}",
    "./components/**/*.{html,php,js}",
    "./client-components/bricks/**/*.{html,php,js}"
  ],
  darkMode: 'class',
  theme: {
    extend: 
    {
      animation:
      {
        'dark-mode-button': 'darkModeToggle 1s ease-in-out'
      },
      keyframes:
      {
        darkModeToggle:
        {
          '0%, 100%': { opacity: 1 },
          '50%': { opacity: 0 }
        }
      }
    },
  },
  plugins: [],
}

