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
    'view_ssh_key'      => '预览部署秘钥(SSH Key)',
    'private_ssh_key'   => 'SSH私钥',
    'public_ssh_key'    => '部署公钥',
    'ssh_key'           => '部署公钥',
    'ssh_key_info'      => '想要用指定的私钥，请将其内容拷贝至此。(私钥本身不要设置密码)',
    'ssh_key_example'   => '私钥内容为空，系统将自动创建(推荐)',
    'server_keys'       => '目标服务器需要添加部署公钥方可执行部署任务。请将公钥拷贝到服务器部署用户目录的<code>$HOME/.ssh/authorized_keys</code>文件中。项目部署公钥如下：',
    'git_keys'          => '如果与项目关联的Git仓库需要身份验证，请将公钥添加到Git仓库的<strong>Deploy Keys</strong>里。',

];
