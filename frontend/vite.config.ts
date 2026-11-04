import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

// The SPA is served by the PHP shell (index.php) from /app. Assets are
// referenced relatively and the PHP shell injects a <base href> so the
// same build works at the domain root and in /reagansoft-innovation/.
export default defineConfig({
  plugins: [react()],
  base: './',
  build: {
    outDir: '../app',
    emptyOutDir: true,
    sourcemap: false,
    target: 'es2019',
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['react', 'react-dom', 'react-router-dom'],
          icons: ['lucide-react'],
        },
      },
    },
  },
});
