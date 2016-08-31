<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Console;

use Fixhub\Bootstrap\ConfigureLogging;
use Fixhub\Bootstrap\ConfigureLogging as ConsoleLogging;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Kernel class.
 */
class Kernel extends ConsoleKernel
{
    /**
     * The custom bootstrappers like Logging or Environment detector.
     * @var array
     */
    protected $customBooters = [
        \Illuminate\Foundation\Bootstrap\ConfigureLogging::class => ConsoleLogging::class,
    ];

    /**
     * Disable bootstrapper list.
     * @var array
     */
    protected $disabledBooters = [

    ];

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \Fixhub\Console\Commands\CheckHeartbeats::class,
        \Fixhub\Console\Commands\CheckUrl::class,
        \Fixhub\Console\Commands\CreateUser::class,
        \Fixhub\Console\Commands\ClearOrphanAvatars::class,
        \Fixhub\Console\Commands\ClearOrphanMirrors::class,
        \Fixhub\Console\Commands\ClearStalledDeployment::class,
        \Fixhub\Console\Commands\ClearOldKeys::class,
        \Fixhub\Console\Commands\UpdateGitMirrors::class,
        \Fixhub\Console\Commands\InstallApp::class,
        \Fixhub\Console\Commands\UpdateApp::class,
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
        $schedule->command('fixhub:heartbeats')
                 ->everyFiveMinutes()
                 ->withoutOverlapping();

        $schedule->command('fixhub:update-mirrors')
                 ->everyFiveMinutes()
                 ->withoutOverlapping();

        $schedule->command('fixhub:checkurls')
                 ->everyFiveMinutes()
                 ->withoutOverlapping();

        $schedule->command('fixhub:purge-avatars')
                 ->weekly()
                 ->sundays()
                 ->at('00:30')
                 ->withoutOverlapping();

        $schedule->command('fixhub:purge-mirrors')
                 ->daily()
                 ->withoutOverlapping();

        $schedule->command('fixhub:purge-temp')
                 ->hourly()
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
            $this->commands[] = \Fixhub\Console\Commands\ResetApp::class;
        }
    }

    /**
     * Get the bootstrap classes for the application.
     *
     * @return array
     */
    protected function bootstrappers()
    {
        foreach ($this->bootstrappers as &$bootstrapper) {
            foreach ($this->customBooters as $sourceBooter => $newBooter) {
                if ($bootstrapper === $sourceBooter) {
                    $bootstrapper = $newBooter;
                    unset($this->customBooters[$sourceBooter]);
                }
            }
        }

        return array_merge(
            array_diff(
                $this->bootstrappers,
                $this->disabledBooters
            ),
            $this->customBooters
        );
    }
}
