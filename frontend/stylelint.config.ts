import type { Config } from 'stylelint'

const config: Config = {
    extends: 'stylelint-config-recommended-scss',
    customSyntax: 'postcss-html',
    fix: true,
    rules: {
        'color-named': 'never',
        'length-zero-no-unit': true,
        'rule-empty-line-before': ['always', {
            ignore: ['first-nested'],
        }],
        'selector-pseudo-element-no-unknown': [true, {
            ignorePseudoElements: ['v-deep'],
        }],
        'value-keyword-case': 'lower',
    },
    overrides: [
        {
            files: ['**/*.scss'],
            customSyntax: 'postcss-scss',
        },
    ],
}

export default config
