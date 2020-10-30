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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use McCool\LaravelAutoPresenter\HasPresenter;
use Piplin\Models\Traits\BroadcastChanges;
use Piplin\Models\Traits\HasTargetable;
use Piplin\Models\Traits\SetupRelations;
use Piplin\Presenters\ProjectPresenter;
use Piplin\Services\Scripts\Runner as Process;
use UnexpectedValueException;
use Version\Compare as VersionCompare;

/**
 * Project model.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $repository
 * @property string $hash
 * @property string $branch
 * @property string $targetable_type
 * @property int $targetable_id
 * @property string $deploy_path
 * @property int|null $key_id
 * @property int $builds_to_keep
 * @property string|null $url
 * @property string|null $build_url
 * @property bool $allow_other_branch
 * @property int $status
 * @property string|null $private_key
 * @property string|null $public_key
 * @property \Illuminate\Support\Carbon|null $last_run
 * @property \Illuminate\Support\Carbon|null $last_mirrored
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Piplin\Models\BuildPlan|null $buildPlan
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Command[] $commands
 * @property-read int|null $commands_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\ConfigFile[] $configFiles
 * @property-read int|null $config_files_count
 * @property-read \Piplin\Models\DeployPlan|null $deployPlan
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Environment[] $environments
 * @property-read int|null $environments_count
 * @property-read string|false $branch_url
 * @property-read string $build_webhook
 * @property-read string $clean_deploy_path
 * @property-read string $deploy_webhook
 * @property-read string $group_name
 * @property-read string $private_key_content
 * @property-read string $public_key_content
 * @property-read string|false $repository_path
 * @property-read string|false $repository_url
 * @property-read string $webhook_url
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Hook[] $hooks
 * @property-read int|null $hooks_count
 * @property-read \Piplin\Models\Key|null $key
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\User[] $members
 * @property-read int|null $members_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Ref[] $refs
 * @property-read int|null $refs_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Release[] $releases
 * @property-read int|null $releases_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\SharedFile[] $sharedFiles
 * @property-read int|null $shared_files_count
 * @property-read Model|\Eloquent $targetable
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Task[] $tasks
 * @property-read int|null $tasks_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Variable[] $variables
 * @property-read int|null $variables_count
 * @method static \Illuminate\Database\Eloquent\Builder|Project newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Project newQuery()
 * @method static \Illuminate\Database\Query\Builder|Project onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Project query()
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereAllowOtherBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereBuildUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereBuildsToKeep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereDeployPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereKeyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereLastMirrored($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereLastRun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project wherePrivateKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project wherePublicKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereRepository($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereTargetableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereTargetableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereUrl($value)
 * @method static \Illuminate\Database\Query\Builder|Project withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Project withoutTrashed()
 * @mixin \Eloquent
 */
class Project extends Model implements HasPresenter
{
    use SoftDeletes, BroadcastChanges, SetupRelations, HasTargetable;

