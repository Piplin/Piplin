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

if (!function_exists('color_darken')) {
    /**
     * Darken a color.
     *
     * @param string $hex
     * @param int    $percent
     *
     * @return string
     */
    function color_darken($hex, $percent)
    {
        $hex = preg_replace('/[^0-9a-f]/i', '', $hex);
        $new_hex = '#';

        if (strlen($hex) < 6) {
            $hex = $hex[0] + $hex[0] + $hex[1] + $hex[1] + $hex[2] + $hex[2];
        }

        for ($i = 0; $i < 3; $i++) {
            $dec = hexdec(substr($hex, $i * 2, 2));
            $dec = min(max(0, $dec + $dec * $percent), 255);
            $new_hex .= str_pad(dechex($dec), 2, 0, STR_PAD_LEFT);
        }

        return $new_hex;
    }
}
