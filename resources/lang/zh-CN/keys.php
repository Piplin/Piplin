<?php

return [

    'manage'            => '秘钥管理',
    'label'             => 'SSH keys',
    'none'              => '还没有设置SSH Keys',
    'name'              => '名称',
    'fingerprint'       => '指纹',
    'create'            => '添加SSH Key',
    'create_success'    => 'SSH key添加成功。',
    'edit'              => '编辑',
    'edit_success'      => 'SSH key信息更新成功。',
    'delete_success'    => '该SSH key已被成功删除。',
    'warning'           => '保存失败，请检查表单信息',
    'view_ssh_key'      => '查看SSH Key',
    'private_ssh_key'   => 'SSH私钥',
    'public_ssh_key'    => 'SSH公钥',
    'ssh_key'           => 'SSH秘钥',
    'ssh_key_info'      => '想要用指定的私钥，请将其内容拷贝至此。(私钥本身不要设置密码)',
    'ssh_key_example'   => '私钥内容为空，系统将自动创建(推荐)',
    'server_keys'       => '请将以下内容追加到目标服务器部署用户的<code>~/.ssh/authorized_keys</code>文件。',
    'git_keys'          => '如果与项目关联的Git仓库需要身份验证，请将以上内容添加到Git仓库的<strong>Deploy Keys</strong>里。',

];
