import eslint from '@eslint/js';

export default [
  {
    ignores: [
      'dist/**',
      'node_modules/**',
      'playwright-report/**',
      'test-results/**',
      'vendor/**',
      'wp-content/uploads/**',
    ],
  },
  eslint.configs.recommended,
  {
    files: [
      'wp-content/themes/estatein/assets/js/**/*.js',
      'wp-content/plugins/estatein-core/assets/js/**/*.js',
    ],
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: {
        document: 'readonly',
        HTMLElement: 'readonly',
        HTMLDialogElement: 'readonly',
        IntersectionObserver: 'readonly',
        localStorage: 'readonly',
        location: 'readonly',
        matchMedia: 'readonly',
        navigator: 'readonly',
        requestAnimationFrame: 'readonly',
        estateinPropertyGallery: 'readonly',
        wp: 'readonly',
        window: 'readonly',
      },
    },
  },
  {
    files: ['scripts/**/*.mjs', 'tests/**/*.mjs', 'playwright.config.mjs'],
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: {
        Buffer: 'readonly',
        URL: 'readonly',
        console: 'readonly',
        document: 'readonly',
        process: 'readonly',
      },
    },
  },
];
