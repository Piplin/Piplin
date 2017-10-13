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
use Fixhub\Http\Requests\StoreSharedFileRequest;
use Fixhub\Models\SharedFile;

/**
 * Controller for managing files.
 */
class SharedFilesController extends Controller
{
    /**
     * Store a newly created file in storage.
     *
     * @param  StoreSharedFileRequest $request
     * @return Response
     */
    public function store(StoreSharedFileRequest $request)
    {
        $fields = $request->only(
            'name',
            'file',
            'targetable_type',
            'targetable_id'
        );

        $targetable_type = array_pull($fields, 'targetable_type');
        $targetable_id = array_pull($fields, 'targetable_id');

        $target = $targetable_type::findOrFail($targetable_id);

        return $target->sharedFiles()->create($fields);
    }

    /**
     * Update the specified file in storage.
     *
     * @param  int                    $file_id
     * @param  StoreSharedFileRequest $request
     * @return Response
     */
    public function update($file_id, StoreSharedFileRequest $request)
    {
        $shared_file = SharedFile::findOrFail($file_id);

        $shared_file->update($request->only(
            'name',
            'file'
        ));

        return $shared_file;
    }

    /**
     * Remove the specified file from storage.
     *
     * @param  int      $file_id
     * @return Response
     */
    public function destroy($file_id)
    {
        $shared_file = SharedFile::findOrFail($file_id);

        $shared_file->delete();

        return [
            'success' => true,
        ];
    }
}
