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

use Fixhub\Models\Command;
use McCool\LaravelAutoPresenter\BasePresenter;

/**
 * The view presenter for a deploy step class.
 */
class DeployStepPresenter extends BasePresenter
{
    /**
     * Gets the deployment stage label from the numeric representation.
     *
     * @return string
     */
    public function name()
    {
        if (!is_null($this->wrappedObject->command_id)) {
            return $this->wrappedObject->command->name;
        } elseif ($this->wrappedObject->stage === Command::DO_INSTALL) {
            return trans('commands.install');
        } elseif ($this->wrappedObject->stage === Command::DO_ACTIVATE) {
            return trans('commands.activate');
        } elseif ($this->wrappedObject->stage === Command::DO_PURGE) {
            return trans('commands.purge');
        }

        return trans('commands.clone');
    }

    /**
     * Gets the deployment stage color.
     *
     * @return string
     */
    public function header_color()
    {
        $colors = [
            Command::DO_CLONE    => '#2979ac',
            Command::DO_INSTALL  => '#c5a113',
            Command::DO_ACTIVATE => '#64a81a',
            Command::DO_PURGE    => '#394BA0',
        ];

        foreach ($colors as $step => $color) {
            if ($this->wrappedObject->stage == $step) {
                return $color;
            } else if (abs($this->wrappedObject->stage - $step) == 1) {
                return color_darken($color, 0.5);
            }
        }

        return '';
    }
}
