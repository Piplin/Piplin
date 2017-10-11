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
use Illuminate\Contracts\Routing\ResponseFactory;
use Fixhub\Http\Requests\StoreTriggerRequest;
use Symfony\Component\HttpFoundation\Response;
use Fixhub\Models\Trigger;

/**
 * Controller for managing triggers.
 */
class TriggerController extends Controller
{
    /**
     * Store a newly created notification in storage.
     *
     * @param StoreTriggerRequest $request
     *
     * @return Response
     */
    public function store(StoreTriggerRequest $request)
    {
        $input = $request->only(
            'name',
            'project_id',
            'type',
            'enabled'
        );

        $input['config'] = $request->configOnly();

        $trigger = Trigger::create($input);

        return $trigger;
    }

    /**
     * Update the specified notification in storage.
     *
     * @param int $trigger_id
     * @param StoreTriggerRequest $request
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update($trigger_id, StoreTriggerRequest $request)
    {
        $input = $request->only(
            'name',
            'enabled'
        );

        $input['config'] = $request->configOnly();

        $trigger = Trigger::findOrFail($trigger_id);

        $trigger->update($input);

        return $trigger;
    }

    /**
     * Remove the specified trigger from storage.
     *
     * @param int $trigger_id
     *
     * @return Response
     */
    public function destroy($trigger_id)
    {
        $trigger = Trigger::findOrFail($trigger_id);

        $trigger->delete();

        return [
            'success' => true,
        ];
    }
}
