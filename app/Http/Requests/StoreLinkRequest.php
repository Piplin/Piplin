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

/**
 * Request for validating link.
 */
class StoreLinkRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'title' => 'required|max:255|unique:links,title',
        ];

        // On edit add the group ID to the rules
        if ($this->get('id')) {
            $rules['title'] .= ',' . $this->get('id');
        }

        return $rules;
    }
}
