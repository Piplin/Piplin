<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Bus\Observers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Piplin\Bus\Jobs\GenerateKeyJob;
use Piplin\Models\Key;

/**
 * Event observer for Key model.
 */
class KeyObserver
{
    use DispatchesJobs;

    /**
     * Called when the model is updated.
     *
     * @param Key $key
     */
    public function saving(Key $key)
    {
        if (empty($key->private_key) || empty($key->public_key)) {
            $this->dispatch(new GenerateKeyJob($key));
        }
    }
}
