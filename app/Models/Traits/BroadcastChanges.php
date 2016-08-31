<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Models\Traits;

use Fixhub\Bus\Events\ModelChanged;
use Fixhub\Bus\Events\ModelCreated;
use Fixhub\Bus\Events\ModelTrashed;

/**
 * A trait to broadcast model changes.
 */
trait BroadcastChanges
{
    /**
     * Override the boot method to bind model event listeners.
     *
     * @return void
     */
    public static function bootBroadcastChanges()
    {
        static::created(function ($model) {
            $channel = strtolower(class_basename(get_class($model)));
            event(new ModelCreated($model, $channel));
        });

        static::updated(function ($model) {
            $channel = strtolower(class_basename(get_class($model)));
            event(new ModelChanged($model, $channel));
        });

        static::deleted(function ($model) {
            $channel = strtolower(class_basename(get_class($model)));
            event(new ModelTrashed($model, $channel));
        });
    }
}
