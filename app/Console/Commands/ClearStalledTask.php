<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Piplin\Models\Task;
use Piplin\Models\Project;
use Piplin\Models\ServerLog;

/**
 * Clears any stalled deployments so that new deployments can be queued.
 */
class ClearStalledTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'piplin:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancels any stalled deployments so new deployments can be run';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $bring_back_up = false;

        // Check the app is offline, if not ask the user if it can be brought down
        if (!App::isDownForMaintenance()) {
            $this->error(trans('app.not_down'));

            if (!$this->confirm(trans('app.switch_down'))) {
                return;
            }

            $bring_back_up = true;

            $this->call('down');
        }

        $this->cleanupDeployments();

        // If we prompted the user to bring the app down, bring it back up
        if ($bring_back_up) {
            $this->call('up');
        }
    }

    /**
     * Cleans up any stalled deployments in the database.
     *
     * @tpdp Maybe readd pending to the queue if possible?
     * @return void
     */
    public function cleanupDeployments()
    {
        // Mark any pending steps as cancelled
        ServerLog::where('status', '=', ServerLog::PENDING)
                 ->update(['status' => ServerLog::CANCELLED]);

        // Mark any running steps as failed
        ServerLog::where('status', '=', ServerLog::RUNNING)
                 ->update(['status' => ServerLog::FAILED]);

        // Mark any running/pending deployments as failed
        Task::whereIn('status', [Task::RUNNING, Task::PENDING])
                  ->update(['status' => Task::FAILED]);

        // Mark any aborting deployments as aborted
        Task::whereIn('status', [Task::ABORTING])
                  ->update(['status' => Task::ABORTED]);

        // Mark any running/pending projects as failed
        Project::whereIn('status', [Project::RUNNING, Project::PENDING])
               ->update(['status' => Project::FAILED]);
    }
}
