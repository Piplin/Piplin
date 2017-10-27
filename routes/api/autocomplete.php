<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::any('autocomplete/users', [
    'as'         => 'autocomplete.users',
    'middleware' => 'api',
    'uses'       => 'Api\AutocompleteController@users',
]);

Route::any('autocomplete/cabinets', [
    'as'         => 'autocomplete.cabinets',
    'middleware' => 'api',
    'uses'       => 'Api\AutocompleteController@cabinets',
]);
