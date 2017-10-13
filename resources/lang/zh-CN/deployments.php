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

    'label'                 => '上线单',
    'latest'                => '上线单列表',
    'none'                  => '该项目尚未执行过上线工作.',
    'started_by'            => '触发来源',
    'deployer'              => '上线者',
    'committer'             => '代码提交',
    'commit'                => '提交版本',
    'manually'              => '手动执行',
    'webhook'               => 'Webhook',
    'rollback'              => '回滚到此版本',
    'rollback_title'        => '请填写回滚信息',
    'expert'                => '回滚有风险，操作请谨慎！',
    'rollback_warning'      => '如有必要请登录目标服务器进行一些清理工作。',
    'rollback_reason'       => ':reason (回滚至 #:id -> :commit)',
    'caution'               => '注意!',
    'cancel'                => '取消',
    'approve'               => '审核通过',
    'deploy'                => '开始上线',
    'loading'               => '加载中',
    'unknown'               => '未知',
    'process'               => '控制台输出',
    'server'                => '服务器',
    'status'                => '状态',
    'started'               => '开始',
    'finished'              => '结束',
    'duration'              => '耗时',
    'output'                => '查看输出',
    'deployment_number'     => '上线单 #:id',
    'pending'               => '等待中',
    'deploying'             => '正在上线中',
    'completed'             => '完成',
    'completed_with_errors' => '完成(异常)',
    'aborted'               => '已中止',
    'aborting'              => '中止',
    'approved'              => '审核通过',
    'approving'             => '审核中',
    'failed'                => '失败',
    'running'               => '运行中',
    'cancelled'             => '已取消',
    'reason'                => '上线信息',
    'source'                => '代码',
    'default'               => '默认分支 (:branch)',
    'different_branch'      => '其他分支',
    'tag'                   => '标签',
    'branch'                => '分支',
    'environment'           => '部署环境',
    'warning'               => '上线工作尚未启动，请确保完成填写所有必填项.',
    'describe_commit'       => '例如：0c6b0d8',
    'describe_reason'       => '请简单描述本次上线的原因',
    'optional'              => '可选步骤',
    'week'                  => ':time 星期|:time 星期',
    'day'                   => ':time 天|:time 天',
    'hour'                  => ':time 小时|:time 小时',
    'minute'                => ':time 分钟|:time 分钟',
    'second'                => ':time 秒|:time 秒',
    'repo_failure'          => '获取git仓库信息失败，请确认git仓库地址正确，并且SSH秘钥被添加。',
    'repo_failure_head'     => '获取代码库失败',

];
