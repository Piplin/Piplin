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

    'manage'            => 'Manage projects',
    'warning'           => 'The project could not be saved, please check the form below.',
    'none'              => 'There are currently no projects setup',
    'name'              => 'Name',
    'name_placeholder'  => 'My Project',
    'group'             => 'Group',
    'ungrouped'         => 'Ungrouped',
    'key'               => 'SSH Key',
    'repository'        => 'Repository',
    'repository_path'   => 'Repository URL',
    'deploy_path'       => 'Project Path',
    'deploy_path_help'  => 'When you deploy, Piplin will create a <code>releases</code> directory within this path. A <code>current</code> symlink will also be created that links to each new release.</p><p>So, if your project path is <code>/data/www/app</code>, your web server\'s root probably should be set to <code>/data/www/app/current/public</code>.',
    'builds'            => 'Builds to Keep',
    'build_options'     => 'Build options',
    'clone'             => 'Clone',
    'clone_name'        => 'Name',
    'branch'            => 'Deploy Branch',
    'last_mirrored'     => 'Last mirrored',
    'image'             => 'Build Image',
    'ci_image'          => 'If you use a CI server which generates an image to indicate the build status ' .
                           'can put the URL here to have it show on the project page',
    'integrations'      => 'Integrations',
    'runned'            => 'Runned',
    'apply'             => 'Apply for a deployment',
    'apply_choose'      => 'Choose a project',
    'apply_intro'       => 'Please choose a project to apply for a deployment.',
    'create'            => 'Add Project',
    'create_success'    => 'Project added.',
    'edit'              => 'Update Poject',
    'edit_success'      => 'Project updated.',
    'delete'            => 'Delete project',
    'delete_success'    => 'The project has beean deleted!',
    'settings'          => 'Settings',
    'recover'           => 'Recover',
    'recover_text'      => 'Do you really want to recover the status of this project?',
    'recover_success'   => 'Project status recovered.',
    'url'               => 'URL',
    'details'           => 'Project Details',
    'tasks'             => 'Tasks',
    'today'             => 'Today',
    'last_week'         => 'Last Week',
    'total_count'       => 'Total',
    'status'            => 'Project Status',
    'build_status'      => 'Build Status',
    'deploy_project'    => 'Deploy the project',
    'deploy'            => 'Deploy',
    'deploy_plan'       => 'Deploy Plan',
    'build_plan'        => 'Build Plan',
    'rollback'          => 'Rollback',
    'finished'          => 'Finished',
    'pending'           => 'Pending',
    'running'           => 'Running',
    'failed'            => 'Failed',
    'initial'           => 'Initial',
    'options'           => 'Options',
    'change_branch'     => 'Allow other branches to be runned?',
    'insecure'          => 'Your Piplin installation is not running over a secure connection, it is recommended ' .
                           'that you let Piplin generate an SSH key rather than supply one yourself so that the ' .
                           'private key is not transmitted over an insecure connection.',
    'history'           => 'Recent history',

];
