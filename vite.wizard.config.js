import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

export default defineConfig({
  plugins: [react()],
  define: {
    'process.env.NODE_ENV': JSON.stringify('production'),
  },
  build: {
    outDir: 'assets/dist',
    emptyOutDir: false,
    lib: {
      entry: resolve(__dirname, 'assets/src/wizard.jsx'),
      name: 'WpFieldWizard',
      fileName: () => 'wizard.js',
      formats: ['iife'],
    },
    rollupOptions: {
      output: {
        assetFileNames: '[name].[ext]',
      },
    },
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, 'assets/src'),
    },
  },
});
