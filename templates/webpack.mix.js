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

mix
   .webpackConfig({
      resolve: {
         alias: {
            '~': path.resolve('resources/js'),
            '@': path.resolve('resources/js/components')
         }
      },
      output: {
         publicPath: '/',
         chunkFilename: "static/chunk_[chunkhash].js",
      }
   })
   .sourceMaps(false, 'source-map')
   .js('resources/js/app.js', 'public/static')
   .sass('resources/sass/app.sass', 'public/static');
