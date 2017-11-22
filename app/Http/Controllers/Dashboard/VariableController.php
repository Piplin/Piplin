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
use Piplin\Http\Requests\StoreVariableRequest;
use Piplin\Models\Variable;

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
        $targetable_id   = array_pull($fields, 'targetable_id');

        $target = $targetable_type::findOrFail($targetable_id);

        // In project
        if ($targetable_type === 'Piplin\\Models\Project') {
            $this->authorize('manage', $target);
        }

        return $target->variables()->create($fields);
    }

    /**
     * Update the specified variable in storage.
     *
     * @param Variable             $variable
     * @param StoreVariableRequest $request
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
