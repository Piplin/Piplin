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
use Piplin\Models\User;

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
            'name'     => 'required|max:255|unique:users,name',
            'level'    => 'required|integer|min:' . User::LEVEL_ADMIN . '|max:' . User::LEVEL_COLLABORATOR,
            'nickname' => 'required|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|confirmed|min:6',
        ];

        if ($this->get('id')) {
            $rules['name'] .= ',' . $this->get('id');
            $rules['email'] .= ',' . $this->get('id');

            if (!empty($this->get('password', null))) {
                $rules['password'] = 'min:6';
            } else {
                unset($rules['password']);
            }
        }

        return $rules;
    }
}
