export default {
  extends: ['stylelint-config-standard'],
  ignoreFiles: ['**/node_modules/**', '**/dist/**', '**/vendor/**'],
  rules: {
    'alpha-value-notation': null,
    'color-function-notation': null,
    'color-hex-length': 'long',
    'custom-property-pattern': null,
    'declaration-block-no-redundant-longhand-properties': null,
    'font-family-name-quotes': null,
    'no-descending-specificity': null,
    'selector-class-pattern': null,
  },
};
