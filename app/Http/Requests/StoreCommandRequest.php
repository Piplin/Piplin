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
use Piplin\Models\Command;

/**
 * Request for validating commands.
 */
class StoreCommandRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name'       => 'required|max:255',
            'user'       => 'max:255',
            'script'     => 'required',
            'optional'   => 'boolean',
            'default_on' => 'boolean',
            'step'       => 'required|integer',
            //'step'       => 'required|integer|min:' . Command::BEFORE_CLONE . '|max:' . Command::AFTER_PURGE,
        ];

        // On edit we don't require the step
        if ($this->get('id')) {
            unset($rules['step']);
        }

        return $rules;
    }
}
