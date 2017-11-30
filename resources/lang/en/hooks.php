<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    'label'                            => 'Hooks',
    'create'                           => 'Add Hook',
    'create_success'                   => 'Hook created.',
    'create_slack'                     => 'Add Slack Hook',
    'create_dingtalk'                  => 'Add Dingtalk Hook',
    'create_mail'                      => 'Add E-mail Hook',
    'create_custom'                    => 'Add Custom Hook',
    'edit'                             => 'Edit the hook',
    'edit_success'                     => 'Hook updated.',
    'edit_slack'                       => 'Edit the Slack hook',
    'edit_dingtalk'                    => 'Edit the Dingtalk hook',
    'edit_mail'                        => 'Edit the e-mail hook',
    'edit_custom'                      => 'Edit the custom hook',
    'delete_success'                   => 'The hook has been deleted.',
    'none'                             => 'The project does not currently have any hooks setup',
    'integration_help'                 => 'Hooks can be used for binding events when something is happening within the project.',
    'name'                             => 'Name',
    'type'                             => 'Type',
    'warning'                          => 'The hook could not be saved, please check the form below.',
    'not_configured_title'             => 'Service not configured',
    'not_configured'                   => 'The selected hook type can not be used as it has not been configured.',
    'triggers'                         => 'Triggers',
    'webhook'                          => 'Webhook URL',
    'icon'                             => 'Icon',
    'bot'                              => 'Bot',
    'icon_info'                        => 'Either an emoji, for example :ghost: or the URL to an image',
    'channel'                          => 'Channel',
    'deployments'                      => 'Tasks',
    'succeeded'                        => 'Succeeded',
    'failed'                           => 'Failed',
    'on_deployment_success'            => 'Task Succeeded',
    'on_deployment_failure'            => 'Task Failed',
    'custom'                           => 'Custom',
    'slack'                            => 'Slack',
    'dingtalk'                         => 'Dingtalk',
    'mail'                             => 'E-mail',
    'which'                            => 'Which type of hook do you wish to add?',
    'test_subject'                     => 'Test Notification',
    'test_message'                     => 'This is a test to ensure the hook is setup correctly - From :app_url',
    'enabled'                          => 'Hook enabled?',

    // Slack
    'branch'                           => 'Branch',
    'project'                          => 'Project',
    'commit'                           => 'Commit',
    'committer'                        => 'Committer',
    'deployment_reason'                => 'Task reason - :reason',
    'deployment_success_slack_message' => ':white_check_mark: Task %s successful! :smile:',
    'deployment_failed_slack_message'  => ':x: Task %s failed! :cry:',

    // Dingtalk
    'deployment_success_ding_message' => 'Task %s successful!',
    'deployment_failed_ding_message'  => ':Task %s failed!',
    'at_mobiles'                      => 'At mobiles',
    'is_at_all'                       => 'Is at all',

    // Email
    'project_name'                     => 'Project name',
    'deployed_branch'                  => 'Deployed branch',
    'task_details'                     => 'View the task',
    'project_details'                  => 'View the project',
    'started_at'                       => 'Started at',
    'finished_at'                      => 'Finished at',
    'last_committer'                   => 'Last committer',
    'last_commit'                      => 'Last commit',
    'reason'                           => 'Task reason - :reason',
    'deployment_success_email_subject' => 'Task Finished',
    'deployment_success_email_message' => 'The task was successful',
    'deployment_failed_email_subject'  => 'Task Failed',
    'deployment_failed_email_message'  => 'The task has failed',
];
