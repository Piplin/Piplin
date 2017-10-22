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
use Illuminate\Support\Str;
use DB;
use Fixhub\Http\Controllers\Controller;
use Fixhub\Http\Requests\StoreCommandRequest;
use Fixhub\Models\Command;
use Fixhub\Models\Project;
use Fixhub\Models\DeployTemplate;

/**
 * Controller for managing commands.
 */
class CommandController extends Controller
{
    /**
     * Display a listing of before/after commands for the supplied stage.
     *
     * @param  mixed $target
     * @param  string  $action Either clone, install, activate or purge
     * @return Response
     */
    public function index($target, $action)
    {
        $types = [
            'clone'    => Command::DO_CLONE,
            'install'  => Command::DO_INSTALL,
            'activate' => Command::DO_ACTIVATE,
            'purge'    => Command::DO_PURGE,
        ];

        if ($target instanceof DeployTemplate) {
            $targetable_type = 'Fixhub\\Models\\DeployTemplate';
            $breadcrumb = [
                ['url' => route('admin.templates.index'), 'label' => trans('templates.label')],
                ['url' => route('admin.templates.show', ['templates' => $target->id]), 'label' => $target->name],
            ];
        } else {
            $targetable_type = 'Fixhub\\Models\\Project';
            $breadcrumb = [
                ['url' => route('projects', ['id' => $target->id, 'tab' => 'commands']), 'label' => $target->name],
            ];
        }

        return view('dashboard.commands.index', [
            'breadcrumb'      => $breadcrumb,
            'title'           => trans('commands.' . strtolower($action)),
            'subtitle'        => $target->name,
            'project'         => $target,
            'targetable_type' => $targetable_type,
            'targetable_id'   => $target->id,
            'action'          => $types[$action],
            'commands'        => $this->getForDeployStep($target, $types[$action]),
        ]);
    }

    /**
     * Store a newly created command in storage.
     *
     * @param  StoreCommandRequest $request
     * @return Response
     */
    public function store(StoreCommandRequest $request)
    {
        $fields = $request->only(
            'name',
            'user',
            'targetable_type',
            'targetable_id',
            'script',
            'step',
            'optional',
            'default_on',
            'environments'
        );

        $targetable_type = array_pull($fields, 'targetable_type');
        $targetable_id = array_pull($fields, 'targetable_id');

        $target = $targetable_type::findOrFail($targetable_id);

        // Get the current highest command order
        $max = $target->commands()->where('step', $fields['step'])
                           ->orderBy('order', 'DESC')
                           ->first();

        $order = 0;
        if (isset($max)) {
            $order = $max->order + 1;
        }

        $fields['order'] = $order;

        $environments = null;
        if (isset($fields['environments'])) {
            $environments = $fields['environments'];
            unset($fields['environments']);
        }

        $command = $target->commands()->create($fields);
        //$command = Command::create($fields);

        if ($environments) {
            $command->environments()->sync($environments);
        }

        $command->environments; // Triggers the loading

        return $command;
    }

    /**
     * Update the specified command in storage.
     *
     * @param  int                 $command_id
     * @param  StoreCommandRequest $request
     * @return Response
     */
    public function update($command_id, StoreCommandRequest $request)
    {
        $fields = $request->only(
            'name',
            'user',
            'script',
            'optional',
            'default_on',
            'environments'
        );

        $command = Command::findOrFail($command_id);

        $environments = null;
        if (isset($fields['environments'])) {
            $environments = $fields['environments'];
            unset($fields['environments']);
        }

        $command->update($fields);

        if ($environments) {
            $command->environments()->sync($environments);
        }

        $command->environments; // Triggers the loading

        return $command;
    }

    /**
     * Re-generates the order for the supplied commands.
     *
     * @param  Request  $request
     * @return Response
     */
    public function reorder(Request $request)
    {
        $order = 0;

        foreach ($request->get('commands') as $command_id) {
            $command = Command::findOrFail($command_id);
            $command->update([
                'order' => $order,
            ]);

            $order++;
        }

        return [
            'success' => true,
        ];
    }

    /**
     * Remove the specified command from storage.
     *
     * @param  int      $command_id
     * @return Response
     */
    public function destroy($command_id)
    {
        $command = Command::findOrFail($command_id);

        $command->delete();

        return [
            'success' => true,
        ];
    }

    /**
     * Get's the commands in a specific step.
     *
     * @param  Project|DeployTemplate $target
     * @param  int              $step
     * @return Collection
     */
    protected function getForDeployStep($target, $step)
    {
        return $target->commands()
                ->with(['environments'])
                ->whereIn('step', [$step - 1, $step + 1])
                ->orderBy('order', 'asc')
                    ->get()->toJson(); // Because CommandPresenter toJson() is not working in the view
    }
}
