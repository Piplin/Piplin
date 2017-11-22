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
use Piplin\Http\Requests\StoreSharedFileRequest;
use Piplin\Models\SharedFile;

/**
 * Controller for managing files.
 */
class SharedFileController extends Controller
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
        $targetable_id   = array_pull($fields, 'targetable_id');

        $target = $targetable_type::findOrFail($targetable_id);

        // In project
        if ($targetable_type === 'Piplin\\Models\Project') {
            $this->authorize('manage', $target);
        }

        return $target->sharedFiles()->create($fields);
    }

    /**
     * Update the specified file in storage.
     *
     * @param SharedFile             $shared_file
     * @param StoreSharedFileRequest $request
     *
     * @return Response
     */
    public function update(SharedFile $shared_file, StoreSharedFileRequest $request)
    {
        $shared_file->update($request->only(
            'name',
            'file'
        ));

        return $shared_file;
    }

    /**
     * Remove the specified file from storage.
     *
     * @param SharedFile $shared_file
     *
     * @return Response
     */
    public function destroy(SharedFile $shared_file)
    {
        $shared_file->forceDelete();

        return [
            'success' => true,
        ];
    }
}
