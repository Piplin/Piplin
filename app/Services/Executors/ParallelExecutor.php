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
use Closure;
use Symfony\Component\Process\Process as SystemProcess;

/**
 * Class to handle parallel tasks.
 */
class ParallelExecutor extends Executor
{
    public function run($steps, $callback = null)
    {
        $processes = [];

        $callback = $callback ?: function () {};

        $taskHosts = [];
        foreach($steps as $step)
        {
            foreach($step->logs as $log) {
                $taskHosts[$log->id] = [$log, $step];
            }
        }
        foreach($taskHosts as $item) {
            $host = $item[0];
            $task = $item[1];
            $process = $this->buildScript($task, $host);
            $processes[] = $process;
        }

        $this->startProcesses($processes);

        while ($this->areRunning($processes)) {
            $this->gatherOutput($processes, $callback);
        }

        return $this->gatherExitCodes($processes);
    }

    /**
     * Start all of the processes.
     *
     * @param  array  $processes
     * @return void
     */
    protected function startProcesses(array $processes)
    {
        foreach ($processes as $process) {
            $process->run(function ($type, $output_line){
                //$ret = 
            });
        }
    }

    /**
     * Determine if any of the processes are running.
     *
     * @param  array  $processes
     * @return bool
     */
    protected function areRunning(array $processes)
    {
        foreach ($processes as $process) {
            if ($process->isRunning()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Gather the output from all of the processes.
     *
     * @param  array  $processes
     * @param  \Closure  $callback
     * @return void
     */
    protected function gatherOutput(array $processes, Closure $callback)
    {
        foreach ($processes as $host => $process) {
            $methods = [
                SystemProcess::OUT => 'getOutput',
                SystemProcess::ERR => 'getErrorOutput',
            ];
            foreach ($methods as $type => $method) {
                $output = $process->{$method}();
                if (! empty($output)) {
                    $callback($type, $host, $output);
                }
            }
        }
    }

    /**
     * Gather the cumulative exit code for the processes.
     *
     * @param  array  $processes
     * @return int
     */
    protected function gatherExitCodes(array $processes)
    {
        $code = 0;
        foreach ($processes as $process) {
            $code = $code + $process->getExitCode();
        }
        return $code;
    }
}
