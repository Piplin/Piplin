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
 * Class to handle integration with Bitbucket webhooks.
 */
class Bitbucket extends Webhook
{
    /**
     * Determines whether the request was from Bitbucket.
     *
     * @return bool
     */
    public function isRequestOrigin()
    {
        return ($this->request->headers->has('X-Event-Key'));
    }

    /**
     * Parses the request for a push webhook body.
     *
     * @return mixed Either an array of parameters for the deployment config, or false if it is invalid.
     */
    public function handlePush()
    {
        // We only care about push events
        if ($this->request->header('X-Event-Key') !== 'repo:push') {
            return false;
        }

        $payload = collect($this->request->json('push'));

        // Invalid event from bitbucket
        if (!$payload->has('changes') || !count($payload->get('changes', []))) {
            return false;
        }

        $head = $payload->get('changes')[0]['new'];

        list($name, $email) = explode(' <', trim($head['target']['author']['raw'], '> '));

        return [
            'reason'          => trim($head['target']['message']),
            'branch'          => $head['name'],
            'source'          => 'Bitbucket',
            'build_url'       => $head['target']['links']['html']['href'],
            'commit'          => $head['target']['hash'],
            'committer'       => $name,
            'committer_email' => $email,
        ];
    }
}
