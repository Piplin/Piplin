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

use Illuminate\Queue\SerializesModels;
use Piplin\Models\User;

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
