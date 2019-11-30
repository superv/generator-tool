module.exports = {
  productionSourceMap: false,
  css: {
    extract: false,
  },
  configureWebpack: {
    externals: ['vue', 'superv-ui'],
  },
}
