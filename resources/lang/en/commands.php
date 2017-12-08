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

    'label'                => 'Commands',
    'build_label'          => 'Build commands',
    'deploy_label'         => 'Deploy commands',
    'deploy_webhook'       => 'Incoming Webhooks are a simple way to trigger deployments from external sources. ',
    'build_webhook'        => 'Incoming Webhooks are a simple way to trigger builds from external sources. ',
    'webhook_help'         => 'Incoming Webhook Help',
    'webhook_example'      => 'By making an HTTP POST request to this URL you will trigger a new deployment of the latest commit.',
    'webhook_fields'       => 'Optional POST fields',
    'webhook_reason'       => 'The reason the deployment is being run',
    'webhook_source'       => 'A name to use to indicate who/what triggered the deployment, for instance "CI server"',
    'webhook_branch'       => 'The branch to deploy, if blank it defaults to the branch configured in the project',
    'webhook_update'       => 'Whether a deployment should only run if the currently deployed branch matches to branch to be deployed' .
                              ' , defaults to false',
    'webhook_url'          => 'A URL to link the name to, for example the build details on your CI server',
    'webhook_commands'     => 'A comma seperated list of optional command IDs to run',
    'webhook_optional'     => 'Optional Command IDs',
    'webhook_curl'         => 'Example cURL command',
    'reason_example'       => 'Testing the deployment webhook',
    'generate_webhook'     => 'Generate a new webhook URL (old URL will stop working)',
    'stage'                => 'Stage',
    'action'               => 'Action',
    'before'               => 'Before',
    'name'                 => 'Name',
    'run_as'               => 'Run as',
    'migrations'           => 'Migrations',
    'bash'                 => 'Script',
    'environments'         => 'Environments',
    'patterns'             => 'Copy patterns',
    'servers'              => 'Servers',
    'default'              => 'Server default',
    'options'              => 'You can use the following tokens in your script (click the view)',
    'release_id'           => 'The release ID',
    'release_path'         => 'The full release path',
    'branch'               => 'The branch being deployed',
    'project_path'         => 'The project path',
    'environment'          => 'The environment name',
    'build_release'        => 'The build release name',
    'after'                => 'After',
    'configure'            => 'Configure',
    'clone'                => 'Clone New Release',
    'clone_help'           => 'Create a new release directory and extract the archive.',
    'install'              => 'Install Dependencies',
    'install_help'         => 'Switch back to the new release directory.',
    'activate'             => 'Activate New Release',
    'activate_help'        => 'A <code>current</code> symlink will be created that links to the new release directory.',
    'purge'                => 'Purge Old Releases',
    'purge_help'           => 'Purge old releases.',
    'prepare'              => 'Prepare',
    'prepare_help'         => 'Prepare',
    'build'                => 'Build',
    'build_help'           => 'Build',
    'test'                 => 'Test',
    'test_help'            => 'Test',
    'result'               => 'Result',
    'result_help'          => 'Result',
    'warning'              => 'The command could not be saved, please check the form below.',
    'create'               => 'Add Command',
    'create_success'       => 'Command created.',
    'edit'                 => 'Edit a command',
    'edit_success'         => 'Command updated.',
    'delete_success'       => 'The command has been deleted.',
    'sha'                  => 'The commit SHA hash',
    'short_sha'            => 'The short commit SHA hash',
    'author_name'          => 'The name of the user who triggered the task ',
    'author_email'         => 'The email address of the user who triggered the task',
    'committer_name'       => 'The name of the person who made the last commit',
    'committer_email'      => 'The email address of the person who made the last commit',
    'none'                 => 'No commands have been configured',
    'optional'             => 'Optional',
    'example'              => 'e.g.',
    'optional_description' => 'Ask at deploy time whether or not the include this step',
    'default_description'  => 'Include this step unless explicitly disabled (e.g. checkbox on by default)',
    'services'             => 'Supported services : Github, Gitlab, Gogs, BitBucket, Gitee &amp; Coding',
    'services_description' => 'You can use the webhook with these services and the relevant details will be gathered from the data ' .
                              'they send across. The &quot;<em>update_only</em>&quot; and &quot;<em>commands</em>&quot; parameters ' .
                              'may be included in the query string, all other fields are ignored.',
    'help'                 => 'Commands allow you to perform extra tasks such as database migrations, dependency installation, unit tests, and more.',

];
