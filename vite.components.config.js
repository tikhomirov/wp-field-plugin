import { defineConfig } from 'vite';
import { resolve } from 'path';

/**
 * WP_Field Components page — vanilla JS enhancement (no React needed).
 * Builds a self-contained IIFE bundle for sidebar search, scroll tracking, etc.
 */
export default defineConfig({
  define: {
    'process.env.NODE_ENV': JSON.stringify('production'),
  },
  build: {
    outDir: 'examples/components/assets',
    emptyOutDir: false,
    lib: {
      entry: resolve(__dirname, 'assets/src/wp-field-components.jsx'),
      name: 'WpFieldComponents',
      fileName: () => 'wp-field-components.js',
      formats: ['iife'],
    },
    rollupOptions: {
      output: {
        assetFileNames: '[name][extname]',
      },
    },
  },
});
