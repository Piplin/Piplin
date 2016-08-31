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

    'label'                => '部署步骤',
    'deploy_webhook'       => '当第三方应用调用以下webhook链接，Fixhub会自动触发该项目的上线',
    'webhook_help'         => 'Webhook 帮助',
    'webhook_example'      => '通过HTTP的POST请求，调用该URL，Fixhub会获取最近一次提交的代码，并自动触发上线。',
    'webhook_fields'       => '可选的POST参数设置',
    'webhook_reason'       => 'The reason the deployment is being run',
    'webhook_source'       => 'A name to use to indicate who/what triggered the deployment, for instance "CI server"',
    'webhook_branch'       => '部署分支，不填写将自动读取项目设置的默认分支',
    'webhook_update'       => 'Whether a deployment should only run if the currently deployed branch matches to branch to be deployed' .
                              ' , defaults to false',
    'webhook_url'          => 'A URL to link the name to, for example the build details on your CI server',
    'webhook_commands'     => 'A comma seperated list of optional command IDs to run',
    'webhook_optional'     => '可选的命令ID',
    'webhook_curl'         => 'cURL 调用范例',
    'reason_example'       => '例如: 系统测试',
    'generate_webhook'     => '重新生成一个webhook链接 (注意: 旧链接将失效)',
    'step'                 => '步骤',
    'before'               => '前置任务',
    'name'                 => '名称',
    'run_as'               => '运行用户',
    'migrations'           => '数据迁移',
    'bash'                 => 'Bash脚本',
    'servers'              => '服务器',
    'default'              => '默认',
    'options'              => 'Bash脚本中可使用的变量 (点击查看)',
    'release_id'           => '发布版本',
    'release_path'         => '发布路径',
    'branch'               => '部署分支',
    'project_path'         => '项目路径',
    'after'                => '后置任务',
    'configure'            => '配置',
    'clone'                => '获取代码',
    'install'              => '安装新版本',
    'activate'             => '切换新版本',
    'purge'                => '清理旧版本',
    'title'                => ':step Commands',
    'warning'              => '保存失败，请检查表单信息.',
    'create'               => '新增',
    'edit'                 => '编辑',
    'sha'                  => 'SHA哈希值',
    'short_sha'            => 'SHA哈希值(短)',
    'deployer_name'        => '上线发起者',
    'deployer_email'       => '上线发起者邮箱',
    'committer_name'       => '代码最后提交者',
    'committer_email'      => '代码最后提交者邮箱',
    'none'                 => '还没有配置安装命令',
    'optional'             => '可选',
    'example'              => '例如:',
    'optional_description' => '上线时该步骤是否需要被提示包含',
    'default_description'  => '包含该步骤，除非显式禁止 (例如: 复选框默认被选中)',
    'services'             => 'Fixhub支持的服务提供商',
    'services_description' => 'You can use the webhook with these services and the relevant details will be gathered from the data ' .
                              'they send across. The &quot;<em>update_only</em>&quot; and &quot;<em>commands</em>&quot; parameters ' .
                              'may be included in the query string, all other fields are ignored.',

];
