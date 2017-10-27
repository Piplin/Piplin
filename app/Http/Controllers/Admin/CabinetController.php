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
use Fixhub\Http\Requests\StoreCabinetRequest;
use Fixhub\Models\DeployTemplate;
use Fixhub\Models\Key;
use Fixhub\Models\Project;
use Fixhub\Models\Cabinet;
use Illuminate\Http\Request;

/**
 * Cabinet management controller.
 */
class CabinetController extends Controller
{
    /**
     * Display a listing of the cabinets.
     *
     * @return Response
     */
    public function index()
    {
        $cabinets = Cabinet::orderBy('order')
                    ->paginate(config('fixhub.items_per_page', 10));
        $keys = Key::orderBy('name')
            ->get();

        return view('admin.cabinets.index', [
            'title'    => trans('cabinets.manage'),
            'cabinets' => $cabinets,
            'keys'     => $keys,
        ]);
    }

    public function show(Cabinet $cabinet)
    {
        $cabinets = Cabinet::all();

        return view('admin.cabinets.show', [
            'title'           => trans('cabinets.manage'),
            'servers_raw'     => $cabinet->servers,
            'targetable_type' => 'Fixhub\\Models\\Cabinet',
            'targetable_id'   => $cabinet->id,
            'targetable'      => $cabinet,
            'environments'    => $cabinets,
            'servers'         => $cabinet->servers->toJson()
        ]);
    }

    /**
     * Store a newly created cabinet in storage.
     *
     * @param  StoreCabinetRequest $request
     *
     * @return Response
     */
    public function store(StoreCabinetRequest $request)
    {
        return Cabinet::create($request->only(
            'name',
            'description',
            'key_id'
        ));
    }

    /**
     * Update the specified cabinet in storage.
     *
     * @param Cabinet $cabinet
     * @param StoreCabinetRequest $request
     *
     * @return Response
     */
    public function update(Cabinet $cabinet, StoreCabinetRequest $request)
    {
        $cabinet->update($request->only(
            'name',
            'description',
            'key_id'
        ));

        return $cabinet;
    }

    /**
     * Re-generates the order for the supplied cabinets.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return Response
     */
    public function reorder(Request $request)
    {
        $order = 0;

        foreach ($request->get('cabinets') as $cabinet_id) {
            $cabinet = Cabinet::findOrFail($cabinet_id);
            $cabinet->update([
                'order' => $order,
            ]);

            $order++;
        }

        return [
            'success' => true,
        ];
    }

    /**
     * Remove the specified cabinet from storage.
     *
     * @param Cabinet $cabinet
     *
     * @return Response
     */
    public function destroy(Cabinet $cabinet)
    {
        $cabinet->forceDelete();

        return [
            'success' => true,
        ];
    }
}
