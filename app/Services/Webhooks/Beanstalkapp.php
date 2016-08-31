<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Services\Webhooks;

/**
 * Class to handle integration with Beanstalkapp webhooks.
 */
class Beanstalkapp extends Webhook
{
    /**
     * Determines whether the request was from Beanstalkapp.
     *
     * @return bool
     */
    public function isRequestOrigin()
    {
        return ($this->request->headers->get('User-Agent') === 'beanstalkapp.com');
    }

    /**
     * Parses the request for a push webhook body.
     *
     * @return mixed Either an array of parameters for the deployment config, or false if it is invalid.
     */
    public function handlePush()
    {
        $payload = $this->request->json();

        // Beanstalk is different to the other services, trigger is not in the headers but in the payload.
        // Only push can be used for beanstalk as create_tag doesn't include enough info
        if (!$payload->has('trigger') || $payload->get('trigger') !== 'push') {
            return false;
        }

        $payload = $payload->get('payload');

        // Sort the commits by the timestamp descending order and then get the first one
        $head = collect($payload['commits'])->sortByDesc(function ($commit) {
            return strtotime($commit['committed_at']);
        })->first();

        return [
            'reason'          => trim($head['message']),
            'branch'          => $payload['branch'],
            'source'          => 'Beanstalkapp',
            'build_url'       => $head['changeset_url'],
            'commit'          => $payload['after'],
            'committer'       => $head['author']['name'],
            'committer_email' => $head['author']['email'],
        ];
    }
}
