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

    'label'          => '环境变量',
    'create'         => '新增',
    'create_success' => '环境变量创建成功。',
    'edit'           => '编辑',
    'edit_success'   => '环境变量更新成功。',
    'delete_success' => '该环境变量已被成功删除。',
    'name'           => '变量名',
    'value'          => '变量值',
    'warning'        => '信息保存失败.',
    'none'           => '还没有配置环境变量',
    'description'    => '在部署过程中需要用到一些自定义的环境变量，' .
                           '但你又不希望在服务器的<code>~/.bashrc</code>文件中设置，就可以在这里设置。',
    'example'        => '例：设置<code>COMPOSER_PROCESS_TIMEOUT</code>，告诉composer运行超时时间。',

];
