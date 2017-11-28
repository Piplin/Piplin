<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use McCool\LaravelAutoPresenter\Facades\AutoPresenter;
use Piplin\Http\Controllers\Controller;
use Piplin\Models\ServerLog;

/**
 * The controller for showing the output of server log.
 */
class ServerLogController extends Controller
{
    /**
     * Gets the log output of a particular deployment step.
     *
     * @param  int       $log_id
     * @return ServerLog
     */
    public function show($log_id)
    {
        $log          = ServerLog::findOrFail($log_id);
        $log->runtime = ($log->runtime() === false ? null : AutoPresenter::decorate($log)->readable_runtime);

        return $log;
    }
}
