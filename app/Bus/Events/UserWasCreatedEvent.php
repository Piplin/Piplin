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
 * Event which is fired when a user is created.
 */
class UserWasCreatedEvent extends Event
{
    use SerializesModels;

    /**
     * The user which was created.
     *
     * @var User
     */
    public $user;

    /**
     * The plain password, this is never stored on the model.
     *
     * @var string
     */
    public $password;

    /**
     * Create a new event instance.
     *
     * @param  User           $user
     * @param  string         $password
     * @return UserWasCreated
     */
    public function __construct(User $user, $password)
    {
        $this->user     = $user;
        $this->password = $password;
    }
}
