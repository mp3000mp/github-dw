// eslint-disable-next-line
const StyleLintPlugin = require('stylelint-webpack-plugin')
const { defineConfig } = require('@vue/cli-service')

module.exports = defineConfig({
  transpileDependencies: true,
  configureWebpack: {
    plugins: [
      new StyleLintPlugin({
        files: ['src/**/*.{scss}'],
        fix: true
      })
    ]
  }
})
