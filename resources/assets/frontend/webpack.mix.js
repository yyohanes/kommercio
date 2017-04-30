const { mix } = require('laravel-mix');

mix.js('resources/js/app.js', 'js');
mix.copy('js/app.js', '../../../public/kommercio/assets/scripts/app.js');