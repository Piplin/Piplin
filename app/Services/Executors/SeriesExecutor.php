<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Services\Executors;

use Carbon\Carbon;
use Fixhub\Models\ServerLog;
use Fixhub\Models\DeployStep;
use Fixhub\Models\Server;
use Fixhub\Models\Command as Stage;
use Fixhub\Models\Variable;
use Fixhub\Models\Project;
use Fixhub\Models\Deployment;
use Fixhub\Services\Scripts\Parser as ScriptParser;
use Fixhub\Services\Scripts\Runner as Process;
use Illuminate\Support\Facades\Cache;

/**
 * Class to handle series tasks.
 */
class SeriesExecutor extends Executor
{
    public function run($tasks)
    {
        foreach ($tasks as $task) {
            foreach ($task->logs as $host) {
                $host->status     = ServerLog::RUNNING;
                $host->started_at =  Carbon::now();
                $host->save();

                try {
                    $this->sendFilesForStep($task, $host);

                    $process = $this->buildScript($task, $host);

                    $failed    = false;
                    $cancelled = false;

                    $output = '';
                    $process->run(function ($type, $output_line) use (&$output, &$host, $process, $task) {
                        if ($type === \Symfony\Component\Process\Process::ERR) {
                            $output .= $this->logError($output_line);
                        } else {
                            $output .= $this->logSuccess($output_line);
                        }

                        $host->output = $output;
                        $host->save();

                        // If there is a cache key, kill the process but leave the key
                        if ($task->stage <= Stage::DO_ACTIVATE && Cache::has($this->cache_key)) {
                            $process->stop(0, SIGINT);

                            $output .= $this->logError('SIGINT');
                        }
                    });

                    if (!$process->isSuccessful()) {
                        $failed = true;
                    }
                    $host->output = $output;
                } catch (\Exception $e) {
                    $host->output .= $this->logError('[' . $host->server->ip_address . ']: ' . $e->getMessage());
                    $failed = true;
                }

                $host->status = ($failed ? ServerLog::FAILED : ServerLog::COMPLETED);

                // Check if there is a cache key and if so abort
                if (Cache::pull($this->cache_key) !== null) {
                    // Only allow aborting if the release has not yet been activated
                    if ($task->stage <= Stage::DO_ACTIVATE) {
                        $host->status = ServerLog::CANCELLED;

                        $cancelled = true;
                        $failed    = false;
                    }
                }

                $host->finished_at =  Carbon::now();
                $host->save();

                // Throw an exception to prevent any more tasks running
                if ($failed) {
                    throw new \RuntimeException('Failed');
                }

                // This is a messy way to do it
                if ($cancelled) {
                    throw new \RuntimeException('Cancelled');
                }
            }
        }
    }
}
