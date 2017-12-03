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

    'name'              => 'App name',
    'about'             => 'App about',
    'signout'           => 'Sign Out',
    'dashboard'         => 'Dashboard',
    'admin'             => 'Administration',
    'projects'          => 'Projects',
    'groups'            => 'Groups',
    'keys'              => 'SSH Key',
    'users'             => 'Users',
    'tips'              => 'Tips',
    'links'             => 'Usefull links',
    'created'           => 'Created',
    'edit'              => 'Edit',
    'confirm'           => 'Confirm',
    'confirm_title'     => 'Confirm your action',
    'confirm_text'      => 'Are you sure you want to do this?',
    'not_applicable'    => 'N/A',
    'date'              => 'Date',
    'status'            => 'Status',
    'details'           => 'Details',
    'delete'            => 'Delete',
    'save'              => 'Save',
    'close'             => 'Close',
    'cancel'            => 'Cancel',
    'copied'            => 'Copied',
    'never'             => 'Never',
    'none'              => 'None',
    'yes'               => 'Yes',
    'no'                => 'No',
    'actions'           => 'Actions',
    'warning'           => 'WARNING',
    'awesome'           => 'Awesome.',
    'whoops'            => 'Whoops.',
    'socket_error'      => 'Server error',
    'socket_error_info' => 'A connection could not be established to the socket at <strong>' . config('piplin.socket_url') . '</strong>. This is required ' .
                           'for reporting the status on running deployments. Please reload, if the issue continues please contact the system administrator',
    'not_down'          => 'You must switch to maintenance mode before running this command, this will ensure that no new deployments are started',
    'switch_down'       => 'Switch to maintenance mode now? The app will switch back to live mode once cleanup is finished',
    'update_available'  => 'An update is available!',
    'outdated'          => 'You are running an out of date release :current, there is an updated release <a href=":link" rel="noreferrer">:latest</a> available!',

];
