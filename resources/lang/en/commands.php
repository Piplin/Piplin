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

    'label'                => 'Commands',
    'deploy_webhook'       => 'Incoming Webhooks are a simple way to trigger deployments from external sources. ',
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
    'step'                 => 'Step',
    'current'              => 'Current',
    'before'               => 'Before',
    'name'                 => 'Name',
    'run_as'               => 'Run as',
    'migrations'           => 'Migrations',
    'bash'                 => 'Bash Script',
    'environments'         => 'Environments',
    'servers'              => 'Servers',
    'default'              => 'Server default',
    'options'              => 'You can use the following tokens in your script (click the view)',
    'release_id'           => 'The release ID',
    'release_path'         => 'The full release path',
    'branch'               => 'The branch being deployed',
    'project_path'         => 'The project path',
    'after'                => 'After',
    'configure'            => 'Configure',
    'clone'                => 'Create New Release',
    'install'              => 'Install Dependencies',
    'activate'             => 'Activate New Release',
    'purge'                => 'Purge Old Releases',
    'warning'              => 'The command could not be saved, please check the form below.',
    'create'               => 'Add a new command',
    'create_success'       => 'Command created.',
    'edit'                 => 'Edit a command',
    'edit_success'         => 'Command updated.',
    'delete_success'       => 'The command has been deleted.',
    'sha'                  => 'The commit SHA hash',
    'short_sha'            => 'The short commit SHA hash',
    'deployer_name'        => 'The name of the user who triggered the deploy ',
    'deployer_email'       => 'The email address of the user who triggered the deploy',
    'committer_name'       => 'The name of the person who made the last commit',
    'committer_email'      => 'The email address of the person who made the last commit',
    'none'                 => 'No commands have been configured',
    'optional'             => 'Optional',
    'example'              => 'e.g.',
    'optional_description' => 'Ask at deploy time whether or not the include this step',
    'default_description'  => 'Include this step unless explicitly disabled (e.g. checkbox on by default)',
    'services'             => 'Supported services',
    'services_description' => 'You can use the webhook with these services and the relevant details will be gathered from the data ' .
                              'they send across. The &quot;<em>update_only</em>&quot; and &quot;<em>commands</em>&quot; parameters ' .
                              'may be included in the query string, all other fields are ignored.',
    'help'                 => 'Commands allow you to perform extra tasks such as database migrations, dependency installation, unit tests, and more.',

];
