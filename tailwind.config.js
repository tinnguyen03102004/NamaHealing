/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./*.php", "./**/*.php"],
  theme: {
    extend: {
      keyframes: {
        wave: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-6px)' },
        },
      },
    },
  },
  plugins: [],
};
