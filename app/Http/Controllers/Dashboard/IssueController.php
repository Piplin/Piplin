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
use Fixhub\Http\Requests\StoreIssueRequest;
use Fixhub\Models\Issue;

/**
 * Controller for managing issues.
 */
class IssueController extends Controller
{
    /**
     * Store a newly created issue in storage.
     *
     * @param  StoreIssueRequest $request
     * @return Response
     */
    public function store(StoreIssueRequest $request)
    {
        $fields = $request->only(
            'title',
            'content',
            'project_id'
        );

        $issue = Issue::create($fields);

        return $issue;
    }

    /**
     * Update the specified issue in storage.
     *
     * @param  StoreIssueRequest $request
     * @return Response
     */
    public function update($issue_id, StoreIssueRequest $request)
    {
        $issue = Issue::findOrFail($issue_id);

        $issue->update($request->only(
            'title',
            'content',
            'project_id'
        ));

        return $issue;
    }

    /**
     * Remove the specified issue from storage.
     *
     * @param  int      $issue_id
     * @return Response
     */
    public function destroy($issue_id)
    {
        $issue = Issue::findOrFail($issue_id);

        $issue->delete();

        return [
            'success' => true,
        ];
    }
}
