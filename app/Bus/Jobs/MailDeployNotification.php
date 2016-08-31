<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Bus\Jobs;

use Fixhub\Models\Deployment;
use Fixhub\Models\Project;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use McCool\LaravelAutoPresenter\Facades\AutoPresenter;

/**
 * Send email notifications for deployment.
 */
class MailDeployNotification extends Job
{
    private $project;
    private $deployment;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Project $project, Deployment $deployment)
    {
        $this->project    = $project;
        $this->deployment = $deployment;
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $emails = $this->project->notifyEmails;

        if ($emails->count() > 0) {
            $status = strtolower(AutoPresenter::decorate($this->project)->readable_status);

            $subject = trans(
                'notifyEmails.subject',
                ['status' => $status, 'project' => $this->project->name]
            );

            $deploymentArr                = $this->deployment->toArray();
            $deploymentArr['commitURL']   = $this->deployment->commit_url;
            $deploymentArr['shortCommit'] = $this->deployment->short_commit;

            $data = [
                'project'    => $this->project->toArray(),
                'deployment' => $deploymentArr,
            ];

            Mail::queueOn(
                'fixhub-low',
                'emails.deployed',
                $data,
                function (Message $message) use ($emails, $subject) {
                    foreach ($emails as $email) {
                        $message->to($email->email, $email->name);
                    }

                    $message->subject($subject);
                }
            );
        }
    }
}
