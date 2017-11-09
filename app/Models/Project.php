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

use Fixhub\Models\Traits\BroadcastChanges;
use Fixhub\Models\Traits\SetupRelations;
use Fixhub\Models\Traits\HasTargetable;
use Fixhub\Presenters\ProjectPresenter;
use Fixhub\Services\Scripts\Runner as Process;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use UnexpectedValueException;
use Version\Compare as VersionCompare;
use McCool\LaravelAutoPresenter\HasPresenter;
use Venturecraft\Revisionable\RevisionableTrait;
use Illuminate\Support\Facades\Auth;

/**
 * Project model.
 */
class Project extends Model implements HasPresenter
{
    use SoftDeletes, BroadcastChanges, SetupRelations, HasTargetable, RevisionableTrait;

    const FINISHED     = 0;
    const PENDING      = 1;
    const DEPLOYING    = 2;
    const FAILED       = 3;
    const NOT_DEPLOYED = 4;

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
        'deployments',
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
        'last_mirrored'
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
        'branch_url'
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
        'allow_other_branch' => 'boolean'
    ];

    /**
     * Revision creations enabled.
     *
     * @var boolean
     */
    protected $revisionCreationsEnabled = true;

    /**
     * Revision ignore attributes.
     *
     * @var array
     */
    protected $dontKeepRevisionOf = ['status', 'last_run', 'last_mirrored'];

    /**
     * Determines whether the project is currently being deployed.
     *
     * @return bool
     */
    public function isDeploying()
    {
        return ($this->status === self::DEPLOYING || $this->status === self::PENDING);
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
            $data = parse_url($this->repository);

            $info['user']      = isset($data['user']) ? $data['user'] : '';
            $info['domain']    = $data['host'];
            $info['port']      = isset($data['port']) ? $data['port'] : '';
            $info['reference'] = substr(str_replace('.git', '', $data['path']), 1);
        }

        return $info;
    }

    /**
     * Checks ability for specified project and user.
     *
     * @param string $name
     * @param User $user
     *
     * @return bool
     */
    public function can($name, User $user = null)
    {
        if ($user === null) {
            $user = Auth::user();
        }

        $roleCheck = true;
        if ($name == 'manage') {
            $roleCheck = $user->is_manager;
        }

        static $isMember = null;
        if (is_null($isMember)) {
            $isMember = ($this->targetable instanceof User && $this->targetable->id == $user->id)
                        || $this->members()->find($user->id) != null;
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
     * @see \Fixhub\Models\Project::accessDetails()
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
     * @see \Fixhub\Models\Project::accessDetails()
     */
    public function getRepositoryUrlAttribute()
    {
        $info = $this->accessDetails();

        if (isset($info['domain']) && isset($info['reference'])) {
            return 'http://' . $info['domain'] . '/' . $info['reference'];
        }

        return false;
    }

    /**
     * Gets the HTTP URL to the branch.
     *
     * @param  string       $alternative
     * @return string|false
     * @see \Fixhub\Models\Project::accessDetails()
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
     * @return Deployment
     */
    public function deployments()
    {
        return $this->hasMany(Deployment::class)
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
     * @see PFixhub\Models\Project::tags()
     * @see PFixhub\Models\Project::branches()
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
