// postcss.config.cjs
const cssnano = require('cssnano')

const isProd = process.env.NODE_ENV === 'production'

module.exports = {
  plugins: [
    isProd && cssnano({ preset: 'default' }),
  ].filter(Boolean),
}
