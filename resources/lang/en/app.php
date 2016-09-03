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

    'name'              => 'App name',
    'about'             => 'App about',
    'signout'           => 'Sign Out',
    'dashboard'         => 'Dashboard',
    'admin'             => 'Administration',
    'projects'          => 'Projects',
    'templates'         => 'Templates',
    'groups'            => 'Groups',
    'users'             => 'Users',
    'tips'              => 'Tips',
    'links'             => 'Usefull links',
    'notifications'     => 'Notifications',
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
    'never'             => 'Never',
    'none'              => 'None',
    'yes'               => 'Yes',
    'no'                => 'No',
    'warning'           => 'WARNING',
    'socket_error'      => 'Server error',
    'socket_error_info' => 'A connection could not be established to the socket at <strong>' . config('fixhub.socket_url') . '</strong>. This is required ' .
                           'for reporting the status on running deployments. Please reload, if the issue continues please contact the system administrator',
    'not_down'          => 'You must switch to maintenance mode before running this command, this will ensure that no new deployments are started',
    'switch_down'       => 'Switch to maintenance mode now? The app will switch back to live mode once cleanup is finished',
    'update_available'  => 'An update is available!',
    'outdated'          => 'You are running an out of date release :current, there is an updated release <a href=":link" rel="noreferrer">:latest</a> available!',

];
