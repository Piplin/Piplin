<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Presenters\Traits;

use Fixhub\Presenters\RuntimeInterface;

/**
 * View presenter for calculating the runtime in a readable format.
 */
trait RuntimePresenter
{
    /**
     * Converts a number of seconds into a more human readable format.
     *
     * @param  int    $seconds The number of seconds
     * @return string
     */
    public function readable_runtime()
    {
        if (!$this->wrappedObject instanceof RuntimeInterface) {
            throw new \RuntimeException('Model must implement RuntimeInterface');
        }

        $seconds = $this->wrappedObject->runtime();

        $units = [
            'week'   => 7 * 24 * 3600,
            'day'    => 24 * 3600,
            'hour'   => 3600,
            'minute' => 60,
            'second' => 1,
        ];

        if ($seconds === 0) {
            return trans_choice('deployments.second', 0, ['time' => 0]);
        }

        $readable = '';
        foreach ($units as $name => $divisor) {
            if ($quot = intval($seconds / $divisor)) {
                $readable .= trans_choice('deployments.' . $name, $quot, ['time' => $quot]) . ', ';
                $seconds -= $quot * $divisor;
            }
        }

        return substr($readable, 0, -2);
    }
}
