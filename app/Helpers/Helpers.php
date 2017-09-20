<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!function_exists('set_active')) {
    /**
     * Set active class if request is in path.
     *
     * @param string $path
     * @param array  $classes
     * @param string $active
     *
     * @return string
     */
    function set_active($path, array $classes = [], $active = 'active')
    {
        if (Request::is($path)) {
            $classes[] = $active;
        }
        $class = e(implode(' ', $classes));
        return empty($classes) ? '' : "class=\"{$class}\"";
    }
}

if (!function_exists('cdn')) {
    /**
    * Creates CDN assets url
    *
    * @param string $path
    * @param null $secure
    * @return string
    */
    function cdn($path, $secure = null)
    {
        if (!config('fixhub.cdn')) {
            return elixir($path);
        }
        $path = trim($path, '/');
        if (in_array(pathinfo($path, PATHINFO_EXTENSION), ['css', 'js'])) {
            $path = elixir($path);
        }
        return '//' . config('fixhub.cdn') . ($path[0] !== '/' ? ('/' . $path) : $path);
    }
}
