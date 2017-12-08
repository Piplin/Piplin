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
 * Class to handle integration with Github webhooks.
 */
class Github extends Webhook
{
    /**
     * Determines whether the request was from Github.
     *
     * @return bool
     */
    public function isRequestOrigin()
    {
        return ($this->request->headers->has('X-GitHub-Event'));
    }

    /**
     * Parses the request for a push webhook body.
     *
     * @return mixed Either an array of parameters for the deployment config, or false if it is invalid.
     */
    public function handlePush()
    {
        // We only care about push events
        if ($this->request->header('X-GitHub-Event') !== 'push') {
            return false;
        }

        $payload = $this->request->json();

        // Github sends a payload when you close a pull request with a non-existent commit.
        if ($payload->has('after') && $payload->get('after') === '0000000000000000000000000000000000000000') {
            return false;
        }

        $head   = $payload->get('head_commit');
        $branch = preg_replace('#refs/(tags|heads)/#', '', $payload->get('ref'));

        return [
            'reason'          => trim($head['message']),
            'branch'          => $branch,
            'source'          => 'Github',
            'build_url'       => $head['url'],
            'commit'          => $head['id'],
            'committer'       => $head['committer']['name'],
            'committer_email' => $head['committer']['email'],
        ];
    }
}
