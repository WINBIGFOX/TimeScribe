import vueTsEslintConfig from '@vue/eslint-config-typescript'
import pluginVue from 'eslint-plugin-vue'

export default [
    {
        name: 'app/files-to-lint',
        files: ['**/*.{ts,vue,js}']
    },
    {
        name: 'app/files-to-ignore',
        ignores: ['resources/js/ziggy.d.ts', 'resources/js/ziggy.js', 'resources/js/Components/ui/*']
    },
    ...pluginVue.configs['flat/essential'],
    ...vueTsEslintConfig(),
    {
        rules: {
            'no-console': 'error',
            'no-debugger': 'error',
            'vue/multi-word-component-names': 'off'
        }
    }
]
