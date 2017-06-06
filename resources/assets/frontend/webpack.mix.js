const { mix } = require('laravel-mix');

mix.js('resources/js/app.js', 'js/compiled.js');
mix.babel(['js/compiled.js'], 'js/app.js');
mix.copy('js/app.js', '../../../public/kommercio/assets/scripts/app.js');