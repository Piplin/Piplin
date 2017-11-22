<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Models\Traits;

use Piplin\Bus\Events\ModelChangedEvent;
use Piplin\Bus\Events\ModelCreatedEvent;
use Piplin\Bus\Events\ModelTrashedEvent;

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
            event(new ModelCreatedEvent($model, $channel));
        });

        static::updated(function ($model) {
            $channel = strtolower(class_basename(get_class($model)));
            event(new ModelChangedEvent($model, $channel));
        });

        static::deleted(function ($model) {
            $channel = strtolower(class_basename(get_class($model)));
            event(new ModelTrashedEvent($model, $channel));
        });
    }
}