    const FINISHED = 0;
    const PENDING  = 1;
    const RUNNING  = 2;
    const FAILED   = 3;
    const INITIAL  = 4;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'deleted_at',
        'updated_at',
        'hash',
        'hooks',
        'commands',
        'targetable',
        'key',
        'tasks',
        'sharedFiles',
        'configFiles',
        'last_mirrored',
        'private_key',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'repository',
        'branch',
        'targetable_type',
        'targetable_id',
        'key_id',
        'deploy_path',
        'builds_to_keep',
        'url',
        'build_url',
        'allow_other_branch',
    ];

    /**
     * The fields which should be treated as Carbon instances.
     *
     * @var array
     */
    protected $dates = [
        'last_run',
        'last_mirrored',
    ];

    /**
     * Additional attributes to include in the JSON representation.
     *
     * @var array
     */
    protected $appends = [
        'group_name',
        'webhook_url',
        'repository_path',
        'repository_url',
        'branch_url',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'                 => 'integer',
        'status'             => 'integer',
        'builds_to_keep'     => 'integer',
        'allow_other_branch' => 'boolean',
    ];

    /**
     * Determines whether the project is currently being deployed.
     *
     * @return bool
     */
    public function isRunning()
    {
        return ($this->status === self::RUNNING || $this->status === self::PENDING);
    }

    /**
     * Generates a hash for use in the webhook URL.
     *
     * @return void
     */
    public function generateHash()
    {
        $this->attributes['hash'] = Str::random(60);
    }

    /**
     * Parses the repository URL to get the user, domain, port and path parts.
     *
     * @return array
     */
    public function accessDetails()
    {
        $info = [];

        if (preg_match('#^(.+)@(.+):([0-9]*)\/?(.+)\.git$#', $this->repository, $matches)) {
            $info['user']      = $matches[1];
            $info['domain']    = $matches[2];
            $info['port']      = $matches[3];
            $info['reference'] = $matches[4];
        } elseif (preg_match('#^https?#', $this->repository)) {
            $data         = parse_url($this->repository);
            $data['path'] = $data['path'] ?? '';

            $info['user']      = $data['user'] ?? '';
            $info['domain']    = $data['host'];
            $info['port']      = $data['port'] ?? '';
            $info['reference'] = substr(str_replace('.git', '', $data['path']), 1);
        }

        return $info;
    }

    /**
     * Checks ability for specified project and user.
     *
     * @param string $name
     * @param User   $user
     *
     * @return bool
     */
    public function can($name, User $user = null)
    {
        if ($user === null) {
            $user = Auth::user();
        }

        $roleCheck = true;
        if ($name === 'manage') {
            $roleCheck = $user->is_manager;
        }

        static $isMember = null;
        if (is_null($isMember)) {
            $isMember = ($this->targetable instanceof User && $this->targetable->id === $user->id)
                        || $this->members()->find($user->id) !== null;
        }

        return $user->is_admin || ($roleCheck && $isMember);
    }

    /**
     * The deploy path without a trailing slash.
     *
     * @return string
     */
    public function getCleanDeployPathAttribute()
    {
        return preg_replace('#/$#', '', $this->deploy_path);
    }

    /**
     * Gets the repository path.
     *
     * @return string|false
     * @see \Piplin\Models\Project::accessDetails()
     */
    public function getRepositoryPathAttribute()
    {
        $info = $this->accessDetails();

        if (isset($info['reference'])) {
            return $info['reference'];
        } else {
            //Support local repository
            return $this->repository;
        }
    }

    /**
     * Gets the HTTP URL to the repository.
     *
     * @return string|false
     * @see \Piplin\Models\Project::accessDetails()
     */
    public function getRepositoryUrlAttribute()
    {
        $info = $this->accessDetails();

        if (isset($info['domain']) && isset($info['reference'])) {
            $port = $info['port'] ?? '';

            return 'http://' . $info['domain'] . $port . '/' . $info['reference'];
        }

        return false;
    }

    /**
     * Gets the HTTP URL to the branch.
     *
     * @param  string       $alternative
     * @return string|false
     * @see \Piplin\Models\Project::accessDetails()
     */
    public function getBranchUrlAttribute($alternative = null)
    {
        $info = $this->accessDetails();

        if (isset($info['domain']) && isset($info['reference'])) {
            $path = 'tree';
            if (preg_match('/bitbucket/', $info['domain'])) {
                $path = 'commits/branch';
            }

            $branch = (is_null($alternative) ? $this->branch : $alternative);

            return 'http://' . $info['domain'] . '/' . $info['reference'] . '/' . $path . '/' . $branch;
        }

        return false;
    }

    /**
     * Define an accessor for the public key content.
     *
     * @return string
     */
    public function getPublicKeyContentAttribute()
    {
        if (!$this->key) {
            return $this->public_key;
        } else {
            return $this->key->public_key;
        }
    }

    /**
     * Define an accessor for the private key content.
     *
     * @return string
     */
    public function getPrivateKeyContentAttribute()
    {
        if (!$this->key) {
            return $this->private_key;
        } else {
            return $this->key->private_key;
        }
    }

    /**
     * Define an accessor for the group name.
     *
     * @return string
     */
    public function getGroupNameAttribute()
    {
        return $this->targetable ? $this->targetable->name : null;
    }

    /**
     * Define an accessor for the webhook URL.
     *
     * @return string
     */
    public function getWebhookUrlAttribute()
    {
        return route('webhook.deploy', $this->hash);
    }

    /**
     * Define an accessor for the deploy webhook URL.
     *
     * @return string
     */
    public function getDeployWebhookAttribute()
    {
        return route('webhook.deploy', $this->hash);
    }

    /**
     * Define an accessor for the build webhook URL.
     *
     * @return string
     */
    public function getBuildWebhookAttribute()
    {
        return route('webhook.build', $this->hash);
    }

    /**
     * Get the build plan associated with the project.
     */
    public function buildPlan()
    {
        return $this->hasOne(BuildPlan::class);
    }

    /**
     * Get the build plan associated with the project.
     */
    public function deployPlan()
    {
        return $this->hasOne(DeployPlan::class);
    }

    /**
     * Belongs to many relationship.
     *
     * @return Server
     */
    public function members()
    {
        return $this->belongsToMany(User::class)->withPivot(['id', 'status']);
    }

    /**
     * Belongs to relationship.
     *
     * @return Key
     */
    public function key()
    {
        return $this->belongsTo(Key::class, 'key_id', 'id');
    }

    /**
     * Has many relationship.
     *
     * @return Task
     */
    public function tasks()
    {
        return $this->hasMany(Task::class)
                    ->orderBy('started_at', 'DESC');
    }

    /**
     * Has many relationship.
     *
     * @return Hook
     */
    public function hooks()
    {
        return $this->hasMany(Hook::class)
                    ->orderBy('name');
    }

    /**
     * Has many relationship for git references.
     *
     * @see PPiplin\Models\Project::tags()
     * @see PPiplin\Models\Project::branches()
     * @return Ref
     */
    public function refs()
    {
        return $this->hasMany(Ref::class);
    }

    /**
     * Gets the list of all tags for the project.
     *
     * @return \Illuminate\Support\Collection
     */
    public function tags()
    {
        $tags = $this->refs()
                     ->where('is_tag', true)
                     ->pluck('name')
                     ->toArray();
        $compare = new VersionCompare;
        // Sort the tags, if compare throws an exception it isn't a value version string so just do a strnatcmp
        @usort($tags, function ($first, $second) use ($compare) {
            try {
                return $compare->compare($first, $second);
            } catch (UnexpectedValueException $error) {
                return strnatcmp($first, $second);
            }
        });

        return collect($tags);
    }

    /**
     * Gets the list of all branches for the project which are not the default.
     *
     * @return Collection
     */
    public function branches()
    {
        return $this->refs()
                    ->where('is_tag', false)
                    ->where('name', '<>', $this->branch)
                    ->orderBy('name')
                    ->pluck('name');
    }

    /**
     * Has many relationship.
     *
     * @return Release
     */
    public function releases()
    {
        return $this->hasMany(Release::class)
                    ->orderBy('id', 'DESC');
    }

    /**
     * Generate a friendly path for the mirror of the repository.
     * Use the repository rather than the project ID, so if a single
     * repo is used in multiple projects it is not duplicated.
     *
     * @return string
     */
    public function mirrorPath()
    {
        return storage_path('app/mirrors/' . preg_replace('/[^_\-.\-a-zA-Z0-9\s]/u', '_', $this->repository));
    }

    /**
     * Get the presenter class.
     *
     * @return string
     */
    public function getPresenterClass()
    {
        return ProjectPresenter::class;
    }
}
