<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Services\Webhooks;

/**
 * Class to handle integration with Custom webhooks.
 */
class Custom extends Webhook
{
    /**
     * Determines whether the request was for the Custom webhook.
     *
     * @return bool
     */
    public function isRequestOrigin()
    {
        return true;
    }

    /**
     * Parses the request for a webhook body.
     *
     * @return mixed Either an array of parameters for the task config, or false if it is invalid.
     */
    public function handlePush()
    {
        // Get the branch if it is the request, otherwise deploy the default branch
        $branch = $this->request->has('branch') ? $this->request->get('branch') : null;

        // If there is a source and a URL validate that the URL is valid
        $build_url = null;
        if ($this->request->has('source') && $this->request->has('url')) {
            $build_url = $this->request->get('url');

            if (!filter_var($build_url, FILTER_VALIDATE_URL)) {
                $build_url = null;
            }
        }

        // TODO: Allow a ref to be passed in?
        return [
            'reason'     => $this->request->get('reason'),
            'branch'     => $branch,
            'source'     => $this->request->get('source'),
            'build_url'  => $build_url,
        ];
    }
}
