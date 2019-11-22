<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\Collection;

class OrderServiceTest extends TestCase
{
    private $mockOrderRepo;

    private $orderService;

    public function setUp():void
    {
        parent::setUp();

        $this->mockOrderRepo = \Mockery::mock('App\Repositories\OrderRepository');

        $this->orderService = new OrderService($this->mockOrderRepo);
    }

    /**
     * Testcase to create order.
     *
     *@dataProvider createOrderProvider
     */
    public function testCreateOrder($distanceData)
    {
        echo "\n----Test case of success in creating the order (OrderService).---\n";
        
        $distanceResult = '{
           "destination_addresses": [
              "296/39, Fauladi Kalam Marg, Chatgiri Colony, Chetgiri Colony, Narayanpura, Chhatarpur, Madhya Pradesh 471001, India"
           ],
           "origin_addresses": [
              "2194, G Block, Jacobpura, Sector 57, Gurugram, Haryana 122001, India"
           ],
           "rows": [
              {
                 "elements": [
                    {
                       "distance": {
                          "text": "635 km",
                          "value": 634923
                       },
                       "duration": {
                          "text": "11 hours 11 mins",
                          "value": 40246
                       },
                       "status": "OK"
                    }
                 ]
              }
           ],
           "status": "OK"
        }';

