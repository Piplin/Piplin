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

use Illuminate\Support\ServiceProvider;
use Piplin\Validators\ChannelValidator;
use Piplin\Validators\HostValidator;
use Piplin\Validators\RepositoryValidator;
use Piplin\Validators\SSHKeyValidator;

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
