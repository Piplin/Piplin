
<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    'label'                 => 'Deployments',
    'latest'                => 'Latest Deployments',
    'none'                  => 'There have not been any deployments yet.',
    'started_by'            => 'Started by',
    'deployer'              => 'Deployer',
    'committer'             => 'Committer',
    'commit'                => 'Commit',
    'manually'              => 'Manually',
    'webhook'               => 'Webhook',
    'rollback'              => 'Rollback',
    'rollback_title'        => 'Rollback to a previous deployment',
    'expert'                => 'You should not rollback to previous deployments unless you a confident you can perform any additional ' .
                               'cleanup which may be required.',
    'rollback_warning'      => 'When rolling back to a previous deployment you may need to manually connect to your servers and run ' .
                               'any database rollbacks or similar, this can not be done automatically.',
    'rollback_reason'       => ':reason (rollback to :id -> :commit)',
    'caution'               => 'Caution!',
    'cancel'                => 'Cancel',
    'loading'               => 'Loading',
    'unknown'               => 'Unknown',
    'process'               => 'Process Output',
    'server'                => 'Server',
    'status'                => 'Status',
    'started'               => 'Started',
    'finished'              => 'Finished',
    'duration'              => 'Duration',
    'output'                => 'View the output',
    'deployment_number'     => 'Deployment #:id',
    'pending'               => 'Pending',
    'deploying'             => 'Deploying',
    'completed'             => 'Completed',
    'completed_with_errors' => 'Completed with errors',
    'aborted'               => 'Aborted',
    'aborting'              => 'Aborting',
    'failed'                => 'Failed',
    'running'               => 'Running',
    'cancelled'             => 'Cancelled',
    'reason'                => 'Reason for deployment',
    'source'                => 'Source',
    'default'               => 'The default branch (:branch)',
    'different_branch'      => 'A different branch',
    'tag'                   => 'A tag',
    'branch'                => 'Branch',
    'warning'               => 'The deployment could not be started, please make sure you have entered all required values.',
    'describe_reason'       => 'Please describe briefly the reason for this deployment',
    'optional'              => 'Select the optional deploy steps to run',
    'week'                  => ':time week|:time weeks',
    'day'                   => ':time day|:time days',
    'hour'                  => ':time hour|:time hours',
    'minute'                => ':time minute|:time minutes',
    'second'                => ':time second|:time seconds',
    'repo_failure'          => 'There was an error retrieving the repository information, please check that the URL is correct and that the SSH key has been added',
    'repo_failure_head'     => 'Problem with the repository',

];
