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

/**
 * Identity model.
 *
 * @property int $id
 * @property string $extern_uid
 * @property int $provider_id
 * @property int $user_id
 * @property string|null $nickname
 * @property string|null $name
 * @property string|null $email
 * @property string|null $avatar
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Identity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Identity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Identity query()
 * @method static \Illuminate\Database\Eloquent\Builder|Identity whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Identity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Identity whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Identity whereExternUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Identity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Identity whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Identity whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Identity whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Identity whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Identity whereUserId($value)
 * @mixin \Eloquent
 */
class Identity extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'extern_uid',
        'name',
        'nickname',
        'email',
        'user_id',
        'provider_id',
    ];
}
