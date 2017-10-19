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
 * Class to handle integration with Oschina webhooks.
 */
class Oschina extends Webhook
{
    /**
     * Determines whether the request was from Oschina.
     *
     * @return bool
     */
    public function isRequestOrigin()
    {
        return ($this->request->headers->has('x-git-oschina-event'));
    }

    /**
     * Parses the request for a push webhook body.
     *
     * @return mixed Either an array of parameters for the deployment config, or false if it is invalid.
     */
    public function handlePush()
    {
        // We only care about "Tag Push Hook" & "Push Hook" events
        if (strpos($this->request->header('x-git-oschina-event'), 'Push Hook') === false) {
            return false;
        }

        $payload = $this->request->json();

        // Sort the commits by the timestamp descending order and then get the first one
        $head = collect($payload->get('commits'))->sortByDesc(function ($commit) {
            return strtotime($commit['timestamp']);
        })->first();

        $branch = preg_replace('#refs/(tags|heads)/#', '', $payload->get('ref'));

        return [
            'reason'          => trim($head['message']),
            'branch'          => $branch,
            'source'          => 'Oschina',
            'build_url'       => $head['url'],
            'commit'          => $head['id'],
            'committer'       => $head['author']['name'],
            'committer_email' => $head['author']['email'],
        ];
    }
}
