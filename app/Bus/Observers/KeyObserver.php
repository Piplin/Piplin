<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Bus\Observers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Fixhub\Bus\Jobs\GenerateKey;
use Fixhub\Models\Key;

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
            $this->dispatch(new GenerateKey($key));
        }
    }
}
