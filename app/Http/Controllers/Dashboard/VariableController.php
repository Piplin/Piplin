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

use Fixhub\Http\Controllers\Controller;
use Fixhub\Http\Requests\StoreVariableRequest;
use Fixhub\Models\Variable;

/**
 * Variable management controller.
 */
class VariableController extends Controller
{
    /**
     * Store a newly created variable in storage.
     *
     * @param  StoreVariableRequest $request
     * @return Response
     */
    public function store(StoreVariableRequest $request)
    {
        $fields = $request->only(
            'name',
            'value',
            'targetable_type',
            'targetable_id'
        );

        $targetable_type = array_pull($fields, 'targetable_type');
        $targetable_id = array_pull($fields, 'targetable_id');

        $target = $targetable_type::findOrFail($targetable_id);

        // In project
        if ($targetable_type == 'Fixhub\\Models\Project') {
            $this->authorize('manage', $target);
        }

        return $target->variables()->create($fields);
    }

    /**
     * Update the specified variable in storage.
     *
     * @param  Variable $variable
     * @param  StoreVariableRequest $request
     *
     * @return Response
     */
    public function update(Variable $variable, StoreVariableRequest $request)
    {
        $variable->update($request->only(
            'name',
            'value'
        ));

        return $variable;
    }

    /**
     * Remove the specified variable from storage.
     *
     * @param  Variable $variable
     * @return Response
     */
    public function destroy(Variable $variable)
    {
        $variable->delete();

        return [
            'success' => true,
        ];
    }
}
