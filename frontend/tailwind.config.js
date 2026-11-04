/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{ts,tsx}'],
  theme: {
    extend: {
      colors: {
        navy: {
          950: '#031a2e',
          900: '#062a4a',
          800: '#083b66',
          700: '#0a4a7d',
          600: '#0b5b96',
        },
        brand: {
          50: '#eef8ff',
          100: '#d8efff',
          200: '#b8e2ff',
          300: '#88d0ff',
          400: '#4fb6ff',
          500: '#1e9ee8',
          600: '#0a7bce',
          700: '#0a63a8',
          800: '#0c5288',
          900: '#0f4570',
        },
        gold: {
          400: '#f5c451',
          500: '#e8ae2e',
          600: '#c98b14',
        },
        ink: {
          DEFAULT: '#13283c',
          soft: '#33506b',
          muted: '#5c7589',
          faint: '#8ca3b5',
        },
      },
      fontFamily: {
        display: ['"Space Grotesk"', 'Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        mono: ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', 'monospace'],
      },
      boxShadow: {
        soft: '0 10px 40px -12px rgba(8, 59, 102, 0.18)',
        lift: '0 30px 70px -24px rgba(8, 59, 102, 0.32)',
        glow: '0 0 0 1px rgba(30, 158, 232, 0.18), 0 24px 60px -24px rgba(10, 123, 206, 0.45)',
      },
      backgroundImage: {
        'grid-faint': 'linear-gradient(rgba(10,123,206,.07) 1px, transparent 1px), linear-gradient(90deg, rgba(10,123,206,.07) 1px, transparent 1px)',
        'radial-glow': 'radial-gradient(60% 60% at 50% 0%, rgba(30,158,232,.28) 0%, rgba(6,42,74,0) 70%)',
      },
      backgroundSize: {
        grid: '42px 42px',
      },
      keyframes: {
        float: {
          '0%,100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-12px)' },
        },
        floatSlow: {
          '0%,100%': { transform: 'translateY(0) rotate(0deg)' },
          '50%': { transform: 'translateY(-18px) rotate(1.5deg)' },
        },
        marquee: {
          '0%': { transform: 'translateX(0)' },
          '100%': { transform: 'translateX(-50%)' },
        },
        pulseRing: {
          '0%': { transform: 'scale(.9)', opacity: '0.6' },
          '70%': { transform: 'scale(1.35)', opacity: '0' },
          '100%': { opacity: '0' },
        },
        shimmer: {
          '100%': { transform: 'translateX(100%)' },
        },
      },
      animation: {
        float: 'float 6s ease-in-out infinite',
        'float-slow': 'floatSlow 10s ease-in-out infinite',
        marquee: 'marquee 38s linear infinite',
        'pulse-ring': 'pulseRing 3s cubic-bezier(.2,.6,.2,1) infinite',
      },
    },
  },
  plugins: [],
};
