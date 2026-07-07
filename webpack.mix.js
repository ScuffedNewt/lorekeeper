const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

// Scripts
mix.js('resources/js/app.js', 'public/js')
    .version();
mix.js('resources/js/app-secondary.js', 'public/js')
    .version();

// CSS
mix.sass('resources/sass/app.scss', 'public/css')
    .version();

// do not uncomment this unless you know how to resolve the CHIBIOK response check error
// mix.js('node_modules/chickenpaint/resources/js/chickenpaint.js', 'public/chickenpaint')
//    .sass('node_modules/chickenpaint/resources/css/chickenpaint.scss', 'public/chickenpaint');