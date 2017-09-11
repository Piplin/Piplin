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
use Fixhub\Http\Requests\StoreTipRequest;
use Fixhub\Models\Tip;
use Illuminate\Http\Request;

/**
 * tip management controller.
 */
class TipController extends Controller
{
    /**
     * Shows the create tip view.
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
     * tip listing.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $tips = Tip::paginate(config('fixhub.items_per_page', 10));

        return view('admin.tips.index', [
            'title'     => trans('tips.manage'),
            'tips_raw' =>$tips,
            'tips'     => $tips->toJson(), // Because PresentableInterface toJson() is not working in the view
        ]);
    }

    /**
     * Store a newly created tip in storage.
     *
     * @param  StoretipRequest $request
     *
     * @return Response
     */
    public function store(StoretipRequest $request)
    {
        return Tip::create($request->only(
            'body',
            'status'
        ));
    }

    /**
     * Store a newly created tip in storage.
     *
     * @param int $tip_id
     * @param StoretipRequest $request
     *
     * @return Response
     */
    public function update($tip_id, StoretipRequest $request)
    {
        $tip = Tip::findOrFail($tip_id);

        $tip->update($request->only(
            'body',
            'status'
        ));

        return $tip;
    }

    /**
     * Remove the specified tip from storage.
     *
     * @param int $tip_id
     *
     * @return Response
     */
    public function destroy($tip_id)
    {
        $tip = Tip::findOrFail($tip_id);

        $tip->delete();

        return [
            'success' => true,
        ];
    }
}
