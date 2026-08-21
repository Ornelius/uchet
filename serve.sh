#!/usr/bin/env bash
# Dev-сервер с увеличенными лимитами загрузки файлов (см. php-dev.ini)
cd "$(dirname "$0")/public"
exec php -c ../php-dev.ini -S 127.0.0.1:8000 ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
