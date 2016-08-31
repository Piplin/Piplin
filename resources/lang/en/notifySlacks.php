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

    'label'               => 'Slack Notifications',
    'create'              => 'Add a new slack notification',
    'edit'                => 'Edit the slack notification',
    'none'                => 'The project does not currently have any slack notifications setup',
    'name'                => 'Name',
    'channel'             => 'Channel',
    'warning'             => 'The notification could not be saved, please check the form below.',
    'icon'                => 'Icon',
    'bot'                 => 'Bot',
    'icon_info'           => 'Either an emoji, for example :ghost: or the URL to an image',
    'webhook'             => 'Webhook URL',
    'test_message'        => 'This is a test to ensure the notification is setup correctly, if you ' .
                             'can see this it means it is! :+1:',
    'success_message'     => 'Deployment %s successful! :smile:',
    'failed_message'      => 'Deployment %s failed! :cry:',
    'branch'              => 'Branch',
    'project'             => 'Project',
    'commit'              => 'Commit',
    'committer'           => 'Committer',
    'failure_only'        => 'Failure Only',
    'notify_failure_only' => 'Notify only on Failure',
    'failure_description' => 'Only notify this channel on failure',

];
