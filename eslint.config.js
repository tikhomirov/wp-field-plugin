import js from '@eslint/js';
import reactPlugin from 'eslint-plugin-react';
import reactHooksPlugin from 'eslint-plugin-react-hooks';
import reactRefreshPlugin from 'eslint-plugin-react-refresh';
import prettierConfig from 'eslint-config-prettier/flat';
import prettierPlugin from 'eslint-plugin-prettier';

export default [
  {
    ignores: [
      'assets/dist/**',
      'dist/**',
      'examples/components/assets/**',
      'vendor/**',
      'node_modules/**',
      'coverage/**',
      'vanilla/assets/css/**',
    ],
  },
  {
    ...js.configs.recommended,
    rules: {
      ...js.configs.recommended.rules,
      'no-unused-vars': [
        'error',
        { caughtErrors: 'none', varsIgnorePattern: '^self$|^fieldId$' },
      ],
    },
  },
  {
    files: ['assets/src/**/*.{js,jsx}'],
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      parserOptions: {
        ecmaFeatures: {
          jsx: true,
        },
      },
      globals: {
        document: 'readonly',
        window: 'readonly',
        URL: 'readonly',
        console: 'readonly',
        history: 'readonly',
        IntersectionObserver: 'readonly',
        requestAnimationFrame: 'readonly',
      },
    },
    plugins: {
      react: reactPlugin,
      'react-hooks': reactHooksPlugin,
      'react-refresh': reactRefreshPlugin,
      prettier: prettierPlugin,
    },
    settings: {
      react: {
        version: 'detect',
      },
    },
    rules: {
      ...reactPlugin.configs.recommended.rules,
      ...reactHooksPlugin.configs.recommended.rules,
      'react/react-in-jsx-scope': 'off',
      'react/prop-types': 'off',
      'react-refresh/only-export-components': 'off',
      'prettier/prettier': 'error',
    },
  },
  {
    files: ['assets/js/**/*.js', 'vanilla/assets/js/**/*.js', 'vite*.js'],
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: {
        console: 'readonly',
        document: 'readonly',
        window: 'readonly',
        localStorage: 'readonly',
        navigator: 'readonly',
        alert: 'readonly',
        Blob: 'readonly',
        URL: 'readonly',
        Date: 'readonly',
        setTimeout: 'readonly',
        clearTimeout: 'readonly',
        jQuery: 'readonly',
        wp: 'readonly',
        google: 'readonly',
        L: 'readonly',
        __dirname: 'readonly',
      },
    },
    plugins: {
      prettier: prettierPlugin,
    },
    rules: {
      'no-var': 'error',
      'prefer-const': 'error',
      'prettier/prettier': 'error',
    },
  },
  prettierConfig,
];
