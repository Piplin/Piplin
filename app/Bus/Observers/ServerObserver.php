<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Bus\Observers;

use Fixhub\Models\Server;

/**
 * Event observer for Server model.
 */
class ServerObserver
{
    /**
     * Called when the model is updating.
     *
     * @param Server $server
     */
    public function updating(Server $server)
    {
        if ($server->isDirty('ip_address')) {
            $server->status = Server::UNTESTED;
        }
    }

    /**
     * Called when the model is saved.
     *
     * @param Server $server
     */
    public function deleting(Server $server)
    {
        $server->logs()->forceDelete();
    }
}
