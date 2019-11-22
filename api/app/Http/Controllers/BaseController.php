<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BaseController extends Controller
{

    /**
     * Function for success response.
     *
     * @param $data data to be sent from controller
     */
    public function successResponse($data = [], $status = '')
    {
        $response = [];
        if (! empty($data)) {
            return response($data);
        } elseif (! empty($status)) {
            $statusResponse = [
                "status" => $status
            ];
            return response($statusResponse);
        } else {
            return response()->json(new \stdClass());
        }
    }

    /**
     * Function for fail response.
     *
     * @param string $message
     * @param number $httpCode
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function failResponse($message, $httpCode)
    {
        $errors = [
            'error' => $message
        ];

        return response($errors, $httpCode);
    }

    /**
     * Function for exception response.
     *
     * @param \Exception $exception
     */
    public function exceptionResponse($exception)
    {
        $exceptionMessage = $exception->getMessage() . " " . $exception->getFile() . " " . $exception->getLine();
        $errors = [
            'error' => $exceptionMessage
        ];
        
        Log::error($exceptionMessage);
        return response($errors, config('config.exception_code'));
    }
}
