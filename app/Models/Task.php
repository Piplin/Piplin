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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use McCool\LaravelAutoPresenter\HasPresenter;
use Piplin\Models\Traits\BroadcastChanges;
use Piplin\Models\Traits\HasTargetable;
use Piplin\Presenters\TaskPresenter;
use Piplin\Presenters\RuntimeInterface;

/**
 * Task model.
 *
 * @property int $id
 * @property string|null $committer
 * @property string $committer_email
 * @property string|null $commit
 * @property int $project_id
 * @property string $targetable_type
 * @property int $targetable_id
 * @property int|null $user_id
 * @property int $status
 * @property bool $is_webhook
 * @property string $branch
 * @property object|null $payload
 * @property string|null $reason
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property string|null $source
 * @property string|null $build_url
 * @property string|null $output
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Artifact[] $artifacts
 * @property-read int|null $artifacts_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Environment[] $environments
 * @property-read int|null $environments_count
 * @property-read string $author_name
 * @property-read string|false $branch_u_r_l
 * @property-read \Piplin\Models\Command $commands
 * @property-read string|false $commit_url
 * @property-read bool $is_build
 * @property-read string $project_name
 * @property-read string $release_id
 * @property-read bool $run_failure
 * @property-read string $short_commit
 * @property-read string $title
 * @property-read \Piplin\Models\Project $project
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Release[] $releases
 * @property-read int|null $releases_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\TaskStep[] $steps
 * @property-read int|null $steps_count
 * @property-read Model|\Eloquent $targetable
 * @property-read \Piplin\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Task newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task newQuery()
 * @method static \Illuminate\Database\Query\Builder|Task onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Task query()
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereBuildUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereCommit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereCommitter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereCommitterEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereIsWebhook($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereOutput($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereTargetableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereTargetableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|Task withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Task withoutTrashed()
 * @mixin \Eloquent
 */
class Task extends Model implements HasPresenter, RuntimeInterface
{
    use SoftDeletes, BroadcastChanges, HasTargetable;

    const DRAFT                 = -1;
    const COMPLETED             = 0;
    const PENDING               = 1;
    const RUNNING               = 2;
    const FAILED                = 3;
    const COMPLETED_WITH_ERRORS = 4;
    const ABORTING              = 5;
    const ABORTED               = 6;
    const LOADING               = 'Loading';

    public static $currentTask = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reason',
        'branch',
        'project_id',
        'targetable_type',
        'targetable_id',
        'user_id',
        'status',
        'source',
        'build_url',
        'commit',
        'committer_email',
        'committer',
        'payload',
    ];

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
    protected $appends = ['project_name', 'author_name', 'commit_url',
                          'short_commit', 'branch_url', 'run_failure',];

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
        'id'         => 'integer',
        'project_id' => 'integer',
        'user_id'    => 'integer',
        'status'     => 'integer',
        'is_webhook' => 'boolean',
        'payload'    => 'object',
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
                    ->orderBy('order', 'ASC');
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
     * @return Command
     */
    private function loadCommands()
    {
        $collection = Command::join('task_steps', 'commands.id', '=', 'task_steps.command_id')
                             ->where('task_steps.task_id', $this->getKey())
                             ->distinct()
                             ->orderBy('step')
                             ->orderBy('order')
                             ->get(['commands.*', 'task_id']);

        $hasMany = new HasMany(Command::query(), $this, 'task_id', 'id');
        $hasMany->matchMany([$this], $collection, 'commands');

        return $this;
    }

    /**
     * Has many relationship.
     *
     * @return TaskStep
     */
    public function steps()
    {
        return $this->hasMany(TaskStep::class);
    }

    /**
     * Has many relationship.
     *
     * @return Artifact
     */
    public function artifacts()
    {
        return $this->hasMany(Artifact::class);
    }

    /**
     * Has many relationship.
     *
     * @return Release
     */
    public function releases()
    {
        return $this->hasMany(Release::class);
    }

    /**
     * Determines whether the task is draft.
     *
     * @return bool
     */
    public function isDraft()
    {
        return ($this->status === self::DRAFT);
    }

    /**
     * Determines whether the task is running.
     *
     * @return bool
     */
    public function isRunning()
    {
        return ($this->status === self::RUNNING);
    }

    /**
     * Determines whether the task is pending.
     *
     * @return bool
     */
    public function isPending()
    {
        return ($this->status === self::PENDING);
    }

    /**
     * Determines whether the task is successful.
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return ($this->status === self::COMPLETED);
    }

    /**
     * Determines whether the task failed.
     *
     * @return bool
     */
    public function isFailed()
    {
        return ($this->status === self::FAILED);
    }

    /**
     * Determines whether the task is waiting to be aborted.
     *
     * @return bool
     */
    public function isAborting()
    {
        return ($this->status === self::ABORTING);
    }

    /**
     * Determines whether the task is aborted.
     *
     * @return bool
     */
    public function isAborted()
    {
        return ($this->status === self::ABORTED);
    }

    /**
     * Determines if the task is the latest task.
     *
     * @return bool
     */
    public function isCurrent()
    {
        if (!isset(self::$currentTask[$this->project_id])) {
            self::$currentTask[$this->project_id] = self::where('project_id', $this->project_id)
                                                        ->where('status', self::COMPLETED)
                                                        ->orderBy('id', 'desc')
                                                        ->first();
        }

        if (isset(self::$currentTask[$this->project_id])) {
            return (self::$currentTask[$this->project_id]->id === $this->id);
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
     * Determines whether the task is build.
     *
     * @return bool
     */
    public function getIsBuildAttribute()
    {
        return $this->targetable instanceof BuildPlan;
    }

    /**
     * Gets the HTTP URL to the commit.
     *
     * @return string|false
     */
    public function getCommitUrlAttribute()
    {
        if ($this->commit !== self::LOADING && $this->project) {
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
     * @see \Piplin\Models\Project::accessDetails()
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
     * Define a accessor for the task author name.
     *
     * @return string
     */
    public function getAuthorNameAttribute()
    {
        if (!empty($this->user_id)) {
            return $this->user->name;
        } elseif (!empty($this->source)) {
            return $this->source;
        }

        return $this->committer;
    }

    /**
     * Checks whether the task failed.
     *
     * @return bool
     */
    public function getRunFailureAttribute()
    {
        return ($this->output !== null && $this->status === self::FAILED);
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
     * Gets the task title.
     *
     * @return string
     */
    public function getTitleAttribute()
    {
        if ($this->targetable instanceof BuildPlan) {
            $title = 'tasks.build_title';
        } else {
            $title = 'tasks.deploy_title';
        }

        return trans($title, ['id' => $this->id]);
    }

    /**
     * Get the presenter class.
     *
     * @return string
     */
    public function getPresenterClass()
    {
        return TaskPresenter::class;
    }
}
