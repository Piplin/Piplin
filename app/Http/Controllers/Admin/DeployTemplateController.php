<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Http\Controllers\Admin;

use Fixhub\Http\Controllers\Controller;
use Fixhub\Http\Requests\StoreDeployTemplateRequest;
use Fixhub\Models\DeployTemplate;

/**
 * Controller for managing deployment template.
 */
class DeployTemplateController extends Controller
{
    /**
     * Shows all templates.
     *
     * @return Response
     */
    public function index()
    {
        $templates = DeployTemplate::orderBy('name')
                    ->paginate(config('fixhub.items_per_page', 10));

        return view('admin.templates.index', [
            'title'         => trans('templates.manage'),
            'templates_raw' => $templates,
            'templates'     => $templates->toJson(), //toJson() is not working in the view
        ]);
    }

    /**
     * Show the template configuration.
     *
     * @param DeployTemplate $template
     * @param string $tab
     * @return Response
     */
    public function show(DeployTemplate $template, $tab = '')
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
            'targetable_type' => 'Fixhub\\Models\\DeployTemplate',
            'targetable_id'   => $template->id,
            'project'         => $template,
            'route'           => 'admin.templates.commands.step',
            'tab'             => $tab,
        ]);
    }

    /**
     * Store a newly created template in storage.
     *
     * @param  StoreDeployTemplateRequest $request
     * @return Response
     */
    public function store(StoreDeployTemplateRequest $request)
    {
        return DeployTemplate::create($request->only(
            'name'
        ));
    }

    /**
     * Update the specified template in storage.
     *
     * @param  DeployTemplate $template
     * @param  StoreDeployTemplateRequest $request
     * @return Response
     */
    public function update(DeployTemplate $template, StoreDeployTemplateRequest $request)
    {
        $template->update($request->only(
            'name'
        ));

        return $template;
    }

    /**
     * Remove the specified template from storage.
     *
     * @param  DeployTemplate $template
     * @return Response
     */
    public function destroy(DeployTemplate $template)
    {
        $template->forceDelete();

        return [
            'success' => true,
        ];
    }
}
