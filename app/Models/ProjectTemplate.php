<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Models;

use Illuminate\Database\Eloquent\Model;
use McCool\LaravelAutoPresenter\HasPresenter;
use Piplin\Models\Traits\SetupRelations;
use Piplin\Models\Traits\BroadcastChanges;
use Piplin\Presenters\ProjectTemplatePresenter;

/**
 * Model for project templates.
 */
class ProjectTemplate extends Model implements HasPresenter
{
    use SetupRelations, BroadcastChanges;

    /**
     * Fields to show in the JSON presentation.
     *
     * @var array
     */
    protected $visible = ['id', 'name', 'command_count', 'file_count',
                          'config_count', 'variable_count', 'environment_count', ];

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
     * Checks ability for specified project and user.
     *
     * @param string $name
     * @param User   $user
     *
     * @return bool
     */
    public function can($name = '', User $user = null)
    {
        return true;
    }

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
        return ProjectTemplatePresenter::class;
    }
}
