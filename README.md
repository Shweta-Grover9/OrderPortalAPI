# RESTful API for order backend system
Rest API for order creation, updation and fetching

## Docker, language, Framework, Database and server requirement

- [Docker](https://www.docker.com/) as the container service to isolate the environment.
- [Php](https://php.net/) to develop backend support.
- [Laravel](https://laravel.com/docs) is a stunningly fast PHP micro-framework for building web applications
- [MySQL](https://mysql.com/) as the database layer
- [Apache](https://httpd.apache.org/docs/) as a proxy layer

## How to Install & Run

1.  Clone the repo. `api` folder contains the complete application code.
2.  We have used the google distance matrix api for distance calculation and you need API key for the same. 
    Go to https://cloud.google.com/maps-platform/routes/ after login create new project and get the API key. 
    Update 'GOOGLE_API_KEY' in environment file located in ./api/.env file
3.  Run `./start.sh` to build docker containers, executing migration and PHPUnit test cases and        
    generating code coverage report
4.  After starting container following will be executed automatically:
	- Table migrations using artisan migrate command.
  - Unit and Integration test cases execution.

## Manually Migrating tables and Data Seeding

1. To run migrations manually use this command `docker exec order_php php artisan migrate`

## Manually Starting the docker and test Cases

1. You can run `docker-compose up` from terminal
2. Server is accessible at `http://localhost:8080`
3. Run manual testcase suite:
	- Integration Tests: `docker exec order_php php ./vendor/bin/phpunit ./tests/Feature/` &
	- Unit Tests: `docker exec order_php php ./vendor/bin/phpunit ./tests/Unit/`

## Coverage report

1. Open URL `http://localhost:8080/CodeCoverage/index.html` for code coverage report

## Swagger integration

1. Open URL `http://localhost:8080/api/documentation` for API demo
2. Here you can perform all order API operations like GET, UPDATE, POST

## Code Structure
api folder contains application code

**./tests**

- this folder contains test cases files written in UnitTest and Integration folders

**./app**

- contains all the configuration files, controllers, models, services, helpers and validators
- database/migrations folder contains the migration files
	- To manually run migrations use this command `docker exec order_php php artisan migrate`
- `OrderController` contains all the api's methods :
    1. localhost:8080/orders?page=1&limit=4 - GET url to fetch orders list with page and limit
    2. localhost:8080/orders - POST method to create new order with origin and destination
    3. localhost:8080/orders - PATCH method to update status for taken.
       (Handled simultaneous update request from multiple users at the same time by returning response status 422 if the order is already taken)
- `OrderService` contains the business logic.

**.env**

- config contains all project, database, session and custom configurations.
  We have set the GOOGLE_API_KEY here in the env file so that it is configurable. Set your GOOGLE_API_KEY here.

## API Reference Documentation

- `localhost:8080/orders?page=:page&limit=:limit` :

    GET Method - to fetch orders with page number and limit
    1. Header :
        - GET /orders?page=0&limit=5 HTTP/1.1
        - Host: localhost:8080
        - Content-Type: application/json

    2. Responses :

    ```
            - Response
            [
              {
                "id": 1,
                "distance": 46732,
                "status": "TAKEN"
              },
              {
                "id": 2,
                "distance": 46731,
                "status": "UNASSIGNED"
              },
              {
                "id": 3,
                "distance": 56908,
                "status": "UNASSIGNED"
              },
              {
                "id": 4,
                "distance": 49132,
                "status": "UNASSIGNED"
              },
              {
                "id": 5,
                "distance": 46732,
                "status": "UNASSIGNED"
              }
            ]
    ```

        Code                    Description
        - 200                   successful operation
        - 422                   Invalid Request Parameter
        - 500                   Internal Server Error

- `localhost:8080/orders` :

    POST Method - to create new order with origin and destination
    1. Header :
        - POST /orders HTTP/1.1
        - Host: localhost:8080
        - Content-Type: application/json

    2. Post-Data :
    ```
         {
            "origin" :["28.704060", "77.102493"],
            "destination" :["28.535517", "77.391029"]
         }
    ```

    3. Responses :
    ```
            - Response
            {
              "id": 44,
              "distance": 46732,
              "status": "UNASSIGNED"
            }
    ```

        Code                    Description
        - 200                   successful operation
        - 422                  Invalid Request Parameter

- `localhost:8080/orders/:id` :

    PATCH method to update status for taken.(Handled simultaneous update request from multiple users at the same time with response status 409)
    1. Header :
        - PATCH /orders/44 HTTP/1.1
        - Host: localhost:8080
        - Content-Type: application/json
    2. Post-Data :
    ```
         {
            "status" : "TAKEN"
         }
    ```

    3. Responses :
    ```
            - Response
            {
              "status": "SUCCESS"
            }
    ```

        Code                    Description
        - 200                   Successful operation
        - 422                   Invalid Request Parameter
        - 409                   Order already taken
        - 404                   Order not found
