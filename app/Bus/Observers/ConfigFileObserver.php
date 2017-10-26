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

use Fixhub\Models\ConfigFile;

/**
 * Event observer for ConfigFile model.
 */
class ConfigFileObserver
{
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
