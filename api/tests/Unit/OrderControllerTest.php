<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Http\Controllers\API\OrderController;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Requests\ShowOrderRequest;

class OrderControllerTest extends TestCase
{
    private $orderMockService;
    
    private $orderController;
    
    public function setUp():void
    {
        parent::setup();
        
        $this->orderMockService = \Mockery::mock('App\Services\OrderService');
        
        $this->orderController = $this->app->instance(OrderController::class,
            new OrderController($this->orderMockService)
                                                    );
    }
    
    /**
     * Testcase to create order failure.
     *
     *
     */
    public function testStoreOrderFailure()
    {
        echo "\n <<<<<< Starting Unit Test Cases >>>>>> \n";
        
        echo "\n----Failed test case in creation of order in Controller.---\n";
        
        $requestParams = [
            "origin"=> ["28.704060", "77.102493"],
            "destination"=>["28.535517", "77.391029"]
        ];
        
        $request = new CreateOrderRequest();
        $request = $request->replace($requestParams);
        $this->orderMockService->shouldReceive('createOrder')->andReturn([]);
        $response =  $this->orderController->store($request);
        $errorData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $errorData);
    }
    
    /**
     * Testcase to create order sucess.
     *
     */
    public function testStoreOrderSuccess()
    {
        echo "\n----Positive test case in creation of order in Controller.---\n";
        
        $requestParams = [
            "origin"=> ["28.704060", "77.102493"],
            "destination"=>["28.535517", "77.391029"]
        ];
        
        $createdOrder = [
            'id' => 1,
            'distance' => '52 m',
            'status' => 'UNASSIGNED'
        ];
        
        $request = new CreateOrderRequest();
        $request = $request->replace($requestParams);
        $this->orderMockService->shouldReceive('createOrder')->andReturn($createdOrder);
        $response =  $this->orderController->store($request);
        $actualResponse = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $actualResponse);
    }
    
    /**
     * Testcase to create order origin not given.
     *
     *@dataProvider storeOrderProvider
     *
     */
    public function testStoreOrderInvalid($requestParams, $comment)
    {
        echo "\n----Test case of invalid parameters in creation of order..---\n";
        
        echo "------".$comment."------\n";
        
        $response =  $this->call('POST', '/orders', $requestParams);
        $response->assertJsonStructure(['errors']);
    }
    
    /**
     * Data provider for store order provider.
     *
     * @return array[][][]|string[][][]|number[][][][]|string[][][][]
     */
    public function storeOrderProvider()
    {
        return [
            [
                [
                    'origin'=>[]
                    
                ],
                'Empty Array Origin'
            ],
            [
                [
                    'origin'=>''
                ],
                'Origin is not array'
            ],
            [
                [
                    'origin'=>[1],
                    'destination'=>[1]
                ],
                'Integer values are passed in request instead of string'
            ],
            [
                [
                    'origin'=>["28.704060", "77.102493","77.102493"],
                    "destination"=> ["28.4595","114.182446"]
                ],
                'Size of origin is not 2'
            ],
            [
                [
                    'origin'=> [28.704060, "77.102493"],
                    "destination"=> [28.4595,"114.182446"]
                ],
                'Float values are passed in origin and destination'
            ],
            [
                [
                    'origin'=>["128.704060", "277.102493"],
                    'destination'=>["128.704060", "277.102493"]
                ],
                'Latitude and longitude values are out of bounds'
            ],
        ];
    }
    
    /**
     * Testcase for create order exception.
     *
     */
    public function testCreateOrderException()
    {
        echo "\n----Test case of exception in creation of order.---\n";
        
        $requestParams = [
            "origin"=> ["28.704060", "77.102493"],
            "destination"=>["28.535517", "77.391029"]
        ];
        
        $request = new CreateOrderRequest();
        
        $request = $request->replace($requestParams);
        
        $this->orderMockService->shouldReceive('createOrder')->andThrow(new \Exception());
        $response =  $this->orderController->store($request);
        $errorData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $errorData);
    }
    
    /**
     * Test case for update order scenarios.
     * 
     * @dataProvider updateOrderProvider
     */
    public function testUpdateOrderScenario($orderId, $orderServiceResponse, $responseKey, $comment)
    {
        echo "\n----Test case of Update order positive and negative scenarios.---\n";
        
        echo "------".$comment."------\n";
        
        $request = new UpdateOrderRequest();
        $requestParams = [
            'status' => 'TAKEN'
        ];
        $request = $request->replace($requestParams);
        
        $this->orderMockService->shouldReceive('updateOrder')->andReturn($orderServiceResponse);
        $response = $this->orderController->update($orderId, $request);
        $errorData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey($responseKey, $errorData);
    }
    
    /**
     * Data provider for update order.
     * 
     * @return number[][]|string[][]|boolean[][]
     */
    public function updateOrderProvider()
    {
        return [
            [1, 409, 'error', 'Order already exist'],
            [1, 404, 'error', 'Order not found'],
            [1, 500, 'error', 'Order is able to changes its status'],
            [0, 500, 'error', 'Order id is not passed'],
            [1, true, 'status', 'Order status changed successfully'],
        ];
    }
    
    /**
     * Testcase for update order exception.
     *
     */
    public function testUpdateOrderException()
    {
        echo "\n----Test case of exception in taking the order.---\n";
        
        $requestParams = [
            'status' => 'TAKEN'
        ];
        
        $request = new UpdateOrderRequest();
        
        $request = $request->replace($requestParams);
        
        $this->orderMockService->shouldReceive('updateOrder')->andThrow(new \Exception());
        $response =  $this->orderController->update(1, $request);
        $errorData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $errorData);
    }
    
    /**
     * Test case for update order scenarios.
     *
     * 
     */
    public function testUpdateOrderInvalidParams()
    {
        echo "\n----Test case of invalid request in taking the order.---\n";
        
        
        $requestParams = [
            'status1' => 'TAKEN'
        ];
        
        $response =  $this->call('PATCH', '/orders/1', $requestParams);
        $response->assertJsonStructure(['errors']);
    }
    
    /**
     * test case for invalid parameters.
     * 
     * @dataProvider showOrderProvider
     */
    public function testShowOrderInvalidParams($requestParams, $comment)
    {
        echo "\n----Test case of invalid request in fetching the order.---\n";
        
        echo "------".$comment."------\n";
        
        $response =  $this->call('GET', '/orders', $requestParams);
        $response->assertJsonStructure(['errors']);
    }
    
    /**
     * 
     * @return number[][]|string[][]
     */
    public function showOrderProvider()
    {
        return [
            [
                [
                    'page'=> 5,
                ],
                'Limit is not passed in fetching order'
            ],
            [
                [
                    'limit'=> 5,
                    
                ],
                'Page is not passed in fetching order'
            ],
            [
                [
                    'page'=> 'a',
                    'limit'=> 'a'
                ],
                'Format of page and limit are not correct in fetching order'
            ],
            [
                [
                    'page'=> 0,
                    'limit'=> 0
                ],
                '0 is passed in page and limit'
            ]
        ];
    }
    
    public function testShowOrderSucceess()
    {
        echo "\n----Test case of positive scenario in fetching the order.---\n";
        
        $requestParams = [
            'page'=>1,
            'limit'=>1
        ];
        
        $orderServiceResponse = [
         [
                "id" => 1,
                "distance" => "632663 m",
                "status" => "TAKEN"
            ]
        ];
        
        $request = new ShowOrderRequest();
        $request = $request->replace($requestParams);
        $this->orderMockService->shouldReceive('fetchOrder')->andReturn($orderServiceResponse);
        $actualResponse = $this->orderController->show($request);
        $actualResponse = json_decode($actualResponse->getContent(), true);
        foreach($actualResponse as $response) {
            $this->assertArrayHasKey("id", $response);
        }
    }
    
    /**
     * Testcase for update order exception.
     *
     */
    public function testShowOrderException()
    {
        echo "\n----Test case of exception in fetching the order.---\n";
        
        $requestParams = [
            'page'=>1,
            'limit'=>1
        ];
        
        $request = new ShowOrderRequest();
        
        $request = $request->replace($requestParams);
        
        $this->orderMockService->shouldReceive('fetchOrder')->andThrow(new \Exception());
        $response =  $this->orderController->show($request);
        $errorData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $errorData);
    }
    
    public function tearDown():void
    {
        parent::tearDown();
        
        \Mockery::close();
    }
}
