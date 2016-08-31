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

use Fixhub\Models\Deployment;
use Fixhub\Presenters\Traits\RuntimePresenter;
use McCool\LaravelAutoPresenter\BasePresenter;

/**
 * The view presenter for a server class.
 */
class ServerLogPresenter extends BasePresenter
{
    use RuntimePresenter;
}
