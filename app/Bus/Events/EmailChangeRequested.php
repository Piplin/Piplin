<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Bus\Events;

use Fixhub\Models\User;
use Illuminate\Queue\SerializesModels;

/**
 * Event for user request to change the login email.
 */
class EmailChangeRequested extends Event
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
        $this->user = $user;
    }
}
