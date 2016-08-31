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
        return [
            'name'               => 'required|max:255',
            'repository'         => 'required',
            'branch'             => 'required|max:255',
            'group_id'           => 'required|integer|exists:project_groups,id',
            'builds_to_keep'     => 'required|integer|min:1|max:20',
            'template_id'        => 'integer|exists:deploy_templates,id',
            'url'                => 'url',
            'build_url'          => 'url',
            'allow_other_branch' => 'boolean',
            'include_dev'        => 'boolean',
            'private_key'        => 'sshkey',
        ];
    }
}
