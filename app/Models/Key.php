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
use Piplin\Models\Traits\BroadcastChanges;
use Piplin\Services\Scripts\Runner as Process;

/**
 * SSH keys model.
 *
 * @property int $id
 * @property string $name
 * @property int $order
 * @property string $private_key
 * @property string $public_key
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $fingerprint
 * @property-read \Illuminate\Database\Eloquent\Collection|\Piplin\Models\Project[] $projects
 * @property-read int|null $projects_count
 * @method static \Illuminate\Database\Eloquent\Builder|Key newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Key newQuery()
 * @method static \Illuminate\Database\Query\Builder|Key onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Key query()
 * @method static \Illuminate\Database\Eloquent\Builder|Key whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Key whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Key whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Key whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Key whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Key wherePrivateKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Key wherePublicKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Key whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Key withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Key withoutTrashed()
 * @mixin \Eloquent
 */
class Key extends Model
{
    use SoftDeletes, BroadcastChanges;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'private_key', 'public_key'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at', 'deleted_at', 'private_key'];

    /**
     * Additional attributes to include in the JSON representation.
     *
     * @var array
     */
    protected $appends = ['fingerprint'];

    /**
     * Has many relationship.
     *
     * @return Project
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Generate the fingerprint for the SSH key.
     *
     * @return string
     * @see https://james-brooks.uk/fingerprint-an-ssh-key-using-php/
     */
    public function getFingerprintAttribute()
    {
        $key    = preg_replace('/^(ssh-[dr]s[as]\s+)|(\s+.+)|\n/', '', trim($this->public_key));
        $buffer = base64_decode($key, true);
        $hash   = md5($buffer);

        return preg_replace('/(.{2})(?=.)/', '$1:', $hash);
    }
}
