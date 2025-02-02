/* eslint-env node */
require('@rushstack/eslint-patch/modern-module-resolution');

module.exports = {
    root: true,
    ignores: ['resources/js/Components/ui/*'],
    extends: [
        'plugin:vue/vue3-essential',
        'eslint:recommended',
        '@vue/eslint-config-typescript',
        '@vue/eslint-config-prettier',
    ],
    parserOptions: {
        ecmaVersion: 'latest',
    },
    rules: {
        'vue/multi-word-component-names': 'off',
        'no-undef': 'off',
    },
};
