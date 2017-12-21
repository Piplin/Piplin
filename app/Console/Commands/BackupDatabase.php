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

use Carbon\Carbon;
use Illuminate\Console\Command;
use Piplin\Services\Scripts\Runner as Process;

/**
 * Database backup manager.
 */
class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'piplin:backup-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes database backup for piplin';

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
        $this->backupDatabase();
    }

    /**
     * Backup the database.
     */
    protected function backupDatabase()
    {
        $date = Carbon::now()->format('Y-m-d_H-i-s');
        $this->call('db:backup', [
            '--database'        => config('database.default'),
            '--destination'     => config('piplin.backup_type'),
            '--destinationPath' => $date,
            '--compression'     => 'gzip',
        ]);
    }
}
