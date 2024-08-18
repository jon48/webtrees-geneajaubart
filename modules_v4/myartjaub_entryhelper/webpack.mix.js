/**
 * Laravel mix - MyArtJaub Entry Helper module
 */

const mix = require('laravel-mix');
require('laravel-mix-clean');

mix
  .setPublicPath('resources')
  .js('src/js/entryhelper.js', 'js/entryhelper.min.js')
  .clean({
    cleanOnceBeforeBuildPatterns: ['js/**/*']
  })
;
