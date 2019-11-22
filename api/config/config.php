<?php

return [
    'taken_order_status' => 'TAKEN',
    'order_converting_parameter' => 1000,
    'order_converting_unit' => 'm',
    'http_fail_code' => 422,
    'exception_code' => 500,
    'latitude_regex' => '/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/',
    'longitude_regex' => '/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/',
    'order_taken_code' => 409,
    'GOOGLE_API_URL' => env('GOOGLE_API_URL'),
    'GOOGLE_APP_KEY' => env('GOOGLE_APP_KEY'),
    'not_found' => 404
];