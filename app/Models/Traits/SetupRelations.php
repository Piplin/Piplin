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

use Piplin\Models\Command;
use Piplin\Models\ConfigFile;
use Piplin\Models\Environment;
use Piplin\Models\SharedFile;
use Piplin\Models\Variable;

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
        return $this->morphMany(Command::class, 'targetable')->orderBy('order', 'ASC');
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

    /**
     * Has many relationship.
     *
     * @return Environment
     */
    public function environments()
    {
        return $this->morphMany(Environment::class, 'targetable')->orderBy('order', 'ASC');
    }
}
