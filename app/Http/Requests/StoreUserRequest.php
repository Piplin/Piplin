<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Http\Requests;

use Fixhub\Http\Requests\Request;
use Fixhub\Models\User;

/**
 * Request for validating users.
 */
class StoreUserRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name'     => 'required|max:255',
            'level'    => 'required|integer|min:' . User::LEVEL_ADMIN . '|max:' . User::LEVEL_OPERATOR,
            'nickname' => 'required|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|confirmed|min:6',
        ];

        // On edit change the password validator
        if ($this->get('id')) {
            $rules['email'] .= ',' . $this->get('id');

            if ($this->get('password') !== '') {
                $rules['password'] = 'min:6';
            } else {
                unset($rules['password']);
            }
        }

        return $rules;
    }
}
