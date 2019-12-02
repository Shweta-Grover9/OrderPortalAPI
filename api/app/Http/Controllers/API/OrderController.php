<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Http\Controllers\BaseController;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Requests\ShowOrderRequest;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Order APIs",
 *      description="Order API description"
 * )
 */
class OrderController extends BaseController
{
    private $orderService;

    /**
     * Constructor to initailze.
     *
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * @OA\Post(
     *      path="/orders",
     *      operationId="createOrders",
     *      tags={"Orders"},
     *      summary="Create orders",
     *      description="Create orders",
     *      @OA\RequestBody(
     *             @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="origin",
     *                     type="array",
     *                     @OA\Items(
     *                          type="string"
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="destination",
     *                     type="array",
     *                      @OA\Items(
     *                          type="string"
     *                     )
     *                 ),
     *                  example={"origin": {"28.6746", "77.1802"}, "destination": {"28.4595", "77.0266"}}
     *             )
     *           )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="{'id':25,'distance':'634923 m','status':'UNASSIGNED'}"
     *       ),
     *       @OA\Response(response=406, description="Unable to create orders"),
     *     )
     *
     */
    public function store(CreateOrderRequest $request)
    {
        try {
            $requestParams = $request->all();

            $createdOrder = $this->orderService->createOrder($requestParams);

            if (!empty($createdOrder)) {
                return $this->successResponse($createdOrder);
            } else {
                return $this->failResponse(__('message.order_failed'), config('config.http_fail_code'));
            }
        } catch (\Exception $exception) {
            return $this->exceptionResponse($exception);
        }
    }

    /**
     * @OA\Patch(
     *      path="/orders/{id}",
     *      operationId="takeOrders",
     *      tags={"Orders"},
     *      summary="Take orders",
     *      description="Take orders",
     *       @OA\Parameter(
     *              name="id",
     *              description="Order id",
     *              required=true,
     *              in="path",
     *              @OA\Schema(
     *                  type="integer"
     *              )
     *      ),
     *     @OA\RequestBody(
     *             @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="status",
     *                     type="string",
     *                 ),
     *                 example={"status": "TAKEN"}
     *             )
     *           )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="{""status"":""SUCCESS""}"
     *       ),
     *       @OA\Response(response=406, description="{""error"":""Order is already taken""}"),
     *     )
     *
     */
    public function update($orderId, UpdateOrderRequest $request)
    {
        try {
            if (!empty($orderId)) {
                $requestParams = $request->all();

                $isUpdated = $this->orderService->updateOrder($requestParams, $orderId);

                if ($isUpdated === true) {
                    return $this->successResponse([], __('message.success'));
                } elseif ($isUpdated == config('config.order_taken_code')) {
                    return $this->failResponse(__('message.order_already_taken'), config('config.order_taken_code'));
                } elseif ($isUpdated == config('config.not_found')) {
                    return $this->failResponse(__('message.order_not_found'), config('config.not_found'));
                } else {
                    return $this->failResponse(__('message.order_update_failed'), config('config.http_fail_code'));
                }
            } else {
                return $this->failResponse(__('message.order_update_failed'), config('config.http_fail_code'));
            }
        } catch (\Exception $exception) {
            return $this->exceptionResponse($exception);
        }
    }

    /**
     * @OA\Get(
     *      path="/orders",
     *      operationId="showOrders",
     *      tags={"Orders"},
     *      summary="Get list of orders",
     *      description="Returns list of orders",
     *           @OA\Parameter(
     *              name="page",
     *              description="Page for order listing",
     *              required=true,
     *              in="query",
     *              @OA\Schema(
     *                  type="integer"
     *              )
     *      ),
     *      @OA\Parameter(
     *              name="limit",
     *              description="Limit of records per page",
     *              required=true,
     *              in="query",
     *              @OA\Schema(
     *                  type="integer"
     *              )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="[{""id"": 1,""distance"": 15,""status"": ""unassigned""}]"
     *       ),
     *       @OA\Response(response=406, description="Unable to fetch orders"),
     *     )
     *
     */
    public function show(ShowOrderRequest $request)
    {
        try {
            $requestParams  = $request->all();
            $orders = $this->orderService->fetchOrder($requestParams);
            return $this->successResponse($orders);
        } catch (\Exception $exception) {
            return $this->exceptionResponse($exception);
        }
    }
}
