<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Http\Controllers\Dashboard;

use Piplin\Http\Controllers\Controller;
use Piplin\Http\Requests\StoreEnvironmentCabinetRequest;
use Piplin\Models\Cabinet;
use Piplin\Models\Environment;

/**
 * Environment cabinets management controller.
 */
class CabinetController extends Controller
{
    /**
     * Store a newly created notification in storage.
     *
     * @param Environment                    $environment
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
     * Remove the specified cabinet from environment.
     *
     * @param Environment $environment
     * @param Cabinet     $cabinet
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
