<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderIntegrationTest extends TestCase
{
    private static $orderId;
    
    public function setUp():void
    {
        parent::setUp();
    }
    
    /**
     * Test case for order creation success.
     *
     */
    public function testCreateOrderSuccess()
    {
        echo "\n <<<<<< Starting Integration Test Cases >>>>>> \n";
        
        echo "\n <<<<<< Create Order Positive Success >>>>>> \n";
        
        $requestParams =  [
            "origin" =>[
                 "24.9164",
                "79.5812"
            ],
            "destination" => [
               "28.4595",
               "77.0266"
            ]
        ];

        $response = $this->post('/orders', $requestParams);
        $responseJson = $response->getContent();
        $responseJson = json_decode($responseJson);
       
        self::$orderId = $responseJson->id;
        $response->assertJsonStructure(['id', 'distance', 'status']);
    }
    
    /**
     * Test case for order creation success.
     *
     */
    public function testCreateOrderDistanceExist()
    {
        echo "\n <<<<<< Create Order Distance Caching Scenario >>>>>> \n";
        
        $requestParams =  [
            "origin" =>[
                "24.9164",
                "79.5812"
            ],
            "destination" => [
                "28.4595",
                "77.0266"
            ]
        ];
        
        $response = $this->post('/orders', $requestParams);
        $responseJson = $response->getContent();
        $responseJson = json_decode($responseJson);
       
        $response->assertJsonStructure(['id', 'distance', 'status']);
    }
    
    /**
     * Test case when request is not complete.
     *
     * @dataProvider createOrderProvider
     *
     */
    public function testCreateOrderInvalidParameters($requestParams)
    {
        echo "\n <<<<<< Create Order Invalid parameters Scenario >>>>>> \n";
        
        $this->post('/orders', $requestParams)->assertJsonStructure([
            'errors' => [
                'origin',
            ]
        ])->assertStatus(config('config.http_fail_code'));
    }

    /**
     * Data provider for order invalid request.
     *
     * @return string[][][]|string[][][][]
     */
    public function createOrderProvider()
    {
        return [
            [
                "destination" => [
                    "28.4595",
                    "77.0266"
                ]
            ],
            [
                [
                    "origin" => [
                        "28.4595",
                    ]
                ],
                [
                    "destination" => [
                        "28.4595",
                        "77.0266"
                    ]
                ],
            ],
            [
                [
                    "origin" => [
                        "284595",
                    ]
                ],
                [
                    "destination" => [
                        "28.4595",
                        "77.0266"
                    ]
                ],
            ]
        ];
    }

    /**
     * Test case for update order.
     *
     */
    public function testUpdateOrderSuccess()
    {
        echo "\n <<<<<< Update Order Positive Success >>>>>> \n";
        
        $this->patch('/orders/'.self::$orderId, ["status" => "TAKEN"])
             ->assertJson(['status'=>'SUCCESS']);
    }

    /**
     * Test case for update order failure.
     *
     */
    public function testUpdateOrderTaken()
    {
        echo "\n <<<<<< Update Order Already Taken>>>>>> \n";
        
        $this->patch('/orders/'.self::$orderId, ["status" => "TAKEN"])
        ->assertJsonStructure(['error']);
    }

    /**
     * Test case for fetch order success.
     *
     */
    public function testFetchOrderSuccess()
    {
        echo "\n <<<<<< Fetch Order Positive Scenario>>>>>> \n";
        
        $requestParams =  [
            "limit" => 10,
            "page" => 1
        ];

        $this->json('GET', '/orders', $requestParams)->assertJsonStructure([['id', 'distance', 'status']]);
    }

    /**
     * Test case for fetch order failure.
     *
     */
    public function testFetchOrderInvalidRequestParameter()
    {
        echo "\n <<<<<< Fetch Order Invalid request parameters>>>>>> \n";
        
        $requestParams =  [
            "limit" => 10,
            "page" => 0
        ];

        $this->json('GET', '/orders', $requestParams)->assertJsonStructure([
            'errors' => [
                'page',
            ]]);
    }
    
    /**
     * Test case for update order.
     *
     */
    public function testUpdateOrderNotFound()
    {
        echo "\n <<<<<< Update Order Not found scenario>>>>>> \n";
        
        $this->patch('/orders/10000', ["status" => "TAKEN"])
        ->assertJsonStructure(['error']);
    }
    
    /**
     * Test case for update order.
     *
     */
    public function testUpdateOrderEmptyOrder()
    {
        echo "\n <<<<<< Update Order Empty Order>>>>>> \n";
        
        $this->patch('/orders/0', ["status" => "TAKEN"])
        ->assertJsonStructure(['error']);
    }
    
    /**
     * Test case for fetch order failure.
     *
     */
    public function testFetchOrderPageNotInRecords()
    {
        echo "\n <<<<<< Fetch order not in records>>>>>> \n";
        
        $requestParams =  [
            "limit" => 10,
            "page" => 1000
        ];
        
        $this->json('GET', '/orders', $requestParams)->assertJson([]);
    }
    
    /**
     * Test case for order creation when distance does not come from google api .
     *
     */
    public function testCreateOrderDistanceNotFound()
    {
        echo "\n <<<<<< in creating order , distance not found from given latitide and longitude>>>>>> \n";
        
        $requestParams =  [
            "origin" =>[
                "24.9164",
                "79.5812"
            ],
            "destination" => [
                "89.4595",
                "77.0266"
            ]
        ];
        
        $response = $this->post('/orders', $requestParams);
        $responseJson = $response->getContent();
        $responseJson = json_decode($responseJson);
        $response->assertJsonStructure(['error']);
    }
}
