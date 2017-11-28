<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Piplin\Bus\Events\TaskFinishedEvent;
use Piplin\Bus\Events\EmailChangeRequestedEvent;
use Piplin\Bus\Events\JsonWebTokenExpiredEvent;
use Piplin\Bus\Events\UserWasCreatedEvent;
use Piplin\Bus\Listeners\ClearJwtListener;
use Piplin\Bus\Listeners\CreateJwtListener;
use Piplin\Bus\Listeners\DeployLinkedEnvironmentListner;
use Piplin\Bus\Listeners\EmailChangeConfirmationListener;
use Piplin\Bus\Listeners\NotifyDeployListener;
use Piplin\Bus\Listeners\SendSignupEmailListener;
use Piplin\Bus\Observers\CommandObserver;
use Piplin\Bus\Observers\ConfigFileObserver;
use Piplin\Bus\Observers\TaskObserver;
use Piplin\Bus\Observers\ProjectTemplateObserver;
use Piplin\Bus\Observers\EnvironmentObserver;
use Piplin\Bus\Observers\HookObserver;
use Piplin\Bus\Observers\KeyObserver;
use Piplin\Bus\Observers\PatternObserver;
use Piplin\Bus\Observers\ProjectObserver;
use Piplin\Bus\Observers\ReleaseObserver;
use Piplin\Bus\Observers\ServerLogObserver;
use Piplin\Bus\Observers\ServerObserver;
use Piplin\Models\Command;
use Piplin\Models\ConfigFile;
use Piplin\Models\Task;
use Piplin\Models\Pattern;
use Piplin\Models\ProjectTemplate;
use Piplin\Models\Environment;
use Piplin\Models\Hook;
use Piplin\Models\Key;
use Piplin\Models\Project;
use Piplin\Models\Release;
use Piplin\Models\Server;
use Piplin\Models\ServerLog;
use SocialiteProviders\Manager\SocialiteWasCalled;

/**
 * The event service provider.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        TaskFinishedEvent::class       => [
            DeployLinkedEnvironmentListner::class,
            NotifyDeployListener::class,
        ],
        EmailChangeRequestedEvent::class => [EmailChangeConfirmationListener::class],
        UserWasCreatedEvent::class       => [SendSignupEmailListener::class],
        JsonWebTokenExpiredEvent::class  => [CreateJwtListener::class],
        Login::class                     => [CreateJwtListener::class],
        Logout::class                    => [ClearJwtListener::class],
        SocialiteWasCalled::class        => [
            'SocialiteProviders\GitLab\GitLabExtendSocialite@handle',
        ],
    ];

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        parent::boot();

        Command::observe(CommandObserver::class);
        ConfigFile::observe(ConfigFileObserver::class);
        Task::observe(TaskObserver::class);
        ProjectTemplate::observe(ProjectTemplateObserver::class);
        Environment::observe(EnvironmentObserver::class);
        Key::observe(KeyObserver::class);
        Hook::observe(HookObserver::class);
        Pattern::observe(PatternObserver::class);
        Project::observe(ProjectObserver::class);
        Release::observe(ReleaseObserver::class);
        Server::observe(ServerObserver::class);
        ServerLog::observe(ServerLogObserver::class);
    }
}
