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

    'label'             => '环境变量',
    'create'            => '新增',
    'edit'              => '编辑',
    'name'              => '变量名',
    'value'             => '变量值',
    'warning'           => '信息保存失败.',
    'description'       => '在部署过程中需要用到一些自定义的环境变量，' .
                           '但你又不希望在服务器的<code>~/.bashrc</code>文件中设置，就可以在这里设置。',
    'example'           => '举例：设置<code>COMPOSER_PROCESS_TIMEOUT</code>并给它一个值，告诉composer运行超时时间。' .
                           '设置<code>SYMFONY_ENV</code>告知Fixhub当前正在部署的是一个symfony项目。',

];
