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

    'label'                            => '推送服务',
    'create'                           => '添加推送服务',
    'create_success'                   => '推送服务创建成功',
    'create_slack'                     => '添加Slack推送',
    'create_dingtalk'                  => '添加钉钉推送',
    'create_mail'                      => '添加邮件订阅',
    'create_custom'                    => '添加Webhook推送',
    'edit'                             => '编辑推送服务',
    'edit_success'                     => '推送服务信息更新成功。',
    'edit_slack'                       => '编辑Slack推送',
    'edit_dingtalk'                    => '编辑钉钉推送',
    'edit_mail'                        => '编辑邮件订阅',
    'edit_custom'                      => '编辑Webhook推送',
    'delete_success'                   => '该推送服务已被成功删除。',
    'none'                             => '该项目还没有设置任何推送',
    'integration_help'                 => '通过集成第三方服务，Piplin可实现对项目的部署状态进行实时推送。',
    'name'                             => '名称',
    'type'                             => '类型',
    'warning'                          => '推送服务无法保存，请检查表单信息',
    'not_configured_title'             => '服务未配置',
    'not_configured'                   => '选择的推送服务无法使用，因为尚未配置',
    'triggers'                         => '触发事件',
    'webhook'                          => 'Webhook URL',
    'icon'                             => '图标',
    'bot'                              => 'Bot',
    'icon_info'                        => '输入 emoji, 例如 :ghost: 或者图片 URL ',
    'channel'                          => '频道',
    'deployments'                      => '部署',
    'succeeded'                        => '成功',
    'failed'                           => '失败',
    'on_deployment_success'            => '部署成功',
    'on_deployment_failure'            => '部署失败',
    'custom'                           => 'Webhook',
    'slack'                            => 'Slack',
    'dingtalk'                         => '钉钉',
    'mail'                             => '邮件',
    'which'                            => '请选择推送服务:',
    'test_subject'                     => 'Piplin推送测试',
    'test_message'                     => '恭喜，推送服务已生效 - 来自 :app_url',
    'enabled'                          => '是否启用?',

    // Slack
    'branch'                           => '分支',
    'project'                          => '项目',
    'commit'                           => '代码版本号',
    'committer'                        => '代码提交者',
    'deployment_reason'                => '部署原因 - :reason',
    'deployment_success_slack_message' => ':white_check_mark: 上线任务 %s 部署成功! :smile:',
    'deployment_failed_slack_message'  => ':x: 上线任务 %s 部署失败! :cry:',

    // Dingtalk
    'deployment_success_ding_message' => '上线任务 %s 部署成功!',
    'deployment_failed_ding_message'  => '上线任务 %s 部署失败!',
    'at_mobiles'                      => '被@人的手机号',
    'is_at_all'                       => '@所有人',

    // Email
    'project_name'                     => '项目名称',
    'deployed_branch'                  => '部署分支',
    'deployment_details'               => '部署详情',
    'project_details'                  => '项目详情',
    'started_at'                       => '开始时间',
    'finished_at'                      => '完成时间',
    'last_committer'                   => '代码提交者',
    'last_commit'                      => '代码版本号',
    'reason'                           => '部署原因 - :reason',
    'deployment_success_email_subject' => '部署已完成',
    'deployment_success_email_message' => '本次部署任务已执行成功',
    'deployment_failed_email_subject'  => '部署失败',
    'deployment_failed_email_message'  => '本次部署任务执行失败了',
];
