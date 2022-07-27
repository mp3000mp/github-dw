module.exports = {
    fix: true,
    extends: 'stylelint-config-recommended-scss',
    rules: {
        'color-named': 'never',
        'color-hex-case': 'lower',
        'color-hex-length': 'long',
        'comment-whitespace-inside': 'always',
        'declaration-colon-space-after': 'always',
        'declaration-colon-space-before': 'never',
        'declaration-empty-line-before': 'never',
        'declaration-block-semicolon-space-before': 'never',
        'declaration-block-trailing-semicolon': 'always',
        indentation: 2,
        'length-zero-no-unit': true,
        'number-leading-zero': 'never',
        'number-no-trailing-zeros': true,
        'property-case': 'lower',
        'rule-empty-line-before': ['always',
            {
                ignore: ['first-nested']
            }],
        'selector-list-comma-newline-after': 'always',
        'selector-pseudo-element-no-unknown': [true,
            {
                ignorePseudoElements: ['v-deep']
            }],
        'string-quotes': 'single',
        'unit-case': 'lower',
        'value-keyword-case': 'lower',
        'value-list-comma-space-after': 'always'
    }
}
