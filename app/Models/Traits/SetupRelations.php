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

use Fixhub\Models\Command;
use Fixhub\Models\Variable;
use Fixhub\Models\SharedFile;
use Fixhub\Models\ConfigFile;

/**
 * A trait to setup relation for project and template models.
 */
trait SetupRelations
{
    /**
     * Has many relationship.
     *
     * @return Command
     */
    public function commands()
    {
        return $this->morphMany(Command::class, 'targetable');
    }

    /**
     * Has many relationship to config file.
     *
     * @return ConfigFile
     */
    public function configFiles()
    {
        return $this->morphMany(ConfigFile::class, 'targetable');
    }

    /**
     * Has many relationship.
     *
     * @return SharedFile
     */
    public function sharedFiles()
    {
        return $this->morphMany(SharedFile::class, 'targetable');
    }

    /**
     * Has many relationship.
     *
     * @return Variable
     */
    public function variables()
    {
        return $this->morphMany(Variable::class, 'targetable');
    }
}
