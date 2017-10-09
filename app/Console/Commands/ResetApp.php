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

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Console\Command;
use Fixhub\Bus\Events\RestartSocketServerEvent;

/**
 * A console command for clearing all data and setting up again.
 */
class ResetApp extends Command
{
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
    public function handle(Dispatcher $dispatcher)
    {
        if (!$this->verifyNotProduction()) {
            return -1;
        }

        $this->callSilent('down');

        $this->resetDatabase();
        $this->clearLogs();
        $this->restartQueue();
        $this->restartSocket($dispatcher);

        $this->callSilent('up');

        return 0;
    }

    /**
    * Resets the database.
    */
    protected function resetDatabase()
    {
        $this->callSilent('migrate', ['--force' => true]);
        $this->callSilent('app:update');
        $this->call('migrate:fresh', ['--seed' => true, '--force' => true]);
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
     * Restarts the queues.
     */
    protected function restartQueue()
    {
        $this->info('Restarting the queue');
        $this->line('');
        $this->call('queue:flush');
        $this->call('queue:restart');
        $this->line('');
    }

    /**
     * Restarts the socket server.
     *
     * @param Dispatcher $dispatcher
     */
    protected function restartSocket(Dispatcher $dispatcher)
    {
        $this->info('Restarting the socket server');
        $dispatcher->dispatch(new RestartSocketServerEvent());
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
