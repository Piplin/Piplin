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
 * Request for validating projects.
 */
class StoreProjectRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name'               => 'required|max:255',
            'branch'             => 'nullable|max:255',
            'targetable_id'      => 'nullable|integer',
            'key_id'             => 'nullable|integer|exists:keys,id',
            'builds_to_keep'     => 'nullable|integer|min:1|max:20',
            'template_id'        => 'nullable|integer|exists:deploy_templates,id',
            'url'                => 'url|nullable',
            'build_url'          => 'url|nullable',
            'allow_other_branch' => 'boolean'
        ];

        // On editing remove the template_id rule
        if ($this->get('id')) {
            unset($rules['template_id']);
            $rules['repository'] = 'required';
        }

        return $rules;
    }
}
