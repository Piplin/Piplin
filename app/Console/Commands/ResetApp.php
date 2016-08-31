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

use Illuminate\Foundation\Testing\Concerns\MocksApplicationServices;

/**
 * A console command for clearing all data and setting up again.
 */
class ResetApp extends UpdateApp
{
    use MocksApplicationServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Used during development to clear the database and logs';

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
        if (!$this->verifyNotProduction()) {
            return;
        }

        $this->app = $this->laravel;

        $this->withoutEvents();

        $this->clearLogs();
        $this->updateConfiguration();
        $this->resetDB();
        $this->migrate(true);
        $this->clearCaches();
        $this->restartQueue();
        $this->restartSocket();
    }

    /**
     * Resets the database.
     *
     * @return void
     */
    protected function resetDB()
    {
        $this->info('Resetting the database');
        $this->line('');
        $this->call('migrate:reset', ['--force' => true]);
        $this->line('');
    }

    /**
     * Removes the log files.
     *
     * @return void
     */
    protected function clearLogs()
    {
        $this->info('Removing log files');
        $this->line('');

        foreach (glob(storage_path('logs/') . '*.log*') as $file) {
            unlink($file);
        }
    }

    /**
     * Ensures that the command is running locally and in debugging mode.
     *
     * @return bool
     */
    private function verifyNotProduction()
    {
        if (config('app.env') !== 'local') {
            $this->block([
                'Fixhub is not in development mode!',
                PHP_EOL,
                'This command does not run in production as its purpose is to wipe your database',
            ]);

            return false;
        }

        return true;
    }
}
