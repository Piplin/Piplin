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

use Illuminate\Http\Request;
use Piplin\Http\Controllers\Controller;
use Piplin\Http\Requests\StoreKeyRequest;
use Piplin\Models\Key;

/**
 * SSH key management controller.
 */
class KeyController extends Controller
{
    /**
     * Shows the create project view.
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
     * Key listing.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $keys = Key::orderBy('name')
                    ->paginate(config('piplin.items_per_page', 10));

        return view('admin.keys.index', [
            'title'    => trans('keys.manage'),
            'keys_raw' => $keys,
            'keys'     => $keys->toJson(), // Because PresentableInterface toJson() is not working in the view
            'current_child' => 'keys',
        ]);
    }

    /**
     * Store a newly created ssh key in storage.
     *
     * @param StoreKeyRequest $request
     *
     * @return Response
     */
    public function store(StoreKeyRequest $request)
    {
        return Key::create($request->only(
            'name',
            'private_key'
        ));
    }

    /**
     * Store a newly created ssh key in storage.
     *
     * @param int             $key_id
     * @param StoreKeyRequest $request
     *
     * @return Response
     */
    public function update($key_id, StoreKeyRequest $request)
    {
        $key = Key::findOrFail($key_id);

        $key->update($request->only(
            'name',
            'private_key'
        ));

        return $key;
    }

    /**
     * Re-generates the order for the supplied ssh keys.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function reorder(Request $request)
    {
        $order = 0;

        foreach ($request->get('keys') as $key_id) {
            $key = Key::findOrFail($key_id);
            $key->update([
                'order' => $order,
            ]);

            $order++;
        }

        return [
            'success' => true,
        ];
    }

    /**
     * Remove the specified ssh key from storage.
     *
     * @param int $key_id
     *
     * @return Response
     */
    public function destroy($key_id)
    {
        $key = Key::findOrFail($key_id);

        $key->delete();

        return [
            'success' => true,
        ];
    }
}
