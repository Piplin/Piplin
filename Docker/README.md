# Piplin - Docker
## 要求
Docker Engine release 17.04.0+
>请在项目主目录运行，非 Docker 子目录

## 构建
```
tar -zcvf Docker/web/src.tar.gz --exclude=Docker --exclude=.git .
cd Docker
docker-compose -p piplin build
rm -f web/src.tar.gz
```

## 初始化运行
>仅需构建后第一次运行时执行
```
docker-compose -p piplin up -d
docker-compose -p piplin exec web php artisan key:generate
docker-compose -p piplin exec web php artisan config:cache
docker-compose -p piplin exec web php artisan migrate
docker-compose -p piplin exec web php artisan db:seed
docker-compose -p piplin exec web supervisorctl restart all
```

## 访问
http://piplin.app
>需要预先配 hosts 到 127.0.0.1 ，可通过修改下面2个文件中的相关配置进行域名变更（建议在初始化之前修改）
```
Docker/web/nginx/piplin.template
Docker/env
```

## 维护
停止容器

`docker-compose -p piplin stop`

启动容器

`docker-compose -p piplin start`

查看容器日志

`docker-compose -p piplin logs`

进入 web 容器

`docker-compose -p piplin exec web bash`

## 清理
>!!!注意以下操作会清理全部 Piplin Docker 数据，一般用于重新部署时用!!!
```
docker-compose -p piplin down --volumes
docker rmi -f piplin_web
```
