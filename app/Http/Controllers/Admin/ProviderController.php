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
use Fixhub\Http\Requests\StoreProviderRequest;
use Fixhub\Models\Provider;
use Illuminate\Http\Request;

/**
 * Provider management controller.
 */
class ProviderController extends Controller
{
    /**
     * Shows the create provider view.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        return $this->index($request)->withAction('create');
    }

    /**
     * provider listing.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $providers = Provider::orderBy('order')
                    ->paginate(config('fixhub.items_per_page', 10));

        return view('admin.providers.index', [
            'title'         => trans('providers.manage'),
            'providers_raw' =>$providers,
            'providers'     => $providers->toJson(), // Because PresentableInterface toJson() is not working in the view
        ]);
    }

    /**
     * Store a newly created provider in storage.
     *
     * @param  StoreProviderRequest $request
     *
     * @return Response
     */
    public function store(StoreProviderRequest $request)
    {
        return Provider::create($request->only(
            'name',
            'slug',
            'icon',
            'description'
        ));
    }

    /**
     * Store a newly created provider in storage.
     *
     * @param int              $provider_id
     * @param StoreProviderRequest $request
     *
     * @return Response
     */
    public function update($provider_id, StoreProviderRequest $request)
    {
        $provider = Provider::findOrFail($provider_id);

        $provider->update($request->only(
            'name',
            'slug',
            'icon',
            'description'
        ));

        return $provider;
    }

    /**
     * Re-generates the order for the supplied providers.
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function reorder(Request $request)
    {
        $order = 0;

        foreach ($request->get('providers') as $provider_id) {
            $provider = Provider::findOrFail($provider_id);
            $provider->update([
                'order' => $order,
            ]);

            $order++;
        }

        return [
            'success' => true,
        ];
    }

    /**
     * Remove the specified provider from storage.
     *
     * @param int $provider_id
     *
     * @return Response
     */
    public function destroy($provider_id)
    {
        $provider = Provider::findOrFail($provider_id);

        $provider->delete();

        return [
            'success' => true,
        ];
    }
}
