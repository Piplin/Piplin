FROM centos:6.9
MAINTAINER Sheaven <sheaven@qq.com>

RUN mkdir -p /var/www/piplin
WORKDIR /var/www/piplin
# 添加 yum 源并替换国内镜像，如不需要可以注释下面的 repo 文件的 COPY 命令
COPY repo/CentOS-Base.repo /etc/yum.repos.d/
RUN set -xe; \
    yum install -y epel-release
COPY repo/epel.repo /etc/yum.repos.d/
# RUN set -xe; \
#    yum -y install https://centos6.iuscommunity.org/ius-release.rpm
# 官方 ius 源安装包下载较慢，使用国内镜像
RUN set -xe; \
    yum -y install https://mirrors.tuna.tsinghua.edu.cn/ius/stable/CentOS/6/x86_64/ius-release-1.0-15.ius.centos6.noarch.rpm
COPY repo/ius.repo /etc/yum.repos.d/
RUN set -xe; \
    curl --silent --location https://rpm.nodesource.com/setup_6.x| bash -
COPY repo/nodesource-el.repo /etc/yum.repos.d/

# 更改时区
RUN set -xe; \
    ln -sf /usr/share/zoneinfo/Asia/Shanghai /etc/localtime \
    && echo "Asia/Shanghai" > /etc/timezone

# 安装环境
RUN set -xe; \
    yum install -y git2u php71u-fpm-nginx php71u-cli php71u-pecl-redis php71u-mysqlnd php71u-json php71u-gd php71u-mbstring php71u-mcrypt php71u-opcache nodejs python27-pip crontabs

# 清理 yum 缓存
RUN set -xe; \
    yum clean all

# 安装 composer
RUN set -xe; \
    php -r "copy('https://install.phpcomposer.com/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && rm -f composer-setup.php \
    && mv composer.phar /usr/local/bin/composer \
    && composer config -g repo.packagist composer https://packagist.phpcomposer.com \
    && composer self-update

# 复制项目代码
ADD src.tar.gz /var/www/piplin/

# 安装项目依赖
RUN set -xe; \
    cd /var/www/piplin \
    && composer install -o \
    && npm install --production --registry=https://registry.npm.taobao.org \
    && make file-permission

# 安装 supervisor 3
RUN set -xe; \
    pip2.7 install supervisor -i https://pypi.douban.com/simple \
    && mkdir -p /etc/supervisor/conf.d \
    && mkdir -p /var/log/supervisor
COPY supervisor/supervisord.conf /etc/supervisor/

# 安装计划任务
COPY crontab /var/spool/cron/root

EXPOSE 80

CMD ["/usr/bin/supervisord","-c","/etc/supervisor/supervisord.conf"]
