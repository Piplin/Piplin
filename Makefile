.PHONY: dependency-install file-permission

dependency: dependency-install file-permission npm-install
install: app-install cache-config
update: dependency-install app-update dump-autoload cache-config

help:
	@echo 'make install -- download dependencies and install'
	@echo 'make update -- pull repo and rebuild assets without minifing'

dependency-install:
	composer install -o --no-dev

npm-install:
	npm install --production 

app-install:
	php artisan app:install

app-update:
	- php artisan app:update

file-permission:
	chmod -R 777 storage/
	chmod -R 777 bootstrap/cache/
	chmod -R 777 public/upload

migration:
	php artisan migrate --force

seed:
	php artisan db:seed

cs:
	vendor/bin/phpcs -p --standard=PSR2 --ignore="app/Helpers/Helpers.php,app/Presenters" app/
	vendor/bin/phpdoccheck --directory=app

dump-autoload:
	php artisan clear-compiled

cache-config:
	php artisan config:cache

# Create the databases for Travis CI
ifeq "$(DB)" "sqlite"
travis:
	@sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/g' .env
	@sed -i 's/DB_DATABASE=piplin//g' .env
	@sed -i 's/DB_USERNAME=travis//g' .env
	@touch $(TRAVIS_BUILD_DIR)/database/database.sqlite
else ifeq "$(DB)" "pgsql"
travis:
	@sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=pgsql/g' .env
	@sed -i 's/DB_USERNAME=piplin/DB_USERNAME=postgres/g' .env
	@psql -c 'CREATE DATABASE piplin;' -U postgres;
else
travis:
	@sed -i 's/DB_USERNAME=piplin/DB_USERNAME=travis/g' .env
	@sed -i 's/DB_PASSWORD=secret/DB_PASSWORD=/g' .env
	@mysql -e 'CREATE DATABASE piplin;'
endif
