<?php

/*
 * This file is part of Laravel HTMLMin.
 *
 * (c) Graham Campbell <graham@alt-three.com>
 * (c) Raza Mehdi <srmk@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Automatic Blade Optimizations
    |--------------------------------------------------------------------------
    |
    | This option enables minification of the blade views as they are
    | compiled. These optimizations have little impact on php processing time
    | as the optimizations are only applied once and are cached. This package
    | will do nothing by default to allow it to be used without minifying
    | pages automatically.
    |
    | Default: false
    |
    */

    'blade' => false,

    /*
    |--------------------------------------------------------------------------
    | Force Blade Optimizations
    |--------------------------------------------------------------------------
    |
    | This option forces blade minification on views where there such
    | minification may be dangerous. This should only be used if you are fully
    | aware of the potential issues this may cause. Obviously, this setting is
    | dependent on blade minification actually being enabled.
    |
    | PLEASE USE WITH CAUTION
    |
    | Default: false
    |
    */

    'force' => false,

    /*
    |--------------------------------------------------------------------------
    | Ignore Blade Files
    |--------------------------------------------------------------------------
    |
    | Here you can specify paths, which you don't want to minify.
    |
    */

    'ignore' => [
        'resources/views/emails',
        'resources/views/html',
        'resources/views/notifications',
        'resources/views/markdown',
        'resources/views/vendor/emails',
        'resources/views/vendor/html',
        'resources/views/vendor/notifications',
        'resources/views/vendor/markdown',
    ],

];
