<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Kernel class.
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \Piplin\Console\Commands\BackupDatabase::class,
        \Piplin\Console\Commands\CreateUser::class,
        \Piplin\Console\Commands\ClearOldArtifacts::class,
        \Piplin\Console\Commands\ClearOrphanAvatars::class,
        \Piplin\Console\Commands\ClearOrphanMirrors::class,
        \Piplin\Console\Commands\ClearStalledTask::class,
        \Piplin\Console\Commands\ClearOldKeys::class,
        \Piplin\Console\Commands\UpdateGitMirrors::class,
        \Piplin\Console\Commands\InstallApp::class,
        \Piplin\Console\Commands\UpdateApp::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('piplin:update-mirrors')
                 ->everyFiveMinutes()
                 ->withoutOverlapping();

        $schedule->command('piplin:purge-avatars')
                 ->weekly()
                 ->sundays()
                 ->at('00:30')
                 ->withoutOverlapping();

        $schedule->command('piplin:purge-mirrors')
                 ->daily()
                 ->withoutOverlapping();

        $schedule->command('piplin:purge-temp')
                 ->hourly()
                 ->withoutOverlapping();

        $schedule->command('piplin:purge-builds')
                 ->daily()
                 ->withoutOverlapping();

        $schedule->command('piplin:backup-database')
                 ->daily()
                 ->withoutOverlapping();
    }

    /**
     * Bootstrap the application for artisan commands.
     *
     * @return void
     */
    public function bootstrap()
    {
        parent::bootstrap();

        // Only register the reset command on the local environment
        if ($this->app->environment('local')) {
            $this->commands[] = \Piplin\Console\Commands\ResetApp::class;
        }
    }
}
