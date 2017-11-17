<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Fixhub\Http\Controllers\Controller;
use Fixhub\Models\Plan;
use Fixhub\Models\Project;

/**
 * The controller of plans.
 */
class PlanController extends Controller
{
    /**
     * The details of an individual plan.
     *
     * @param Plan   $plan
     * @param string $tab
     *
     * @return View
     */
    public function show(Plan $plan, $tab = '')
    {
        $data = [
            'plan'     => $plan,
            'project'  => $plan->project,
            'branches' => [],
            'tags'     => [],
            'optional' => [],
        ];

        return view('dashboard.plans.show', $data);
    }
}
