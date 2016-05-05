var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    mix.sass('app.scss');
    mix.copy('node_modules/vue/dist/vue.common.js', 'resources/assets/js/vendor/vue/vue.common.js');
    mix.copy('node_modules/vue/dist/vue.min.js', 'resources/assets/js/vendor/vue/vue.min.js');
    mix.copy('node_modules/vue-resource/dist/vue-resource.min.js', 'resources/assets/js/vendor/vue-resource/vue-resource.min.js');
    mix.scripts([
        'vendor/vue/vue.min.js',
        'vendor/vue-resource/vue-resource.min.js',
        'app.js'
    ]);
    mix.browserify('app.js');
    mix.version(['css/app.css', 'js/all.js']);
});