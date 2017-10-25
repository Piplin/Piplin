<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Providers;

use Fixhub\Bus\Events\DeployFinishedEvent;
use Fixhub\Bus\Events\EmailChangeRequestedEvent;
use Fixhub\Bus\Events\JsonWebTokenExpiredEvent;
use Fixhub\Bus\Events\UserWasCreatedEvent;
use Fixhub\Bus\Listeners\ClearJwtListener;
use Fixhub\Bus\Listeners\CreateJwtListener;
use Fixhub\Bus\Listeners\EmailChangeConfirmationListener;
use Fixhub\Bus\Listeners\NotifyDeployListener;
use Fixhub\Bus\Listeners\SendSignupEmailListener;
use Fixhub\Bus\Listeners\DeployLinkedEnvironmentListner;
use Fixhub\Models\Deployment;
use Fixhub\Models\Hook;
use Fixhub\Models\Key;
use Fixhub\Models\Project;
use Fixhub\Models\ServerLog;
use Fixhub\Bus\Observers\DeploymentObserver;
use Fixhub\Bus\Observers\HookObserver;
use Fixhub\Bus\Observers\KeyObserver;
use Fixhub\Bus\Observers\ProjectObserver;
use Fixhub\Bus\Observers\ServerLogObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
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
        DeployFinishedEvent::class       => [
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

        Deployment::observe(DeploymentObserver::class);
        Key::observe(KeyObserver::class);
        Hook::observe(HookObserver::class);
        Project::observe(ProjectObserver::class);
        ServerLog::observe(ServerLogObserver::class);
    }
}
