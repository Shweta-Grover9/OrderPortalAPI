<?php
namespace App\Repositories;

use App\Models\Order;
use App\Models\MasterOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Libraries\RestAPILIbrary;
use App\Models\OrderDistance;
use App\Models\Distance;

class OrderRepository
{

    private $restApi;

    const UNASSIGNED_STATUS = 'UNASSIGNED';
    
    const TAKEN_STATUS = 'TAKEN';
    
    public function __construct(RestAPILIbrary $restApi)
    {
        $this->restApi = $restApi;
    }

    /**
     * Function to calculate Distance.
     *
     * @throws \Exception
     * @return string
     */
    public function calculateDistance($requestParams)
    {
        try {
            $url = config('config.GOOGLE_API_URL');

            $startLatitude = current($requestParams['origin']);
            $startLongitude = end($requestParams['origin']);
            $endLatitude = current($requestParams['destination']);
            $endLongitude = end($requestParams['destination']);

            $parameters = "origins=" . $startLatitude . "," . $startLongitude .
                          "&destinations=" . $endLatitude . "," . $endLongitude .
                          "&key=" . config('config.GOOGLE_APP_KEY');

            $headers = [];

            return $this->restApi->execute($url, $headers, $parameters);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to insert order records in database.
     *
     * @param array $requestParams
     * @param integer $distance
     */
    public function insertRecords($requestParams, $distance, $distanceData)
    {
        try {
            $orderId = DB::transaction(function () use ($requestParams, $distance, $distanceData) {
                if (empty($distanceData)) {
                    $distanceData = $this->insertDistanceData($requestParams, $distance);
                }
                $orderId = $this->insertOrderData($distanceData);
                return $orderId;
            });

            $orderResponse = new \stdClass();
            $orderResponse->id = $orderId;
            $orderResponse->distance = $distance;
            $orderResponse->status = self::UNASSIGNED_STATUS;
            return $orderResponse;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to insert order data.
     *
     * @return integer orderId
     */
    private function insertOrderData($distanceData)
    {
        $order = new Order();
        $order->status = self::UNASSIGNED_STATUS;
        $order->distance_id = $distanceData->id ?? 0;
        $order->save();

        return $order->id;
    }

    /**
     * Function to save distance for order.
     */
    private function insertDistanceData($requestParams, $distance)
    {
        $data = [
            'start_latitude' => current($requestParams['origin']) ?? 0.0,
            'start_longitude' => end($requestParams['origin']) ?? 0.0,
            'end_latitude' => current($requestParams['destination']) ?? 0.0,
            'end_longitude' => end($requestParams['destination']) ?? 0.0,
            'distance' => $distance,
        ];

        return Distance::create($data);
    }

    /**
     *
     * @param array $requestParams
     * @param integer $orderId
     */
    public function updateOrder($requestParams, $orderId)
    {
        try {
            $status = $requestParams['status'];

            // Handling of race condition
            $isUpdated = DB::transaction(function () use ($orderId, $status) {
                $order = Order::lockForUpdate()->where('id', $orderId)->where('status', self::UNASSIGNED_STATUS)->first();
                if (!empty($order)) {
                    $order->status = strtoupper($status);
                    return $order->save();
                } else {
                    return false;
                }
            });

            return $isUpdated;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to fetch order details.
     *
     * @param array $requestParams
     */
    public function fetchOrder($requestParams)
    {
        try {
            $page = $requestParams['page'];
            $limit = $requestParams['limit'];
            $offset = ($page - 1) * $limit;
            return Order::join('distance', 'distance.id', '=', 'orders.distance_id')
                        ->select('orders.id', 'distance', 'status')
                        ->offset($offset)
                        ->limit($limit)
                        ->orderBy('id')
                        ->get();
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to check whether order is taken.
     *
     * @param integer $orderId
     * @throws \Exception
     */
    public function isOrderTaken($order)
    {
        try {
            if (!empty($order->status) && $order->status == self::TAKEN_STATUS) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to check whether order exist.
     *
     * @param integer $orderId
     * @throws \Exception
     *
     */
    public function checkOrderExist($orderId)
    {
        try {
            return Order::find($orderId);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to check whether for particular latitude and longitude distance exist.
     *
     * @param array $requestParams
     * @throws \Exception
     */
    public function checkDistanceExistence($requestParams)
    {
        try {
            $startLatitude = current($requestParams['origin']) ?? '';
            $startLongitude = end($requestParams['origin']) ?? '';
            $endLatitude = current($requestParams['destination']) ?? '';
            $endLongitude = end($requestParams['destination']) ?? '';
            
            $distance = Distance::select('distance', 'id')->where('start_latitude', $startLatitude)
                                        ->where('start_longitude', $startLongitude)
                                        ->where('end_latitude', $endLatitude)
                                         ->where('end_longitude', $endLongitude)
                                         ->first();
            return $distance;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
