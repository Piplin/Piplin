<?php

namespace Fixhub\Models;

use Fixhub\Models\Traits\BroadcastChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Fixhub\Services\Scripts\Runner as Process;

/**
 * SSH keys model.
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
     * Override the boot method to bind model event listeners.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        // When  creating the model generate an SSH Key pair and a webhook hash
        static::saving(function (Key $model) {
            if (!array_key_exists('private_key', $model->attributes) || $model->private_key === '') {
                $model->generateSSHKey();
            }

            if (!array_key_exists('public_key', $model->attributes) || $model->public_key === '') {
                $model->regeneratePublicKey();
            }
        });
    }

    /**
     * Generates an SSH key and sets the private/public key properties.
     *
     * @return void
     */
    protected function generateSSHKey()
    {
        $key = tempnam(storage_path('app/'), 'sshkey');
        unlink($key);

        $process = new Process('tools.GenerateSSHKey', [
            'key_file' => $key,
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        $this->attributes['private_key'] = file_get_contents($key);
        $this->attributes['public_key']  = file_get_contents($key . '.pub');

        unlink($key);
        unlink($key . '.pub');
    }

    /**
     * Generates an SSH key and sets the private/public key properties.
     *
     * @return void
     */
    protected function regeneratePublicKey()
    {
        $key = tempnam(storage_path('app/'), 'sshkey');
        file_put_contents($key, $this->private_key);

        $process = new Process('tools.RegeneratePublicSSHKey', [
            'key_file' => $key,
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        $this->attributes['public_key']  = file_get_contents($key . '.pub');

        unlink($key);
        unlink($key . '.pub');
    }

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
        $key    = preg_replace('/^(ssh-[dr]s[as]\s+)|(\s+.+)|\n/', '', trim($this->private_key));
        $buffer = base64_decode($key);
        $hash   = md5($buffer);
        return preg_replace('/(.{2})(?=.)/', '$1:', $hash);
    }
}
