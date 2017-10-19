<?php

namespace Fixhub\Models;

use Fixhub\Models\Traits\BroadcastChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Fixhub\Services\Scripts\Runner as Process;
use Venturecraft\Revisionable\RevisionableTrait;

/**
 * SSH keys model.
 */
class Key extends Model
{
    use SoftDeletes, BroadcastChanges, RevisionableTrait;

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
    protected $dontKeepRevisionOf = ['private_key'];

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
        $buffer = base64_decode($key);
        $hash   = md5($buffer);
        return preg_replace('/(.{2})(?=.)/', '$1:', $hash);
    }
}
