var elixir = require('laravel-elixir');
             require('laravel-elixir-remove');
             require('laravel-elixir-bower-io');

elixir.config.production = true;
elixir.config.sourcemaps = false;

var gulp   = require('gulp');
var shell  = require('gulp-shell');
var jsuglify = require('gulp-uglify');

var Task = elixir.Task;
elixir.extend('lang', function() {
    new Task('lang', function(){
        return gulp.src('').pipe(shell('php artisan js-localization:refresh'));
    });

});

elixir.extend('jsminify', function() {
    new Task('jsminify', function () {
    return gulp.src('public/js/*.js')
        .pipe(jsuglify())
        .pipe(gulp.dest('public/js/'));
    });
});

var node_path = 'node_modules';

var paths = {
    'backbone'         : node_path + '/backbone',
    'underscore'       : node_path + '/underscore',
    'moment'           : node_path + '/moment',
    'jquery'           : node_path + '/jquery',
    'jquery_sortable'  : node_path + '/jquery-sortable',
    'bootstrap_sass'   : node_path + '/bootstrap-sass',
    'socketio_client'  : node_path + '/socket.io-client',
    'ionicons'         : node_path + '/ionicons',
    'cropper'          : node_path + '/cropper',
    'toastr'           : node_path + '/toastr',
    'select2'          : node_path + '/select2',
    'ace'              : node_path + '/ace-min-noconflict',
    'raphael'          : node_path + '/raphael',
    'morris'           : node_path + '/morris.js',
    'localization'     : '/../vendor/fixhub/js-localization'
};

elixir(function(mix) {
    mix
    .sass('app.scss', 'public/css/app.css')
    .styles([
        paths.select2      + '/dist/css/select2.min.css',
        paths.morris       + '/morris.css',
        paths.ionicons     + '/css/ionicons.min.css',
        paths.toastr       + '/build/toastr.min.css',
        paths.cropper      + '/dist/cropper.min.css',
    ], 'public/css/vendor.css', './')
    .scripts([
        paths.jquery           + '/dist/jquery.min.js',
        paths.jquery_sortable  + '/source/js/jquery-sortable-min.js',
        paths.underscore       + '/underscore-min.js',
        paths.moment           + '/min/moment.min.js',
        paths.bootstrap_sass   + '/assets/javascripts/bootstrap.min.js',
        paths.select2          + '/dist/js/select2.min.js',
        paths.raphael          + '/raphael.min.js',
        paths.morris           + '/morris.min.js',
        paths.backbone         + '/backbone-min.js',
        paths.socketio_client  + '/socket.io.js',
        paths.localization     + '/resources/js/localization.js',
        paths.toastr           + '/build/toastr.min.js',
        paths.cropper          + '/dist/cropper.min.js'
    ], 'public/js/vendor.js', node_path)
    .scripts([
        paths.ace             + '/ace.js',
        paths.ace             + '/mode-sh.js',
        paths.ace             + '/mode-php.js',
        paths.ace             + '/mode-yaml.js',
        paths.ace             + '/mode-ini.js'
    ], 'public/js/ace.js', node_path)

    .scripts([
        'app.js',
        'projects.js',
        'templates.js',
        'keys.js',
        'providers.js',
        'servers.js',
        'hooks.js',
        'shareFiles.js',
        'configFiles.js',
        'variables.js',
        'deployment.js',
        'commands.js',
        'users.js',
        'groups.js',
        'links.js',
        'tips.js',
        'uploader.js',
        'profile.js'
    ], 'public/js/app.js', 'resources/assets/js')
    .jsminify()
    .copy(paths.bootstrap_sass + '/assets/fonts/bootstrap/**', 'public/fonts')
    .copy(paths.ionicons       + '/fonts/**', 'public/fonts')
    .version([
        'public/css/app.css',
        'public/css/vendor.css',
        'public/js/app.js',
        'public/js/ace.js',
        'public/js/vendor.js'
    ])
    .copy('public/fonts', 'public/build/fonts')
    .remove([
        'public/css',
        'public/js',
        'public/fonts'
    ])
    .lang();
});
