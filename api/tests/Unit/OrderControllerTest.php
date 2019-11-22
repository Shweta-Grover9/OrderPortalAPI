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
    public function testStoreOrderInvalid($requestParams)
    {
        echo "\n----Test case of invalid parameters in creation of order.---\n";
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
                    
                ]
            ],
            [
                [
                    'origin'=>''
                ]
            ],
            [
                [
                    'origin'=>[1],
                    'destination'=>[1]
                ]
            ],
            [
                [
                    'origin'=>["28.704060", "77.102493","77.102493"],
                    "destination"=> ["28.4595","114.182446"]
                ]
            ],
            [
                [
                    'origin'=> [28.704060, "77.102493"],
                    "destination"=> [28.4595,"114.182446"]
                ]
            ],
            [
                [
                    'origin'=>["128.704060", "277.102493"],
                    'destination'=>["128.704060", "277.102493"]
                ]
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
    public function testUpdateOrderScenario($orderId, $orderServiceResponse, $responseKey)
    {
        echo "\n----Test case of Update order positive and negative scenarios.---\n";
        
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
            [1, 409, 'error'],
            [1, 404, 'error'],
            [1, 500, 'error'],
            [0, 500, 'error'],
            [1, true, 'status'],
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
     * @dataProvider updateOrderProvider
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
     * 
     * @dataProvider showOrderProvider
     */
    public function testShowOrderInvalidParams($requestParams)
    {
        echo "\n----Test case of invalid request in fetching the order.---\n";
        
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
                    'page'=> 5
                ]
            ],
            [
                [
                    'limit'=> 5
                ]
            ],
            [
                [
                    'page'=> 'a',
                    'limit'=> 'a'
                ]
            ],
            [
                [
                    'page'=> 0,
                    'limit'=> 0
                ]
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
