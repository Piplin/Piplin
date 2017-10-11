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

/**
 * Request for validating triggers.
 */
class StoreTriggerRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = array_merge([
            'name'                       => 'required|max:255',
            'project_id'                 => 'required|integer|exists:projects,id',
            'type'                       => 'required|in:schedule,daily',
            'enabled'                    => 'boolean',
        ], $this->configRules());

        return $rules;
    }

    /**
     * Gets the input which are allowed in the request based on the type.
     *
     * @return array
     */
    public function configOnly()
    {
        return $this->only(array_keys($this->configRules()));
    }

    /**
     * Validation rules specific to slack.
     *
     * @return array
     */
    private function scheduleRules()
    {
        return [
            'interval' => 'required|integer',
        ];
    }

    /**
     * Validation rules specific to build time.
     *
     * @return array
     */
    private function dailyRules()
    {
        return [
            'runtime' => 'required',
        ];
    }

    /**
     * Gets the additional rules based on the type from the request.
     *
     * @return array
     */
    private function configRules()
    {
        switch ($this->get('type')) {
            case 'schedule':
                return $this->scheduleRules();
            case 'daily':
            default:
                return $this->dailyRules();
        }
    }
}
