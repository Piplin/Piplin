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

        return $target->variables()->create($fields);
    }

    /**
     * Update the specified variable in storage.
     *
     * @param  int                  $variable_id
     * @param  StoreVariableRequest $request
     * @return Response
     */
    public function update($variable_id, StoreVariableRequest $request)
    {
        $variable = Variable::findOrFail($variable_id);

        $variable->update($request->only(
            'name',
            'value'
        ));

        return $variable;
    }

    /**
     * Remove the specified variable from storage.
     *
     * @param  int      $variable_id
     * @return Response
     */
    public function destroy($variable_id)
    {
        $variable = Variable::findOrFail($variable_id);

        $variable->delete();

        return [
            'success' => true,
        ];
    }
}
