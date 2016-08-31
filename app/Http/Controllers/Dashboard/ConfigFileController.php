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
use Fixhub\Http\Requests\StoreConfigFileRequest;
use Fixhub\Models\ConfigFile;

/**
 * Manage the config global file like some environment files.
 */
class ConfigFileController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(StoreConfigFileRequest $request)
    {
        $fields = $request->only(
            'name',
            'path',
            'content',
            'targetable_type',
            'targetable_id'
        );

        $targetable_type = array_pull($fields, 'targetable_type');
        $targetable_id = array_pull($fields, 'targetable_id');

        $target = $targetable_type::findOrFail($targetable_id);

        return $target->configFiles()->create($fields);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int      $file_id
     * @return Response
     */
    public function update($file_id, StoreConfigFileRequest $request)
    {
        $config_file = ConfigFile::findOrFail($file_id);

        $config_file->update($request->only(
            'name',
            'path',
            'content'
        ));

        return $config_file;
    }

    /**
     * Remove the specified file from storage.
     *
     * @param  int      $file_id
     * @return Response
     */
    public function destroy($file_id)
    {
        $config_file = ConfigFile::findOrFail($file_id);

        $config_file->delete();

        return [
            'success' => true,
        ];
    }
}
