<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Presenters;

use Creativeorange\Gravatar\Gravatar;
use McCool\LaravelAutoPresenter\BasePresenter;

/**
 * The view presenter for a user class.
 */
class UserPresenter extends BasePresenter
{
    private $gravatar;
    /**
     * UserPresenter constructor.
     *
     * @param mixed    $object
     * @param Gravatar $gravatar
     */
    public function __construct(Gravatar $gravatar)
    {
        $this->gravatar = $gravatar;
    }

    /**
     * Get the user avatar.
     *
     * @return string
     */
    public function avatar_url()
    {
        if ($this->getWrappedObject()->avatar) {
            return url($this->getWrappedObject()->avatar);
        }

        return config('piplin.gravatar') ? $this->gravatar->get($this->getWrappedObject()->email) : '/img/noavatar.png';
    }
}
