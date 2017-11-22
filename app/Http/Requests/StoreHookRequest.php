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

/**
 * Request for validating hooks.
 */
class StoreHookRequest extends Request
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
            'type'                       => 'required|in:slack,dingtalk,mail,custom',
            'enabled'                    => 'boolean',
            'on_deployment_success'      => 'boolean',
            'on_deployment_failure'      => 'boolean',
        ], $this->configRules());

        if ($this->route('notification')) {
            unset($rules['project_id']);
        }

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
    private function slackRules()
    {
        return [
            'channel' => 'required|max:255|channel',
            'webhook' => 'required|url',
            'icon'    => 'string|nullable|regex:/^:(.*):$/',
        ];
    }

    /**
     * Validation rules specific to dingtalk.
     *
     * @return array
     */
    private function dingtalkRules()
    {
        return [
            'webhook'    => 'required|url',
            'at_mobiles' => 'nullable|string',
            'is_at_all'  => 'nullable',
        ];
    }

    /**
     * Validation rules specific to email.
     *
     * @return array
     */
    private function mailRules()
    {
        return [
            'email' => 'required|email',
        ];
    }

    /**
     * Validation rules specific to custom hooks.
     *
     * @return array
     */
    private function customRules()
    {
        return [
            'url' => 'required|url',
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
            case 'slack':
                return $this->slackRules();
            case 'dingtalk':
                return $this->dingtalkRules();
            case 'mail':
                return $this->mailRules();
            case 'custom':
            default:
                return $this->customRules();
        }
    }
}
