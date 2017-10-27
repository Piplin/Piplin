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

const node_path = 'node_modules';
const assets_path = 'resources/assets';
const dist_path = 'public';

const paths = {
    'ace'              : `${node_path}/ace-min-noconflict`,
    'backbone'         : `${node_path}/backbone`,
    'bootstrap_sass'   : `${node_path}/bootstrap-sass`,
    'cropper'          : `${node_path}/cropper`,
    'ionicons'         : `${node_path}/ionicons`,
    'jquery'           : `${node_path}/jquery`,
    'jquery_sortable'  : `${node_path}/jquery-sortable`,
    'livestamp'        : `${node_path}/livestamp`,
    'localization'     : 'vendor/fixhub/js-localization',
    'moment'           : `${node_path}/moment`,
    'morris'           : `${node_path}/morris.js`,
    'raphael'          : `${node_path}/raphael`,
    'select2'          : `${node_path}/select2`,
    'socketio_client'  : `${node_path}/socket.io-client`,
    'toastr'           : `${node_path}/toastr`,
    'underscore'       : `${node_path}/underscore`
};

const skeletons = [
    `${assets_path}/js/components/dashboard/commands.js`,
    `${assets_path}/js/components/dashboard/configFiles.js`,
    `${assets_path}/js/components/dashboard/environments.js`,
    `${assets_path}/js/components/dashboard/sharedFiles.js`,
    `${assets_path}/js/components/dashboard/variables.js`,
];

mix
    .options({
        processCssUrls: false
    })
    .copyDirectory(`${paths.bootstrap_sass}/assets/fonts/bootstrap`, `${dist_path}/fonts`)
    .copyDirectory(`${paths.ionicons}/fonts`, `${dist_path}/fonts`)
    .scripts([
        `${paths.jquery}/dist/jquery.min.js`,
        `${paths.jquery_sortable}/source/js/jquery-sortable-min.js`,
        `${paths.underscore}/underscore-min.js`,
        `${paths.moment}/min/moment-with-locales.min.js`,
        `${paths.bootstrap_sass}/assets/javascripts/bootstrap.min.js`,
        `${paths.select2}/dist/js/select2.min.js`,
        `${paths.raphael}/raphael.min.js`,
        `${paths.morris}/morris.min.js`,
        `${paths.backbone}/backbone-min.js`,
        `${paths.socketio_client}/dist/socket.io.js`,
        `${paths.localization}/resources/js/config.js`,
        `${paths.localization}/resources/js/localization.js`,
        `${paths.toastr}/build/toastr.min.js`,
        `${paths.cropper}/dist/cropper.min.js`,
        `${paths.livestamp}/livestamp.js`
    ], `${dist_path}/js/vendor.js`)
    .scripts([
        `${paths.ace}/ace.js`,
        `${paths.ace}/mode-sh.js`,
        `${paths.ace}/mode-php.js`,
        `${paths.ace}/mode-yaml.js`,
        `${paths.ace}/mode-ini.js`
    ], `${dist_path}/js/ace.js`)
    .scripts([
        `${assets_path}/js/components/admin/groups.js`,
        `${assets_path}/js/components/admin/providers.js`,
        `${assets_path}/js/components/admin/projects.js`,
        `${assets_path}/js/components/admin/templates.js`,
        `${assets_path}/js/components/admin/links.js`,
        `${assets_path}/js/components/admin/tips.js`,
        `${assets_path}/js/components/admin/keys.js`,
        `${assets_path}/js/components/admin/cabinets.js`,
        `${assets_path}/js/components/admin/users.js`
    ].concat(skeletons), `${dist_path}/js/admin.js`)
    .scripts([
        `${assets_path}/js/components/dashboard/commands.js`,
        `${assets_path}/js/components/dashboard/deployment.js`,
        `${assets_path}/js/components/dashboard/hooks.js`,
        `${assets_path}/js/components/dashboard/members.js`,
        `${assets_path}/js/components/dashboard/projects.js`,
        `${assets_path}/js/components/dashboard/profile.js`,
        `${assets_path}/js/components/dashboard/servers.js`,
        `${assets_path}/js/components/dashboard/environmentLinks.js`,
        `${assets_path}/js/components/dashboard/cabinets.js`
    ].concat(skeletons), `${dist_path}/js/dashboard.js`)
    .scripts([
        `${assets_path}/js/bootstrap.js`,
        `${assets_path}/js/fixhub.js`,
        `${assets_path}/js/utils/uploader.js`,
    ], `${dist_path}/js/app.js`)
    .styles([
        `${paths.select2}/dist/css/select2.min.css`,
        `${paths.morris}/morris.css`,
        `${paths.ionicons}/css/ionicons.min.css`,
        `${paths.toastr}/build/toastr.min.css`,
        `${paths.cropper}/dist/cropper.min.css`
    ], `${dist_path}/css/vendor.css`)
    .sass(`${assets_path}/sass/app.scss`, `${dist_path}/css/app.css`);

if (mix.inProduction()) {
    mix.version();
}

if (!mix.inProduction()) {
    mix.sourceMaps()
    mix.browserSync({proxy: 'fixhub.app'})
}