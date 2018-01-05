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
use Piplin\Services\Scripts\Runner as Process;

/**
 * Clears out any old artifacts which have been left on disk.
 */
class ClearOldArtifacts extends Command
{
    const BUILDS_TO_KEEP = 50;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'piplin:purge-builds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears out any old artifacts which have been left on disk.';

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
        $process = new Process('cd ' . storage_path('app/artifacts/'), [], false);

        $process->appendScript('(ls -t|head -n '. self::BUILDS_TO_KEEP .';ls)|sort|uniq -u|xargs rm -rf');

        $process->run();

        if ($process->isSuccessful()) {
            $this->info('Deleted');
        } else {
            $this->info('Failed to delete');
        }
    }
}
