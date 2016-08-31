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

    'manage'            => '项目管理',
    'warning'           => '项目保存失败，请检查表单信息(必要时请切换选项卡).',
    'none'              => '暂未设置项目',
    'name'              => '项目名称',
    'name_placeholder'  => '我的项目',
    'group'             => '项目分组',
    'repository'        => '仓储',
    'repository_url'    => '仓储路径',
    'builds'            => '版本记录',
    'build_options'     => '构建选项',
    'project_details'   => '项目详情',
    'branch'            => '默认分支',
    'last_mirrored'     => '代码同步',
    'image'             => '构建状态',
    'ci_image'          => 'If you use a CI server which generates an image to indicate the build status ' .
                           'can put the URL here tox have it show on the project page',
    'latest'            => '最近上线',
    'create'            => '新增项目',
    'edit'              => '编辑',
    'url'               => 'URL',
    'details'           => '仓储详情',
    'deployments'       => '上线统计',
    'today'             => '今日上线次数',
    'last_week'         => '上周上线次数',
    'latest_duration'   => '上次上线耗时',
    'health'            => '项目状态',
    'deploy_status'     => '部署状态',
    'build_status'      => '构建状态',
    'app_status'        => '应用状态',
    'heartbeats_status' => '心跳状态',
    'view_ssh_key'      => '查看SSH秘钥内容',
    'private_ssh_key'   => 'SSH私钥',
    'public_ssh_key'    => 'SSH公钥',
    'ssh_key'           => 'SSH私钥',
    'ssh_key_info'      => '想要用指定的私钥，请将其内容拷贝至此。(私钥本身不要设置密码)',
    'ssh_key_example'   => '假如留空，系统将自动创建。(推荐)',
    'deploy_project'    => '开始上线',
    'deploy'            => '开始上线',
    'redeploy'          => '重新上线',
    'server_keys'       => '以下内容必须被追加到目标服务器部署用户的<code>~/.ssh/authorized_keys</code>文件中。',
    'git_keys'          => '如果你的git仓储需要身份验证，以下内容也应该被添加到相关的<strong>Deploy Keys</strong>配置中。',
    'finished'          => '已完成',
    'pending'           => '等待中',
    'deploying'         => '正在上线',
    'failed'            => '失败',
    'not_deployed'      => '未上线',
    'options'           => '选项',
    'change_branch'     => '允许上线其他分支?',
    'include_dev'       => '安装composer开发包?',
    'insecure'          => 'Your Fixhub installation is not running over a secure connection, it is recommended ' .
                           'that you let Fixhub generate an SSH key rather than supply one yourself so that the ' .
                           'private key is not transmitted over an insecure connection.',

];
