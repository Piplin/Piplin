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
use Fixhub\Http\Requests\StoreTemplateRequest;
use Fixhub\Models\Template;

/**
 * Controller for managing deployment template.
 */
class TemplateController extends Controller
{
    /**
     * Shows all templates.
     *
     * @return Response
     */
    public function index()
    {
        $templates = Template::orderBy('name')
                    ->get();

        return view('admin.templates.index', [
            'title'     => trans('templates.manage'),
            'templates' => $templates->toJson(), // Because PresentableInterface toJson() is not working in the view
        ]);
    }

    /**
     * Show the template configuration.
     *
     * @param  int      $template_id
     * @return Response
     */
    public function show($template_id)
    {
        $template = Template::findOrFail($template_id);

        return view('admin.templates.show', [
            'breadcrumb' => [
                ['url' => route('admin.templates.index'), 'label' => trans('templates.label')],
            ],
            'title'           => $template->name,
            'sharedFiles'     => $template->sharedFiles,
            'configFiles'     => $template->configFiles,
            'variables'       => $template->variables,
            'targetable_type' => 'Fixhub\\Models\\Template',
            'targetable_id'   => $template->id,
            'project'         => $template,
            'route'           => 'admin.templates.commands.step',
        ]);
    }

    /**
     * Store a newly created template in storage.
     *
     * @param  StoreTemplateRequest $request
     * @return Response
     */
    public function store(StoreTemplateRequest $request)
    {
        return Template::create($request->only(
            'name'
        ));
    }

    /**
     * Update the specified template in storage.
     *
     * @param  int                  $template_id
     * @param  StoreTemplateRequest $request
     * @return Response
     */
    public function update($template_id, StoreTemplateRequest $request)
    {
        $template = Template::findOrFail($template_id);

        $template->update($request->only(
            'name'
        ));

        return $template;
    }

    /**
     * Remove the specified template from storage.
     *
     * @param  int      $template_id
     * @return Response
     */
    public function destroy($template_id)
    {
        $template = Template::findOrFail($template_id);

        $template->delete();

        return [
            'success' => true,
        ];
    }
}
