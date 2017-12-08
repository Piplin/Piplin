<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Bus\Events;

use Illuminate\Auth\Events\Login;
use Illuminate\Queue\SerializesModels;
use Piplin\Models\User;

/**
 * Event which is fired when the JSON web token expires.
 */
class JsonWebTokenExpiredEvent extends Login
{
    use SerializesModels;

    /**
     * @var User
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user  = $user;
    }
}
