/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,jsx}'],
  theme: {
    extend: {
      colors: {
        brand: {
          red: '#f21d30',
          redHover: '#d91426',
          dark: '#141416',
          darkSlate: '#1d1f23',
          charcoal: '#22252a',
          cream: '#fdfbf7',
          beige: '#f6f2ea',
          warmBorder: '#eae3d5',
          muted: '#686a71',
        },
      },
      fontFamily: {
        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
        serif: ['"Playfair Display"', 'serif'],
      },
    },
  },
  plugins: [],
}
