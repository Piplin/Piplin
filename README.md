# Fixhub

[![StyleCI](https://styleci.io/repos/67609292/shield)](https://styleci.io/repos/67609292/)
[![Build Status](https://travis-ci.org/Fixhub/Fixhub.svg?branch=master)](https://travis-ci.org/Fixhub/Fixhub)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Fixhub 是一款基于PHP [Laravel 5.5](http://laravel.com)框架开发的开源Web自动化部署系统。

![Screenshot](http://www.fixhub.org/fixhub.png)

## Fixhub能做什么？

* 支持PHP、Python、JAVA、Ruby等项目的发布
* 通过SSH将程序部署到多台服务器上
* 直接从Git仓库克隆项目代码并进行打包、安装
* 可灵活配置自定义部署步骤
* 支持项目的多环境部署(可自行建立开发、测试、预发布和生产等多个环境)
* 支持联动部署，比如：开发环境部署成功后可自动触发测试环境启动部署
* 服务管理支持机柜功能，机柜可与多个部署环境绑定
* 支持项目克隆与模板功能
* 支持项目成员，项目可添加个多个成员
* 通过Websocket实现项目部署状态的实时跟踪
* 支持Gitlab、Github、Gogs、Gitee(Oschina)等代码托管平台进行集成
* 支持钉钉机器人、Slack、邮件和自定义Webhook的服务集成

## 安装环境要求

Fixhub目前只支持类Unix操作系统，为了能运行Fixhub，您还需要安装一些基础软件。

- Web服务器: **Nginx**, **Apache** (with mod_rewrite)，or **Lighttpd**
- [PHP](http://www.php.net) 7.0.0+或更高(不再支持PHP7以下版本)
- 数据库: 推荐使用[MySQL](https://www.mysql.com) 或 [PostgreSQL](http://www.postgresql.org)。 当然[SQLite](https://www.sqlite.org)也可以运行。
- [Composer](https://getcomposer.org)
- [Redis](http://redis.io)
- [Node.js](https://nodejs.org/)
- [队列系统](http://laravel.com/docs/5.5/queues), 推荐使用[Beanstalkd](http://kr.github.io/beanstalkd/)或Redis。
- [Rsync](https://rsync.samba.org/) 如无特殊情况，一般系统都会自带rsync

### 可选项

- 为了确保队列监听、websocket等后台服务的正常运行，推荐使用[Supervisor](http://supervisord.org)
- 缓存服务推荐使用Memcached, 更多的缓存方案选择请看 [caching server](http://laravel.com/docs/5.5/cache).

## 安装手册

一. 克隆代码

```shell
$ git clone https://github.com/fixhub/fixhub.git
```

二. 安装依赖包

```shell
$ composer install -o --no-dev
```

三. 安装socket.io

```shell
$ npm install --production
```

> 安装过程如出现卡顿，请尝试更换npm镜像: `npm config set registry http://registry.npm.taobao.org/`

四. 确保storage、bootstrap/cache和public/upload目录可写。

```shell
$ make file-permission
```

五. 拷贝.env.example到.env

```shell
$ cp .env.example .env
```

六. 安装Fixhub

```shell
$ php artisan app:install
```

七. 清除配置缓存

```shell
$ php artisan config:clear
```

八. 将你的服务器根目录指向 `public/`, 请查看 [examples/](/examples) 下的相关配置文件，里面包含 Apache和 nginx的配置范例.

九. 启动web socket，配置相关计划任务.

1、 通过`supervisor`管理Fixhub后台服务，请看 [examples/supervisor.conf](examples/supervisor.conf)

2、 不通过`supervisor`管理Fixhub后台服务，你需要手动启动websocket服务。在Fixhub根目录执行`node socket.js` (目录监听6001端口)。手动设置计划任务请看 [examples/crontab](examples/crontab).

### 升级

一. 获取最新代码

```shell
$ git fetch --all
$ git checkout 0.4.2
 ```

二. 更新依赖

```shell
$ composer install -o --no-dev
```

三. 执行Fixhub升级

```shell
$ php artisan app:update
```

## 系统演示

体验Fixhub, 请访问 [Fixhub](http://fixhub.org):

- **用户名:** fixhub 或 `fixhub@fixhub.org`
- **密码:** `fixhub`

> 注意：系统每5分钟会自动重置一次密码，该账号没有权限访问管理功能.

## 使用到的技术

- [x] Laravel
- [x] Bootstrap
- [x] ionicons
- [x] Node.js
- [x] Beanstalkd
- [x] Redis
- [x] Memcached
- [x] JWT-Auth
- [x] Socket.io
- [x] Supervisor

## 开发使用到的技术

- [x] Webpack
- [x] Sass
- [x] Codception
- [x] PHP CodeSniffer
- [x] PHP Docblock Checker
- [x] PHP CS Fixer
- [x] Travis-ci
- [x] Style-CI
- [x] Gitlab-Ci

## 开发相关

Fixhub代码里已经自带编译后的前端静态资源，如果你不想修改前端样式，可直接忽略本环节。

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

Fixhub is licensed under [The MIT License (MIT)](LICENSE).
