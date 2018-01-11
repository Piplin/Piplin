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

use Piplin\Models\ConfigFile;

/**
 * Event observer for ConfigFile model.
 */
class ConfigFileObserver
{

    /**
     * Called when the model is updated.
     *
     * @param ConfigFile $configFile
     */
    public function updated(ConfigFile $configFile)
    {
        $isChanged = $configFile->isDirty('content') || $configFile->isDirty('path');

        if ($isChanged) {
            $configFile->status = ConfigFile::UNSYNCED;
            $configFile->save();
        }
    }

    /**
     * Called when the model is deleting.
     *
     * @param ConfigFile $configFile
     */
    public function deleting(ConfigFile $configFile)
    {
        $configFile->environments()->detach();
    }
}
