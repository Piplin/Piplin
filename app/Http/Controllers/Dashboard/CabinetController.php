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
use Fixhub\Http\Requests\StoreEnvironmentCabinetRequest;
use Illuminate\Http\Request;
use Fixhub\Models\Environment;
use Fixhub\Models\Cabinet;

/**
 * Environment cabinets management controller.
 */
class CabinetController extends Controller
{
    /**
     * Store a newly created notification in storage.
     *
     * @param Environment $environment
     * @param StoreEnvironmentCabinetRequest $request
     *
     * @return Response
     */
    public function store(Environment $environment, StoreEnvironmentCabinetRequest $request)
    {
        $cabinet_ids = $request->get('cabinet_ids');

        $cabinets = Cabinet::whereIn('id', $cabinet_ids)->get();

        $environment->cabinets()->attach($cabinet_ids);

        return $cabinets;
    }

    /**
     * Queues a connection test for the specified server.
     *
     * @param Environment $environment
     * @param Cabinet $cabinet
     *
     * @return Response
     */
    public function test(Environment $server, Cabinet $cabinet)
    {

    }

    /**
     * Remove the specified cabinet from environment.
     *
     * @param Environment $environment
     * @param Cabinet $cabinet
     *
     * @return Response
     */
    public function destroy(Environment $environment, Cabinet $cabinet)
    {
        $environment->cabinets()->detach($cabinet->id);

        return [
            'success' => true,
        ];
    }
}
