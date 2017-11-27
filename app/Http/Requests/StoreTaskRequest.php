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
 * Request for validating tasks.
 */
class StoreTaskRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //'environments' => 'required',
        ];
    }

    /**
     * Gets the input which are allowed in the request based on the type.
     *
     * @return array
     */
    public function payloadOnly()
    {
        return $this->only(array_keys($this->payloadRules()));
    }

    /**
     * Gets the additional rules based on the type from the request.
     *
     * @return array
     */
    private function payloadRules()
    {
        return [
            'source'                       => 'nullable',
            'source_'.$this->get('source') => 'nullable',
        ];
    }
}
