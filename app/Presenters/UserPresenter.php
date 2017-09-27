<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Presenters;

use McCool\LaravelAutoPresenter\BasePresenter;
use Creativeorange\Gravatar\Gravatar;

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

        return config('fixhub.gravatar') ? $this->gravatar->get($this->getWrappedObject()->email) : '/img/noavatar.png';
    }
}
