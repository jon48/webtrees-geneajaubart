/**
 * Laravel mix - MyArtJaub Admin Tasks module
 *
 * Output:
 *      - welcomeblock.js
 */

const mix = require('laravel-mix');
require('laravel-mix-clean');

mix
  .setPublicPath('resources')
  .js('src/js/admintasks.js', 'js/admintasks.min.js')
  .clean({
    cleanOnceBeforeBuildPatterns: ['css/**/*', 'js/**/*']
  })
;
