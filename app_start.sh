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


curl -X POST http://localhost:8083/connectors -H "Accept:application/json" -H "Content-Type: application/json" -d '{
  "name": "identity-access-connector",
  "config": {
    "connector.class": "io.debezium.connector.mysql.MySqlConnector",
    "database.hostname": "identity_access_mysql",
    "database.port": "3306",
    "database.user": "user",
    "database.password": "password",
    "database.server.id": "184054",
    "database.include.list": "laravel_db",
    "table.include.list": "laravel_db.tbl_stored_event",
    "include.schema.changes": "true",
    "schema.history.internal.kafka.bootstrap.servers": "kafka:9092",
    "schema.history.internal.kafka.topic": "schema-changes.identity_access",
    "topic.prefix": "identity_access_eventstore",
    "database.server.name": "identity_access_db"
  }
}'