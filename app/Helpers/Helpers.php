<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!function_exists('set_active')) {
    /**
     * Set active class if request is in path.
     *
     * @param dynamic $patterns
     * @param array   $classes
     * @param string  $active
     *
     * @return string
     */
    function set_active($patterns, array $classes = [], $active = 'active')
    {
        if (Request::is($patterns)) {
            $classes[] = $active;
        }
        $class = e(implode(' ', $classes));

        return empty($classes) ? '' : "class=\"{$class}\"";
    }
}

if (!function_exists('cdn')) {
    /**
     * Creates CDN assets url.
     *
     * @param  string $path
     * @param  null   $secure
     * @return string
     */
    function cdn($path, $secure = null)
    {
        if (!config('piplin.cdn')) {
            return mix($path);
        }
        $path = trim($path, '/');
        if (in_array(pathinfo($path, PATHINFO_EXTENSION), ['css', 'js'], true)) {
            $path = mix($path);
        }

        return '//' . config('piplin.cdn') . ($path[0] !== '/' ? ('/' . $path) : $path);
    }
}
