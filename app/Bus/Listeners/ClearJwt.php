<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Bus\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Session;

/**
 * Listener class to remove the JWT on logout.
 */
class ClearJwt
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Logout $event
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(Logout $event)
    {
        Session::forget('jwt');
    }
}
