# Fixhub

[![StyleCI](https://styleci.io/repos/67016052/shield)](https://styleci.io/repos/67016052/)
[![Build Status](https://img.shields.io/travis/Fixhub/Fixhub/master.svg?style=flat-square)](https://travis-ci.org/Fixhub/Fixhub)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Fixhub 是一款免费、开源，基于[Laravel 5.3](http://laravel.com)框架开发的web自动部署系统。

![Screenshot](http://fixhub.org/upload/screenshot.png)

## Fixhub能做什么？

**注意** Fixhub仍处于并将长期处于初级阶段，下面的部分特性可能还没有100%完成，即使完成了也有可能会有bug。有任何问题请随时向我们反馈。

* 通过SSH将程序部署到多台服务器上
* 与Git仓储打通（最好与自托管的Gitlab服务器对接）
* 安装composer依赖
* 执行远程服务器bash命令
* 非常友好的上线过程信息提示
* 在服务器保留追溯版本，以便回滚时使用
* 任务计划的健康检测
* 通过webhook触发部署
* Slack和邮件通知

## 下一阶段需要实现的功能

- [ ] 通过OAuth2.0实现与自托管的Gitlab账户打通
- [ ] 权限控制
- [ ] 工单，工作流
- [ ] 可视化的统计

## 使用到的技术

- [x] Laravel 5.3
- [x] Bootstrap
- [x] ionicons
- [x] Node.js
- [x] Beanstalkd
- [x] Redis
- [x] Memcached
- [x] JWT-Auth
- [x] Socket.io
- [x] Supervisor

## 开发相关

- [x] Gulp
- [x] Sass
- [x] PHPUnit
- [x] PHP CodeSniffer
- [x] PHP Docblock Checker
- [x] PHP CS Fixer
- [x] Travis-ci
- [x] Style-CI
- [x] Gitlab-Ci


## 安装环境要求

- [PHP](http://www.php.net) 5.6.4+或更高(推荐使用PHP7)
- 数据库, 推荐使用[MySQL](https://www.mysql.com) 或 [PostgreSQL](http://www.postgresql.org)。 当然[SQLite](https://www.sqlite.org)也可以运行。
- [Composer](https://getcomposer.org)
- [Redis](http://redis.io)
- [Node.js](https://nodejs.org/)
- [队列系统](http://laravel.com/docs/5.3/queues), 推荐使用[Beanstalkd](http://kr.github.io/beanstalkd/)或Redis。

### 可选项

- 为了保持队列监听器以及websoket等后台服务的正常运行，推荐使用[Supervisor](http://supervisord.org)
- 缓存服务推荐使用Memcached, 更多的缓存方案选择请看 [caching server](http://laravel.com/docs/5.3/cache).

## 安装手册

一. Clone the repository

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

四. 确保storage、bootstrap/cache和public/upload目录可写。

```shell
$ chmod -R 777 bootstrap/cache
$ chmod -R 777 storage
$ chmod -R 777 public/upload
```

五. 拷贝.env.example到.env

```shell
$ cp .env.example .env
```

六. 安装Fixhub

```shell
$ php artisan app:install
```

七. 将你的服务器根目录指向 `public/`, 请查看 `examples/`下的相关配置文件，里面包含 Apache和 nginx的配置范例.

八. 启动web socket，配置相关计划任务.

    1、 通过`supervisor`管理Fixhub后台服务，请看`examples/supervisor.conf`

    2、 不通过`supervisor`管理Fixhub后台服务，你需要手动启动websocket服务。在Fixhub根目录执行`node socket.js` (目录监听6001端口)。手动设置计划任务请看`examples/crontab`.

### 升级

一. 获取最新代码

```shell
$ git fetch --all
$ git checkout 0.0.7
 ```

二. 更新依赖

```shell
$ composer install -o --no-dev
```

三. 执行Fixhub升级

```shell
$ php artisan app:update
```

### 系统演示

体验Fixhub, 请访问 [Fixhub](http://fixhub.org):

- **用户名:** demo 或 `demo@fixhub.org`
- **密码:** `fixhub`

注意：demo账号的角色为*开发工程师*，没有权限访问管理功能.

### 鸣谢

- [Laravel](http://laravel.com)
- [Bootstrap](https://github.com/twbs/bootstrap)
- [AdminLTE](https://github.com/almasaeed2010/AdminLTE)
- [Forge](https://forge.laravel.com/)
- [Deployer](https://github.com/REBELinBLUE/deployer)
- [socket.io](https://github.com/socketio/socket.io)
- [ionicons](http://ionicons.com/)


## 软件授权协议

Fixhub is licensed under [The MIT License (MIT)](LICENSE).
