<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Bus\Notifications\Task;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use NotificationChannels\Webhook\WebhookMessage;
use Piplin\Bus\Notifications\Notification;
use Piplin\Models\Task;
use Piplin\Models\Hook;
use Piplin\Models\Project;

/**
 * Base class for Task notifications.
 */
abstract class TaskFinishedNotification extends Notification
{
    /**
     * @var Project
     */
    protected $project;

    /**
     * @var Task
     */
    protected $task;

    /**
     * Create a new notification instance.
     *
     * @param Project $project
     * @param Task    $task
     */
    public function __construct(Project $project, Task $task)
    {
        $this->project    = $project;
        $this->task = $task;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param string $subject
     * @param string $translation
     * @param Hook   $notification
     *
     * @return MailMessage
     */
    protected function buildMailMessage($subject, $translation, Hook $notification)
    {
        $message = trans($translation);

        $table = [
            trans('hooks.project_name')    => $this->project->name,
            trans('hooks.deployed_branch') => $this->task->branch,
            trans('hooks.started_at')      => $this->task->started_at,
            trans('hooks.finished_at')     => $this->task->finished_at,
            trans('hooks.last_committer')  => $this->task->committer,
            trans('hooks.last_commit')     => $this->task->short_commit,
        ];

        $action = route('tasks', ['id' => $this->task->id]);

        $email = (new MailMessage())
            ->view(['notifications.email', 'notifications.email-plain'], [
                'name'  => $notification->name,
                'table' => $table,
            ])
            ->subject(trans($subject))
            ->line($message)
            ->action(trans('hooks.task_details'), $action);

        if (!empty($this->task->reason)) {
            $email->line(trans('hooks.task_reason', ['reason' => $this->task->reason]));
        }

        return $email;
    }

    /**
     * Get the slack version of the notification.
     *
     * @param string $translation
     * @param Hook   $notification
     *
     * @return SlackMessage
     */
    protected function buildSlackMessage($translation, Hook $notification)
    {
        $message = trans($translation);

        $fields = [
            trans('hooks.project') => sprintf(
                '<%s|%s>',
                route('projects', ['id' => $this->project->id]),
                $this->project->name
            ),
            trans('hooks.commit') => $this->task->commit_url ? sprintf(
                '<%s|%s>',
                $this->task->commit_url,
                $this->task->short_commit
            ) : $this->task->short_commit,
            trans('hooks.committer') => $this->task->committer,
            trans('hooks.branch')    => $this->task->branch,
        ];

        return (new SlackMessage())
            ->from(null, $notification->config->icon)
            ->to($notification->config->channel)
            ->attachment(function (SlackAttachment $attachment) use ($message, $fields) {
                $attachment
                    ->content(sprintf($message, sprintf(
                        '<%s|#%u>',
                        route('tasks', ['id' => $this->task->id]),
                        $this->task->id
                    )))
                    ->fallback(sprintf($message, '#' . $this->task->id))
                    ->fields($fields)
                    ->footer(trans('app.name'))
                    ->timestamp($this->task->finished_at);
            });
    }

    /**
     * Get the dingtalk version of the notification.
     *
     * @param string $translation
     * @param Hook   $notification
     *
     * @return WebhookMessage
     */
    protected function buildDingtalkMessage($translation, Hook $notification)
    {
        $message = trans($translation);
        $subject = sprintf($message, '#' . $this->task->id);
        $commit  = $this->task->commit_url ? sprintf(
            '[%s](%s)',
            $this->task->short_commit,
            $this->task->commit_url
        ) : $this->task->short_commit;
        $task_url = route('tasks', ['id' => $this->task->id]);

        $content = trans('hooks.project') . ': ' . sprintf(
            '[%s](%s)',
            $this->project->name,
            route('projects', ['id' => $this->project->id])
        ) . ' ';
        $content .= trans('hooks.commit') . ': ' . $commit . "\n\n";

        $content .= trans('hooks.committer') . ':' . $this->task->committer . ' ';
        $content .= trans('hooks.branch') . ':' . $this->task->branch . "\n\n";

        if (!empty($this->task->reason)) {
            $content .= '> ' . trans('hooks.task_reason', ['reason' => $this->task->reason]) . "\n\n";
        }

        $text = '#### ' . $subject . "\n"
                 . $content
                 . '##### [' . trans('hooks.task_details') . '](' . $task_url . ")\n\n";
        $atMobiles = !empty($notification->config->at_mobiles) ? explode(',', $notification->config->at_mobiles) : [];

        return (new WebhookMessage())
            ->data([
                'msgtype'  => 'markdown',
                'markdown' => [
                    'title' => $subject,
                    'text'  => $text,
                ],
                'at' => [
                    'atMobiles' => $atMobiles,
                    'isAtAll'   => !!$notification->config->is_at_all,
                ],
            ])->header('Content-Type', 'application/json;charset=utf-8');
    }

    /**
     * Get the webhook version of the notification.
     *
     * @param string $event
     * @param Hook   $notification
     *
     * @return WebhookMessage
     */
    protected function buildWebhookMessage($event, Hook $notification)
    {
        return (new WebhookMessage())
            ->data(array_merge(array_only(
                $this->task->attributesToArray(),
                ['id', 'branch', 'started_at', 'finished_at', 'commit', 'source', 'reason']
            ), [
                'project'      => $this->task->project_name,
                'committed_by' => $this->task->committer,
                'started_by'   => $this->task->author_name,
                'status'       => ($event === 'task_succeeded') ? 'success' : 'failure',
                'url'          => route('tasks', ['id' => $this->task->id]),
            ]))
            ->header('X-Piplin-Project-Id', $notification->project_id)
            ->header('X-Piplin-Notification-Id', $notification->id)
            ->header('X-Piplin-Event', $event);
    }
}
