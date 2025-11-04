// postcss.config.cjs
const { default: purgecss } = require('@fullhuman/postcss-purgecss')
const cssnano = require('cssnano')

const isProd = process.env.NODE_ENV === 'production'

module.exports = {
  plugins: [
    isProd &&
      purgecss({
        content: [
          './resources/js/**/*.vue',
          './resources/views/**/*.blade.php',
          './resources/js/**/*.js',
          './public/**/*.html',
        ],
        defaultExtractor: (content) => content.match(/[A-Za-z0-9-_:/]+/g) || [],
        safelist: {
          standard: [
            // navbar / bootstrap-ish
            'show', 'collapse', 'collapsing', 'navbar-collapse', 'navbar', 'nav', 'active','showMobileMenu',
            // wow/animate
            'wow', 'animated', 'fadeInUp', 'fadeIn', 'fade',
            // grid/utils
            /^col-/, /^row$/, /^container/, /^g-/, /^justify-/, /^align-/, /^d-/,
            // buttons
            /^btn/, /^button/, /^radius-/,
            // font awesome
            /^fa-/, /^fa$/, 'fa-solid', 'fa-regular',
            // tiny-slider
            /^tns-/, 'tns', 'tns-slide-active',
          ],
        },
      }),
    isProd && cssnano({ preset: 'default' }),
  ].filter(Boolean),
}
