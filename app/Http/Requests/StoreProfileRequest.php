<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Http\Requests;

use Piplin\Http\Requests\Request;

/**
 * Validate the user name and password.
 */
class StoreProfileRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'nickname' => 'required|max:255',
            'password' => 'required|confirmed|min:6',
        ];

        if (empty($this->get('password'))) {
            unset($rules['password']);
        }

        return $rules;
    }
}
