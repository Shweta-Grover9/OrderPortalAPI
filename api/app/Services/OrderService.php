<?php
namespace App\Services;

use App\Repositories\OrderRepository;

class OrderService
{

    private $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Function to create order.
     *
     * @param array $requestParams
     * @throws \Exception
     */
    public function createOrder($requestParams)
    {
        try {
            $distance = 0;
            //first check whether distance already exist for origin and destination
            $distanceData = $this->orderRepository->checkDistanceExistence($requestParams);
            
            if (empty($distanceData)) {
                //hit google api to find distance
                $distanceResult = $this->orderRepository->calculateDistance($requestParams);
                $distance = $this->findDistance($distanceResult);
            } else {
                $distance = $distanceData->distance ?? 0;
            }
            //insert recrds in db
            if ($distance!= 0) {
                $createdOrder = $this->orderRepository->insertRecords($requestParams, $distance, $distanceData);
                return $this->formulateResult($createdOrder);
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to update order status.
     *
     * @param array $requestParams
     * @param integer $orderId
     * @throws \Exception
     * @return
     */
    public function updateOrder($requestParams, $orderId)
    {
        try {
            $isOrderExist = $this->orderRepository->checkOrderExist($orderId);

            if (! empty($isOrderExist)) {
                $isOrderTaken = $this->orderRepository->isOrderTaken($orderId);
                if (empty($isOrderTaken)) {
                    return $this->orderRepository->updateOrder($requestParams, $orderId);
                } else {
                    return config('config.order_taken_code');
                }
            } else {
                return config('config.not_found');
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to fetch order data.
     *
     * @param array $requestParams
     * @throws \Exception
     *
     */
    public function fetchOrder($requestParams)
    {
        try {
            $orders = $this->orderRepository->fetchOrder($requestParams);
            if (! empty($orders)) {
                $result = $orders->map(function ($order) {
                    return $this->formulateResult($order);
                });

                return $result->toArray();
            } else {
                return [];
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to calculate distance.
     *
     * @param json $distanceResult
     * @return number
     */
    private function findDistance($distanceResult)
    {
        $distance = 0;
        $distanceResult = json_decode($distanceResult, true);

        if (isset($distanceResult['rows'])) {
            $distanceRow = current($distanceResult['rows']);
            if (isset($distanceRow['elements'])) {
                $distanceElements = current($distanceRow['elements']);
                if (isset($distanceElements['distance']['value'])) {
                    $distance = $distanceElements['distance']['value'];
                    $distance = intval($distance);
                }
            }
        }
        return $distance;
    }

    /**
     * Function to formulate result.
     *
     * @param Model $order
     */
    private function formulateResult($order)
    {
        if (! empty($order)) {
            return [
                'id' => $order->id,
                'distance' => $order->distance . " " . config('config.order_converting_unit'),
                'status' => $order->status
            ];
        } else {
            return false;
        }
    }
}
