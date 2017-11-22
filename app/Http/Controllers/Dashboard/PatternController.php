<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Http\Controllers\Dashboard;

use Piplin\Http\Controllers\Controller;
use Piplin\Http\Requests\StorePatternRequest;
use Piplin\Models\Pattern;
use Piplin\Models\Plan;

/**
 * Controller for patterns.
 */
class PatternController extends Controller
{
    /**
     * Store a newly created file in storage.
     *
     * @param  StoreSharedFileRequest $request
     * @return Response
     */
    public function store(StorePatternRequest $request)
    {
        $fields = $request->only(
            'name',
            'copy_pattern',
            'plan_id'
        );

        $plan = Plan::findOrFail($fields['plan_id']);

        $this->authorize('manage', $plan->project);

        $pattern = Pattern::create($fields);

        return $pattern;
    }

    /**
     * Update the specified file in storage.
     *
     * @param Pattern             $pattern
     * @param StorePatternRequest $request
     *
     * @return Response
     */
    public function update(Pattern $pattern, StorePatternRequest $request)
    {
        $pattern->update($request->only(
            'name',
            'copy_pattern'
        ));

        return $pattern;
    }

    /**
     * Remove the specified file from storage.
     *
     * @param Pattern $pattern
     *
     * @return Response
     */
    public function destroy(Pattern $pattern)
    {
        $pattern->forceDelete();

        return [
            'success' => true,
        ];
    }
}
