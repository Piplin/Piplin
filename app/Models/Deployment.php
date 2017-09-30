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

use Fixhub\Bus\Events\ModelChangedEvent;
use Fixhub\Presenters\RuntimeInterface;
use Fixhub\Presenters\DeploymentPresenter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use McCool\LaravelAutoPresenter\HasPresenter;

/**
 * Deployment model.
 */
class Deployment extends Model implements HasPresenter, RuntimeInterface
{
    use SoftDeletes;

    const COMPLETED             = 0;
    const PENDING               = 1;
    const DEPLOYING             = 2;
    const FAILED                = 3;
    const COMPLETED_WITH_ERRORS = 4;
    const ABORTING              = 5;
    const ABORTED               = 6;
    const APPROVING             = 7;
    const APPROVED              = 8;
    const LOADING               = 'Loading';

    public static $currentDeployment = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['reason', 'branch', 'project_id', 'source', 'build_url',
                           'commit', 'committer_email', 'committer', ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'deleted_at', 'updated_at', 'user', 'commands'];

    /**
     * Additional attributes to include in the JSON representation.
     *
     * @var array
     */
    protected $appends = ['project_name', 'deployer_name', 'commit_url',
                          'short_commit', 'branch_url', 'repo_failure', ];

    /**
     * The fields which should be tried as Carbon instances.
     *
     * @var array
     */
    protected $dates = ['started_at', 'finished_at'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'             => 'integer',
        'project_id'     => 'integer',
        'user_id'        => 'integer',
        'status'         => 'integer',
        'is_webhook'     => 'boolean',
    ];

    /**
     * Belongs to relationship.
     *
     * @return Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Belongs to many relationship.
     *
     * @return Server
     */
    
    public function environments()
    {
        return $this->belongsToMany(Environment::class)
                    ->orderBy('id', 'ASC');
    }


    /**
     * Belongs to relationship.
     *
     * @return User
     */
    public function user()
    {
        return $this->belongsTo(User::class)
                    ->withTrashed();
    }

    /**
     * Define a command attribute to be able to access to commands relationship.
     *
     * @return Command
     */
    public function getCommandsAttribute()
    {
        if (!$this->relationLoaded('commands')) {
            $this->loadCommands();
        }

        if ($this->relationLoaded('commands')) {
            return $this->getRelation('commands');
        }

        return collect([]);
    }

    /**
     * Query the DB and load the HasMany relationship for commands.
     *
     * @return Deployment
     */
    private function loadCommands()
    {
        $collection = Command::join('deploy_steps', 'commands.id', '=', 'deploy_steps.command_id')
                             ->where('deploy_steps.deployment_id', $this->getKey())
                             ->distinct()
                             ->orderBy('step')
                             ->orderBy('order')
                             ->get(['commands.*', 'deployment_id']);

        $hasMany = new HasMany(Command::query(), $this, 'deployment_id', 'id');
        $hasMany->matchMany([$this], $collection, 'commands');

        return $this;
    }

    /**
     * Has many relationship.
     *
     * @return DeployStep
     */
    public function steps()
    {
        return $this->hasMany(DeployStep::class);
    }

    /**
     * Determines whether the deployment is running.
     *
     * @return bool
     */
    public function isRunning()
    {
        return ($this->status === self::DEPLOYING);
    }

    /**
     * Determines whether the deployment is pending.
     *
     * @return bool
     */
    public function isPending()
    {
        return ($this->status === self::PENDING);
    }

    /**
     * Determines whether the deployment is successful.
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return ($this->status === self::COMPLETED);
    }

    /**
     * Determines whether the deployment failed.
     *
     * @return bool
     */
    public function isFailed()
    {
        return ($this->status === self::FAILED);
    }

    /**
     * Determines whether the deployment is waiting to be approved.
     *
     * @return bool
     */
    public function isApproving()
    {
        return ($this->status === self::APPROVING);
    }

    /**
     * Determines whether the deployment is approved.
     *
     * @return bool
     */
    public function isApproved()
    {
        return ($this->status === self::APPROVED);
    }

    /**
     * Determines whether the deployment is waiting to be aborted.
     *
     * @return bool
     */
    public function isAborting()
    {
        return ($this->status === self::ABORTING);
    }

    /**
     * Determines whether the deployment is aborted.
     *
     * @return bool
     */
    public function isAborted()
    {
        return ($this->status === self::ABORTED);
    }

    /**
     * Determines if the deployment is the latest deployment.
     *
     * @return bool
     */
    public function isCurrent()
    {
        if (!isset(self::$currentDeployment[$this->project_id])) {
            self::$currentDeployment[$this->project_id] = self::where('project_id', $this->project_id)
                                                              ->where('status', self::COMPLETED)
                                                              ->orderBy('id', 'desc')
                                                              ->first();
        }

        if (isset(self::$currentDeployment[$this->project_id])) {
            return (self::$currentDeployment[$this->project_id]->id === $this->id);
        }

        return false;
    }

    /**
     * Determines how long the deploy took.
     *
     * @return false|int False if the deploy is still running, otherwise the runtime in seconds
     */
    public function runtime()
    {
        if (!$this->finished_at) {
            return;
        }

        return $this->started_at->diffInSeconds($this->finished_at);
    }

    /**
     * Gets the HTTP URL to the commit.
     *
     * @return string|false
     */
    public function getCommitUrlAttribute()
    {
        if ($this->commit !== self::LOADING) {
            $info = $this->project->accessDetails();
            if (isset($info['domain']) && isset($info['reference'])) {
                $path = 'commit';
                if (preg_match('/bitbucket/', $info['domain'])) {
                    $path = 'commits';
                }

                return 'http://' . $info['domain'] . '/' . $info['reference'] . '/' . $path . '/' . $this->commit;
            }
        }

        return false;
    }

    /**
     * Gets the short commit hash.
     *
     * @return string
     */
    public function getShortCommitAttribute()
    {
        if ($this->commit !== self::LOADING) {
            return substr($this->commit, 0, 7);
        }

        return $this->commit;
    }

    /**
     * Gets the HTTP URL to the branch.
     *
     * @return string|false
     * @see \Fixhub\Models\Project::accessDetails()
     */
    public function getBranchURLAttribute()
    {
        return $this->project->getBranchUrlAttribute($this->branch);
    }

    /**
     * Define a accessor for the project name.
     *
     * @return string
     */
    public function getProjectNameAttribute()
    {
        return $this->project->name;
    }

    /**
     * Define a accessor for the fixhub name.
     *
     * @return string
     */
    public function getDeployerNameAttribute()
    {
        if (!empty($this->user_id)) {
            return $this->user->name;
        } elseif (!empty($this->source)) {
            return $this->source;
        }

        return $this->committer;
    }

    /**
     * Checks whether the repository failed to load.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getRepoFailureAttribute()
    {
        return ($this->commit === self::LOADING && $this->status === self::FAILED);
    }

    /**
     * Mutator to get the release ID.
     *
     * @return string
     */
    public function getReleaseIdAttribute()
    {
        return $this->started_at->format('YmdHis');
    }

    /**
     * Get the presenter class.
     *
     * @return string
     */
    public function getPresenterClass()
    {
        return DeploymentPresenter::class;
    }
}
