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
use Fixhub\Http\Requests\StoreLinkRequest;
use Fixhub\Models\Link;
use Illuminate\Http\Request;

/**
 * link management controller.
 */
class LinkController extends Controller
{
    /**
     * Shows the create link view.
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
     * link listing.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $links = Link::orderBy('order')
                    ->paginate(config('fixhub.items_per_page', 10));

        return view('admin.links.index', [
            'title'     => trans('links.manage'),
            'links_raw' =>$links,
            'links'     => $links->toJson(), // Because PresentableInterface toJson() is not working in the view
        ]);
    }

    /**
     * Store a newly created link in storage.
     *
     * @param  StoreLinkRequest $request
     *
     * @return Response
     */
    public function store(StoreLinkRequest $request)
    {
        return Link::create($request->only(
            'title',
            'url',
            'description'
        ));
    }

    /**
     * Store a newly created link in storage.
     *
     * @param Link             $link
     * @param StoreLinkRequest $request
     *
     * @return Response
     */
    public function update(Link $link, StoreLinkRequest $request)
    {
        $link->update($request->only(
            'title',
            'url',
            'description'
        ));

        return $link;
    }

    /**
     * Re-generates the order for the supplied links.
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function reorder(Request $request)
    {
        $order = 0;

        foreach ($request->get('links') as $link_id) {
            $link = Link::findOrFail($link_id);
            $link->update([
                'order' => $order,
            ]);

            $order++;
        }

        return [
            'success' => true,
        ];
    }

    /**
     * Remove the specified link from storage.
     *
     * @param Link $link
     *
     * @return Response
     */
    public function destroy(Link $link)
    {
        $link->delete();

        return [
            'success' => true,
        ];
    }
}
