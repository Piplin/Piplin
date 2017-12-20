# Piplin - [![Composer Cache](https://shield.with.social/cc/github/Piplin/Piplin/master.svg?style=flat-square)](https://packagist.org/packages/laravel/framework)

[![StyleCI](https://styleci.io/repos/67609292/shield)](https://styleci.io/repos/67609292/)
[![Build Status](https://travis-ci.org/Piplin/Piplin.svg?branch=master)](https://travis-ci.org/Piplin/Piplin)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Piplin(灵感来自于"pipeline"，读作/ˈpɪpˌlɪn/ 或 /ˈpaɪpˌlaɪn/)是一款免费、开源的持续集成与部署系统，适用于软件的自动化构建、测试和部署相关的各种应用场景。


![Screenshot](http://piplin.com/img/screenshot.png?v1)

## Piplin能做什么？

* 支持PHP、Python、JAVA、Ruby等项目的构建、测试与发布
* 可与Gitlab、Github、Gogs、Gitee(Oschina)等代码托管平台进行集成
* 可灵活配置自定义构建和部署步骤
* 支持自定义构建物规则，对构建物创建发布版本并部署
* 支持项目的多环境部署(可自行建立开发、测试、预发布和生产等多个环境)
* 支持联动部署，比如：开发环境部署成功后可自动触发测试环境启动部署
* 服务管理支持机柜功能，机柜可与多个部署环境绑定
* 支持项目克隆功能
* 项目支持多成员
* 通过Websocket实现项目部署状态的实时跟踪
* 支持钉钉机器人、Slack、邮件和自定义Webhook的服务集成

## Piplin原理示意图

### 总体

![Principle](http://piplin.com/img/principle.png?v2)

### 步骤命令

![Commands](http://piplin.com/screenshots/commands.png?v1)

## 安装环境要求

Piplin目前只支持类Unix操作系统(如: Linux, Freebsd, Mac OS等)，为了能运行Piplin，您还需要安装一些基础软件。

- Web服务器: **Nginx**, **Apache** (with mod_rewrite)，or **Lighttpd**
- [PHP](http://www.php.net) 7.0+
- 数据库: 推荐使用[MySQL](https://www.mysql.com) 或 [PostgreSQL](http://www.postgresql.org)。 [SQLite](https://www.sqlite.org)也可运行。
- [Composer](https://getcomposer.org)
- [Redis](http://redis.io)
- [Node.js](https://nodejs.org/)
- [队列系统](http://laravel.com/docs/5.5/queues), 推荐使用[Beanstalkd](http://kr.github.io/beanstalkd/)或Redis。
- [Supservisord](http://www.supervisord.org/), Piplin使用Supervisord进行后台进程管理。
- [Rsync](https://rsync.samba.org/) 如无特殊情况，一般系统都会自带rsync
- 缓存服务: 推荐使用Memcached, 更多的缓存方案选择请看 [caching server](http://laravel.com/docs/5.5/cache).

> Docker安装，请访问我们的[Piplin Docker](https://github.com/Piplin/Docker)项目。

## 安装手册

### 全新安装

#### 一. 克隆代码

```shell
$ git clone https://github.com/Piplin/Piplin.git piplin
```

#### 二. 安装依赖包

```shell
$ cd piplin
$ make
```

> 安装过程如出现卡顿，请尝试更换npm镜像: `npm config set registry http://registry.npm.taobao.org/`

#### 三. 安装Piplin

```shell
$ make install
```

> Piplin安装器会进入一个交互式控制台，请根据提示进行相关参数设置。

#### 四. 请将Web服务器的根目录指向 `public/`, 请参考 [examples/](/examples) 下的相关配置文件，里面包含 Apache和Nginx的配置范例.

> 注意: `examples/` 提供的仅仅是范例，并不能保证直接拷贝就能使用，需要根据实际情况进行相关配置调整。

#### 五. 配置supervisord

Piplin使用`supervisord`进行后台进程管理。该配置范例请查看[examples/supervisor.conf](examples/supervisor.conf)。 一般supervisord的主配置文件在 `/etc/supervisor/supervisord.conf` ，其大致内容：

```
[unix_http_server]
file=/var/run/supervisor.sock   ; (the path to the socket file)
chmod=0700                       ; sockef file mode (default 0700)

......

[include]
files = /etc/supervisor/conf.d/*.conf
```

##### 配置步骤如下：

1). 拷贝 examples/supervisor.conf

```shell
$ cp examples/supervisor.conf /etc/supervisor/conf.d/piplin.conf
$ vi /etc/supervisor/conf.d/piplin.conf
```

> 请根据实际情况修改相关参数设置，尤其注意路径相关的参数。

2). 重启supervisord

```shell
$ /etc/init.d/supervisord restart 或 service supervisord restart
```

3). 检查supervisord服务是否正常

```shell
$ supervisorctl
```

如果返回如下信息，代表服务正常:

```
piplin:queue_0                   RUNNING   pid 26981, uptime 2 days, 15:30:59
piplin:queue_1                   RUNNING   pid 26980, uptime 2 days, 15:30:59
piplin:queue_2                   RUNNING   pid 26979, uptime 2 days, 15:30:59
piplin-broadcast                 RUNNING   pid 26987, uptime 2 days, 15:30:59
piplin-socketio                  RUNNING   pid 26978, uptime 2 days, 15:30:59
supervisor>
```

六. 访问Piplin

恭喜！您已完成Piplin的安装。请通过浏览器访问安装过程中设置的应用网址。

> 计划任务相关的设置请看 [examples/crontab](examples/crontab).


### 升级

一. 获取最新代码

```shell
$ git fetch --all
$ git checkout v1.0.1
 ```

二. 升级

```shell
$ make update
```

## 系统演示

体验Piplin, 请访问 [Piplin](http://piplin.com):

- **用户名:** piplin 或 `piplin@piplin.com`
- **密码:** `piplin`

> 注意：系统每5分钟会自动重置一次密码，该账号没有权限访问管理功能.

## 开发相关

Piplin代码里已经自带编译后的前端静态资源，如果你不想修改前端样式，可直接忽略本环节。

工具集：

- Node.js
- Webpack

```shell
npm install
npm run prod
```

## 鸣谢

- [Laravel](http://laravel.com)
- [Bootstrap](https://github.com/twbs/bootstrap)
- [AdminLTE](https://github.com/almasaeed2010/AdminLTE)
- [Envoy](https://laravel.com/docs/5.5/envoy)
- [Forge](https://forge.laravel.com/)
- [Deployer](https://github.com/REBELinBLUE/deployer)
- [socket.io](https://github.com/socketio/socket.io)
- [ionicons](http://ionicons.com/)

## 软件授权协议

Piplin is licensed under [The MIT License (MIT)](LICENSE). Piplin is based on [Deployer](https://github.com/REBELinBLUE/deployer).