        $requestParams = [
                          "origin" =>  ["28.4595","77.0266"],
                          "destination" => ["24.9164", "79.5812"]
                         ];
        $orderObject = new \stdClass();
        $orderObject->id = 1;
        $orderObject->distance = 123;
        $orderObject->status = 'unassigned';
        $this->mockOrderRepo->shouldReceive('checkDistanceExistence')->andReturn($distanceData);
        $this->mockOrderRepo->shouldReceive('calculateDistance')->andReturn($distanceResult);
        $this->mockOrderRepo->shouldReceive('insertRecords')->andReturn($orderObject);
        $response = $this->orderService->createOrder($requestParams);
        $this->assertArrayHasKey('id', $response);
        
    }

    /**
     * Testcase for create order exception.
     *
     * @dataProvider exceptionProvider
     *
     */
    public function testOrderException($request, $function)
    {
        echo "\n----Test case of exception in creating and fetching the order (OrderService).---\n";
        
        $this->expectException(\Exception::class);

        $this->orderService->$function($request);
    }
    
    
    /**
     * TestCase for update order where order already taken.
     *
     */
    public function testUpdateOrderAlreadyTaken()
    {
        echo "\n----Test case of order already updated (OrderService).---\n";
        
        $orderId = 1;
        $requestParams = [
            'status' => 'TAKEN'
        ];
        $isOrdertaken = true;
        $this->mockOrderRepo->shouldReceive('checkOrderExist')->andReturn(true);
        $this->mockOrderRepo->shouldReceive('isOrderTaken')->andReturn($isOrdertaken);
        $response = $this->orderService->updateOrder($requestParams, $orderId);
        $this->assertIsInt($response);
    }

    /**
     * TestCase for update order where order already taken.
     *
     */
    public function testUpdateOrderNotExist()
    {
        echo "\n----Test case of order not found (OrderService).---\n";
        
        $orderId = 1;
        $requestParams = [
            'status' => 'TAKEN'
        ];
        $this->mockOrderRepo->shouldReceive('checkOrderExist')->andReturn(false);
        $response = $this->orderService->updateOrder($requestParams, $orderId);
        $this->assertIsInt($response);
    }

    /**
     * TestCase for update order Success.
     *
     */
    public function testUpdateOrderSuccess()
    {
        echo "\n----Test case of success in updating the order (OrderService).---\n";
        
        $orderId = 1;
        $requestParams = [
            'status' => 'TAKEN'
        ];
        $isOrdertaken = null;
        $orderUpdated = true;
        $this->mockOrderRepo->shouldReceive('checkOrderExist')->andReturn(true);
        $this->mockOrderRepo->shouldReceive('isOrderTaken')->andReturn($isOrdertaken);
        $this->mockOrderRepo->shouldReceive('updateOrder')->andReturn($orderUpdated);
        $response = $this->orderService->updateOrder($requestParams, $orderId);
        $this->assertTrue($response);
    }

    /**
     * Testcase for update order exception.
     *
     */
    public function testUpdateOrderException()
    {
        echo "\n----Test case in exception of order (OrderService).---\n";
        
        $this->expectException(\Exception::class);
        $orderId = 1;
        $requestParams = [
            'status' => 'TAKEN'
        ];

        $this->orderService->updateOrder($requestParams, $orderId);
    }

    /**
     * TestCase for fetch order success.
     *
     */
    public function testFetchOrderSuccess()
    {
        echo "\n----Test case of successfully fetching of orders (OrderService).---\n";
        
        $requestParams = [
            'page'=>1,
            'limit' => 10
        ];

        $order = new \stdClass();
        $order->id = 1;
        $order->status = "unassigned";
        $order->distance = 123;

        $orderCollection = new Collection();

        $orderCollection->add($order);
      
        $this->mockOrderRepo->shouldReceive('fetchOrder')->andReturn($orderCollection);
        $actualResponse = $this->orderService->fetchOrder($requestParams);
        foreach($actualResponse as $response) {
            $this->assertArrayHasKey("id", $response);
        }
    }

    /**
     * TestCase for fetch order fail.
     *
     */
    public function testFetchOrderFail()
    {
        echo "\n----Test case in failure of  fetching of orders (OrderService).---\n";
        
        $requestParams = [
            'page'=>1,
            'limit' => 10
        ];

        $orderCollection = null;
        $this->mockOrderRepo->shouldReceive('fetchOrder')->andReturn($orderCollection);
        $actualResponse = $this->orderService->fetchOrder($requestParams);
        $this->assertEmpty($actualResponse);
    }

    /**
     * Provider for exception testcases.
     *
     * @return string[][]|number[][][]|string[][][][]
     */
    public function exceptionProvider()
    {
        $createOrderRequest = [
            "origin" =>  ["28.4595","77.0266"],
            "destination" => ["24.9164", "79.5812"]
        ];

        $fetchOrderRequest = [
            'page'=>1,
            'limit' => 10
        ];

        return [
            [$createOrderRequest, 'createOrder'],
            [$fetchOrderRequest, 'fetchOrder']
        ];
    }

    /**
     * Testcase for create order failure.
     *
     */
    public function testEmptyFormulateResult()
    {
        echo "\n----Test case to cover distance caching (OrderService).---\n";
        
        $distanceResult = '{
           "destination_addresses": [
              "296/39, Fauladi Kalam Marg, Chatgiri Colony, Chetgiri Colony, Narayanpura, Chhatarpur, Madhya Pradesh 471001, India"
           ],
           "origin_addresses": [
              "2194, G Block, Jacobpura, Sector 57, Gurugram, Haryana 122001, India"
           ],
           "rows": [
              {
                 "elements": [
                    {
                       "distance": {
                          "text": "635 km",
                          "value": 634923
                       },
                       "duration": {
                          "text": "11 hours 11 mins",
                          "value": 40246
                       },
                       "status": "OK"
                    }
                 ]
              }
           ],
           "status": "OK"
        }';

        $requestParams = [
            "origin" =>  ["28.4595","77.0266"],
            "destination" => ["24.9164", "79.5812"]
        ];
        $orderObject = null;
        $this->mockOrderRepo->shouldReceive('checkDistanceExistence')->andReturn(null);
        $this->mockOrderRepo->shouldReceive('calculateDistance')->andReturn($distanceResult);
        $this->mockOrderRepo->shouldReceive('insertRecords')->andReturn($orderObject);
        $response = $this->orderService->createOrder($requestParams);
        $this->assertFalse($response);
    }
    
    /**
     * Provider for create order testcase.
     * 
     * @return NULL[]|\stdClass[]
     */
    public function createOrderProvider()
    {
        $distanceObj = new \stdClass();
        $distanceObj->distance = 1;
        return [
            [null], 
            [$distanceObj]
        ];
    }
    
    public function tearDown():void
    {
        parent::tearDown();
        
        \Mockery::close();
    }
}
