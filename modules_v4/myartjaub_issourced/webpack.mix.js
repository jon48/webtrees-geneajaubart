/**
 * Laravel mix - MyArtJaub IsSourced module
 *
 * Output:
 *      - default.min.css
 *      - clouds.min.css
 *      - colors.min.css
 *      - fab.min.css
 *      - minimal.min.css
 *      - webtrees.min.css
 *      - xenea.min.css
 *      - _myartjaub_ruraltheme_.min.css
 */

const mix = require('laravel-mix');
require('laravel-mix-clean');

// https://github.com/postcss/autoprefixer
const postcssAutoprefixer = require('autoprefixer')();

// https://github.com/jakob101/postcss-inline-rtl
const postcssRtl = require('@mjhenkes/postcss-rtl')();

// https://github.com/bezoerb/postcss-image-inliner
const postcssImageInliner = require('postcss-image-inliner')({
  assetPaths: ['src/sass/resources/images'],
  maxFileSize: 0
});

// https://github.com/postcss/postcss-custom-properties
// Enable CSS variables in IE
const postcssCustomProperties = require('postcss-custom-properties')();

mix
  .setPublicPath('resources')
  .js('src/js/issourced.js', 'js/issourced.min.js')
  .sass('src/sass/default.scss', 'css/default.min.css')
  .sass('src/sass/clouds.scss', 'css/clouds.min.css')
  .sass('src/sass/colors.scss', 'css/colors.min.css')
  .sass('src/sass/fab.scss', 'css/fab.min.css')
  .sass('src/sass/minimal.scss', 'css/minimal.min.css')
  .sass('src/sass/webtrees.scss', 'css/webtrees.min.css')
  .sass('src/sass/xenea.scss', 'css/xenea.min.css')
  .sass('src/sass/rural.scss', 'css/_myartjaub_ruraltheme_.min.css')
  .options({
    processCssUrls: false,
    postCss: [
      postcssRtl,
      postcssAutoprefixer,
      postcssImageInliner,
      postcssCustomProperties
    ]
  })
  .clean({
    cleanOnceBeforeBuildPatterns: ['css/**/*', 'js/**/*']
  })
;
