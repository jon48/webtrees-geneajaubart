/**
 * Laravel mix - MyArtJaub Welcome Block module
 *
 * Output:
 *      - welcomeblock.js
 */

const mix = require('laravel-mix');
require('laravel-mix-clean');

mix
  .setPublicPath('resources')
  .js('src/js/welcomeblock.js', 'js/welcomeblock.min.js')
  .clean({
    cleanOnceBeforeBuildPatterns: ['css/**/*', 'js/**/*']
  })
;
