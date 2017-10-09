<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use Fixhub\Http\Controllers\Controller;
use Fixhub\Models\ServerLog;
use McCool\LaravelAutoPresenter\Facades\AutoPresenter;

/**
 * The controller for showing the output of server log.
 */
class ServerLogController extends Controller
{
    /**
     * Gets the log output of a particular deployment step.
     *
     * @param  int $log_id
     * @return ServerLog
     */
    public function show($log_id)
    {
        $log = ServerLog::findOrFail($log_id);
        $log->runtime = ($log->runtime() === false ? null : AutoPresenter::decorate($log)->readable_runtime);

        return $log;
    }
}
