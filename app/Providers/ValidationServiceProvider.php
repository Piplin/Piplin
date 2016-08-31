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

use Fixhub\Validators\ChannelValidator;
use Fixhub\Validators\HostValidator;
use Fixhub\Validators\RepositoryValidator;
use Fixhub\Validators\SSHKeyValidator;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider to register the validation classes.
 **/
class ValidationServiceProvider extends ServiceProvider
{
    public $validators = [
        'channel'    => ChannelValidator::class,
        'repository' => RepositoryValidator::class,
        'sshkey'     => SSHKeyValidator::class,
        'host'       => HostValidator::class,
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        foreach ($this->validators as $field => $validator) {
            $this->app->validator->extend($field, $validator . '@validate');
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
