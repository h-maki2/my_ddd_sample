#!/bin/bash

if [ ! -f "identity_access/src/.env" ]; then 
  if [ -f "identity_access/src/.env.example" ]; then
    cp identity_access/src/.env.example identity_access/src/.env
  fi
fi

if [ ! -f "task_management/src/.env" ]; then 
  if [ -f "task_management/src/.env.example" ]; then
    cp task_management/src/.env.example task_management/src/.env
  fi
fi

docker compose up --build -d

sleep 15

docker exec identity_access chmod -R 775 /var/www/html/identity_access/src/storage

docker exec identity_access chmod -R 775 /var/www/html/identity_access/src/bootstrap/cache

docker exec identity_access chown -R www-data:www-data /var/www/html/identity_access/src/storage

docker exec identity_access chown -R www-data:www-data /var/www/html/identity_access/src/bootstrap/cache

docker exec identity_access bash -c "cd /var/www/html/identity_access/src && composer install"

docker exec identity_access bash -c "cd /var/www/html/identity_access/src && php artisan key:generate"

docker exec identity_access bash -c "cd /var/www/html/identity_access/src && php artisan migrate"


docker exec task_management chmod -R 775 /var/www/html/task_management/src/storage

docker exec task_management chmod -R 775 /var/www/html/task_management/src/bootstrap/cache

docker exec task_management chown -R www-data:www-data /var/www/html/task_management/src/storage

docker exec task_management chown -R www-data:www-data /var/www/html/task_management/src/bootstrap/cache

docker exec task_management bash -c "cd /var/www/html/task_management/src && composer install"

docker exec task_management bash -c "cd /var/www/html/task_management/src && php artisan key:generate"

docker exec task_management bash -c "cd /var/www/html/task_management/src && php artisan migrate"