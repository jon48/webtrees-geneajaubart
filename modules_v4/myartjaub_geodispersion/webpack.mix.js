/**
 * Laravel mix - MyArtJaub GeoDispersion module
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

let mix = require('laravel-mix');
require('laravel-mix-clean');

//https://github.com/postcss/autoprefixer
const postcss_autoprefixer = require("autoprefixer")();

//https://github.com/jakob101/postcss-inline-rtl
const postcss_rtl = require("@mjhenkes/postcss-rtl")();

//https://github.com/bezoerb/postcss-image-inliner
const postcss_image_inliner = require("postcss-image-inliner")({
    assetPaths: ['src/sass/resources/images'],
    maxFileSize: 0,
});


//https://github.com/postcss/postcss-custom-properties
//Enable CSS variables in IE
const postcss_custom_properties = require("postcss-custom-properties")();

mix
    .setPublicPath('resources')
    .js('src/js/geodispersion.js', 'js/geodispersion.min.js')
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
            postcss_rtl,
            postcss_autoprefixer,
            postcss_image_inliner,
            postcss_custom_properties,
        ]
    })
    .clean({
        cleanOnceBeforeBuildPatterns: ['css/**/*', 'js/**/*']
    })
    ;