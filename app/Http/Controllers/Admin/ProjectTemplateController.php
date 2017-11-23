<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Http\Controllers\Admin;

use Piplin\Http\Controllers\Controller;
use Piplin\Http\Requests\StoreProjectTemplateRequest;
use Piplin\Models\ProjectTemplate;

/**
 * Controller for managing project template.
 */
class ProjectTemplateController extends Controller
{
    /**
     * Shows all templates.
     *
     * @return Response
     */
    public function index()
    {
        $templates = ProjectTemplate::orderBy('name')
                    ->paginate(config('piplin.items_per_page', 10));

        return view('admin.templates.index', [
            'title'         => trans('templates.manage'),
            'templates_raw' => $templates,
            'templates'     => $templates->toJson(), //toJson() is not working in the view
            'current_child' => 'templates',
        ]);
    }

    /**
     * Show the template configuration.
     *
     * @param  ProjectTemplate $template
     * @param  string         $tab
     * @return Response
     */
    public function show(ProjectTemplate $template, $tab = '')
    {
        return view('admin.templates.show', [
            'breadcrumb' => [
                ['url' => route('admin.templates.index'), 'label' => trans('templates.label')],
            ],
            'title'           => $template->name,
            'sharedFiles'     => $template->sharedFiles,
            'configFiles'     => $template->configFiles,
            'variables'       => $template->variables,
            'environments'    => $template->environments,
            'targetable_type' => 'Piplin\\Models\\ProjectTemplate',
            'targetable_id'   => $template->id,
            'targetable'      => $template,
            'route'           => 'admin.templates.commands.step',
            'tab'             => $tab,
        ]);
    }

    /**
     * Store a newly created template in storage.
     *
     * @param  StoreProjectTemplateRequest $request
     * @return Response
     */
    public function store(StoreProjectTemplateRequest $request)
    {
        return ProjectTemplate::create($request->only(
            'name'
        ));
    }

    /**
     * Update the specified template in storage.
     *
     * @param  ProjectTemplate             $template
     * @param  StoreProjectTemplateRequest $request
     * @return Response
     */
    public function update(ProjectTemplate $template, StoreProjectTemplateRequest $request)
    {
        $template->update($request->only(
            'name'
        ));

        return $template;
    }

    /**
     * Remove the specified template from storage.
     *
     * @param  ProjectTemplate $template
     * @return Response
     */
    public function destroy(ProjectTemplate $template)
    {
        $template->forceDelete();

        return [
            'success' => true,
        ];
    }
}
