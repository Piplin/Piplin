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

use Carbon\Carbon;
use Fixhub\Bus\Jobs\Repository\UpdateGitMirrorJob;
use Fixhub\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Updates the mirrors for all git repositories.
 */
class UpdateGitMirrors extends Command
{
    use DispatchesJobs;

    const UPDATES_TO_QUEUE         = 3;
    const UPDATE_FREQUENCY_MINUTES = 5;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fixhub:update-mirrors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pulls in updates for git mirrors';

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
     * @return mixed
     */
    public function handle()
    {
        $last_mirrored_since = Carbon::now()->subMinutes(self::UPDATE_FREQUENCY_MINUTES);
        $todo                = self::UPDATES_TO_QUEUE;

        Project::where('last_mirrored', '<', $last_mirrored_since)->chunk($todo, function ($projects) {
            foreach ($projects as $project) {
                $this->dispatch(new UpdateGitMirrorJob($project));
            }
        });
    }
}
