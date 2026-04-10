import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

/**
 * Отдельная конфигурация для сборки admin-shell в IIFE формате.
 * Нужен self-contained бандл (React внутри) для подключения через
 * стандартный wp_enqueue_script без поддержки ES модулей.
 */
export default defineConfig({
  plugins: [react()],
  /**
   * define: заменяет process.env.NODE_ENV на строку "production" при сборке.
   * Необходимо для IIFE-бандла: в браузере нет глобального process,
   * и без замены React падает с ReferenceError, не давая скрипту загрузиться.
   */
  define: {
    'process.env.NODE_ENV': JSON.stringify('production'),
  },
  build: {
    outDir: 'assets/dist',
    emptyOutDir: false,
    lib: {
      entry: resolve(__dirname, 'assets/src/admin-shell.jsx'),
      name: 'WpFieldAdminShell',
      fileName: () => 'admin-shell.js',
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
