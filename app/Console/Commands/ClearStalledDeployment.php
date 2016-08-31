<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Console\Commands;

use Fixhub\Models\Deployment;
use Fixhub\Models\Project;
use Fixhub\Models\ServerLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

/**
 * Clears any stalled deployments so that new deployments can be queued.
 */
class ClearStalledDeployment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fixhub:cleanup';

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
        Deployment::whereIn('status', [Deployment::DEPLOYING, Deployment::PENDING])
                  ->update(['status' => Deployment::FAILED]);

        // Mark any aborting deployments as aborted
        Deployment::whereIn('status', [Deployment::ABORTING])
                  ->update(['status' => Deployment::ABORTED]);

        // Mark any deploying/pending projects as failed
        Project::whereIn('status', [Project::DEPLOYING, Project::PENDING])
               ->update(['status' => Project::FAILED]);
    }
}
