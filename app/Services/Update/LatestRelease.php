<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Services\Update;

use Httpful\Exception\ConnectionErrorException;
use Httpful\Request;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * A class to get the latest release tag for Github.
 */
class LatestRelease implements LatestReleaseInterface
{
    const CACHE_TIME_IN_HOURS = 1;

    /**
     * @var string
     **/
    private $github_url = 'https://api.github.com/repos/piplin/piplin/releases/latest';

    private $cache;

    /**
     * Class constructor.
     *
     * @param CacheRepository $cache
     **/
    public function __construct(CacheRepository $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get the latest release from Github.
     *
     * @return string
     */
    public function latest()
    {
        $cache_for = self::CACHE_TIME_IN_HOURS * 60;

        $release = $this->cache->remember('piplin_latest_version', $cache_for, function () {
            try {
                $request = Request::get($this->github_url)
                                  ->expectsJson()
                                  ->withAccept('application/vnd.github.v3+json');

                if (config('piplin.github_oauth_token')) {
                    $request->withAuthorization('token ' . config('piplin.github_oauth_token'));
                }

                $response = $request->send();
            } catch (ConnectionErrorException $e) {
                return false;
            }

            if ($response->hasErrors()) {
                return false;
            }

            return $response->body;
        });

        if (is_object($release)) {
            return $release->tag_name;
        }

        return false;
    }
}
