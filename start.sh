#!/bin/bash

echo "----Set up the docker images and running the docker containers. -------"
docker-compose down -v && docker-compose build && docker-compose up -d

echo "-----Composer install----------"
docker exec order_php composer install 

docker exec order_php chmod -R 777 ./

docker exec order_php php artisan config:cache

docker exec order_php php artisan migrate

echo "-----Project Setup Commands-----"
docker exec order_php php artisan portal:setup





