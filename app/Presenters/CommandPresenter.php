<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Presenters;

use Illuminate\Support\Facades\Config;
use McCool\LaravelAutoPresenter\BasePresenter;
use Fixhub\Models\Command;

class CommandPresenter extends BasePresenter
{
    /**
     * Gets the readable list of before clone commands.
     *
     * @return string
     * @see self::commandNames()
     */
    public function before_clone()
    {
        return $this->commandNames(Command::BEFORE_CLONE);
    }

    /**
     * Gets the readable list of after clone commands.
     *
     * @return string
     * @see self::commandNames()
     */
    public function after_clone()
    {
        return $this->commandNames(Command::AFTER_CLONE);
    }

    /**
     * Gets the readable list of before install commands.
     *
     * @return string
     * @see self::commandNames()
     */
    public function before_install()
    {
        return $this->commandNames(Command::BEFORE_INSTALL);
    }

    /**
     * Gets the readable list of after install commands.
     *
     * @return string
     * @see self::commandNames()
     */
    public function after_install()
    {
        return $this->commandNames(Command::AFTER_INSTALL);
    }

    /**
     * Gets the readable list of before activate commands.
     *
     * @return string
     * @see self::commandNames()
     */
    public function before_activate()
    {
        return $this->commandNames(Command::BEFORE_ACTIVATE);
    }

    /**
     * Gets the readable list of after activate commands.
     *
     * @return string
     * @see self::commandNames()
     */
    public function after_activate()
    {
        return $this->commandNames(Command::AFTER_ACTIVATE);
    }

    /**
     * Gets the readable list of before purge commands.
     *
     * @return string
     * @see self::commandNames()
     */
    public function before_purge()
    {
        return $this->commandNames(Command::BEFORE_PURGE);
    }

    /**
     * Gets the readable list of after purge commands.
     *
     * @return string
     * @see self::commandNames()
     */
    public function after_purge()
    {
        return $this->commandNames(Command::AFTER_PURGE);
    }

    /**
     * Gets the readable list of commands.
     *
     * @param  int    $stage
     * @return string
     */
    private function commandNames($stage)
    {
        $commands = [];

        foreach ($this->wrappedObject->commands as $command) {
            if ($command->step === $stage) {
                $commands[] = $command->name;
            }
        }

        if (count($commands)) {
            return implode(', ', $commands);
        }

        return trans('app.none');
    }
}
