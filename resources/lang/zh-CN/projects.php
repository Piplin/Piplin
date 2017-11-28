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

    'manage'            => '项目管理',
    'warning'           => '项目保存失败，请检查表单信息(必要时请切换选项卡).',
    'none'              => '暂未设置项目',
    'name'              => '项目名称',
    'name_placeholder'  => '给项目起一个名字...',
    'group'             => '项目分组',
    'ungrouped'         => '不分组',
    'key'               => '部署秘钥',
    'repository'        => '代码仓库',
    'repository_path'   => '仓库路径',
    'deploy_path'       => '项目路径',
    'deploy_path_help'  => '当执行部署时，Piplin会在远程服务器的部署路径内自动创建一个名为 <code>releases</code> 的目录。同时会建立 <code>current</code> 软链到最新版本。<br />假设该项值被设置为 <code>/data/www/app</code>，那么Web服务的主目录可能要设为<code>/data/www/app/current/public</code>。',
    'builds'            => '保留版本',
    'build_options'     => '部署选项',
    'clone'             => '克隆',
    'clone_name'        => '名称',
    'clone_type'        => '目标类型',
    'clone_duplicate'   => '克隆成新项目',
    'clone_convert'     => '转换成部署模板',
    'branch'            => '默认分支',
    'last_mirrored'     => '代码同步',
    'image'             => '构建状态',
    'ci_image'          => '请填写项目构建状态的图标URL',
    'integrations'      => '服务集成',
    'deployed'          => '最近上线',
    'apply'             => '申请上线',
    'apply_choose'      => '选择项目',
    'apply_intro'       => '申请一旦被逐级审核通过，将由运维执行上线操作。',
    'create'            => '创建项目',
    'create_success'    => '项目创建成功。',
    'edit'              => '编辑',
    'edit_success'      => '项目信息更新成功。',
    'delete'            => '删除项目',
    'delete_success'    => '该项目已被成功删除。',
    'settings'          => '项目设置',
    'url'               => 'URL',
    'details'           => '项目详情',
    'tasks'             => '任务统计',
    'today'             => '今日任务',
    'last_week'         => '上周任务',
    'total_count'       => '总共任务',
    'status'            => '项目状态',
    'build_status'      => '构建状态',
    'deploy_project'    => '开始上线',
    'deploy'            => '开始上线',
    'deploy_plan'       => '部署计划',
    'build_plan'        => '构建计划',
    'rollback'          => '回滚',
    'finished'          => '已完成',
    'pending'           => '等待中',
    'running'           => '运行中',
    'failed'            => '失败',
    'not_deployed'      => '未上线',
    'options'           => '选项',
    'change_branch'     => '允许运行其他分支?',
    'insecure'          => 'SSH秘钥不建议在非HTTPS协议中传输中，建议留空由Piplin自动生成。',
    'history'           => '历史记录',

];
