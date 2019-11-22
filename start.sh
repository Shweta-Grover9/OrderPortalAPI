#!/bin/bash
docker-compose down -v

docker-compose build 

docker-compose up -d

docker exec order_php composer install


echo clear cache
docker exec order_php php artisan config:cache

docker exec order_php php artisan route:clear

docker exec order_php php artisan route:cache

echo view clear
docker exec order_php php artisan view:clear

echo creating tables

docker exec order_php php artisan migrate

echo running integration testcases
docker exec order_php ./vendor/bin/phpunit ./tests/Feature/

echo running unit testcases and creating code coverage file
 docker exec order_php ./vendor/bin/phpunit ./tests/Unit/



echo generating swagger documentation
docker exec order_php php artisan l5-swagger:generate

docker exec order_php chmod -R 777 ./

