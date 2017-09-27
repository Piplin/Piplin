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

use Fixhub\Bus\Events\DeployFinished;
use Fixhub\Bus\Events\EmailChangeRequested;
use Fixhub\Bus\Events\JsonWebTokenExpired;
use Fixhub\Bus\Events\UserWasCreated;
use Fixhub\Bus\Listeners\ClearJwt;
use Fixhub\Bus\Listeners\CreateJwt;
use Fixhub\Bus\Listeners\EmailChangeConfirmation;
use Fixhub\Bus\Listeners\NotifyDeploy;
use Fixhub\Bus\Listeners\SendSignupEmail;
use Fixhub\Models\Hook;
use Fixhub\Models\ServerLog;
use Fixhub\Bus\Observers\HookObserver;
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
        DeployFinished::class       => [NotifyDeploy::class],
        EmailChangeRequested::class => [EmailChangeConfirmation::class],
        UserWasCreated::class       => [SendSignupEmail::class],
        JsonWebTokenExpired::class  => [CreateJwt::class],
        Login::class                => [CreateJwt::class],
        Logout::class               => [ClearJwt::class],
        SocialiteWasCalled::class => [
            'SocialiteProviders\GitLab\GitLabExtendSocialite@handle',
        ],
    ];

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        parent::boot();

        Hook::observe(HookObserver::class);
        ServerLog::observe(ServerLogObserver::class);
    }
}
