<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Models;

use Fixhub\Models\Traits\SetupRelations;
use Fixhub\Presenters\DeployTemplatePresenter;
use Illuminate\Database\Eloquent\Model;
use McCool\LaravelAutoPresenter\HasPresenter;

/**
 * Model for deploy templates.
 */
class DeployTemplate extends Model implements HasPresenter
{
    use SetupRelations;

    /**
     * Fields to show in the JSON presentation.
     *
     * @var array
     */
    protected $visible = ['id', 'name', 'command_count', 'file_count',
                          'config_count', 'variable_count', 'environment_count'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * Additional attributes to include in the JSON representation.
     *
     * @var array
     */
    protected $appends = ['command_count', 'file_count', 'config_count', 'variable_count', 'environment_count'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'          => 'integer',
    ];

    /**
     * Define a accessor for the count of projects.
     *
     * @return int
     */
    public function getCommandCountAttribute()
    {
        return $this->commands()
                    ->count();
    }

    /**
     * Define a accessor for the count of persistent files.
     *
     * @return int
     */
    public function getFileCountAttribute()
    {
        return $this->sharedFiles()
                    ->count();
    }

    /**
     * Define a accessor for the count of config files.
     *
     * @return int
     */
    public function getConfigCountAttribute()
    {
        return $this->configFiles()
                    ->count();
    }

    /**
     * Define a accessor for the count of env variables.
     *
     * @return int
     */
    public function getVariableCountAttribute()
    {
        return $this->variables()
                    ->count();
    }

    /**
     * Define a accessor for the count of env environments.
     *
     * @return int
     */
    public function getEnvironmentCountAttribute()
    {
        return $this->environments()
                    ->count();
    }

    /**
     * Get the presenter class.
     *
     * @return string
     */
    public function getPresenterClass()
    {
        return DeployTemplatePresenter::class;
    }
}
