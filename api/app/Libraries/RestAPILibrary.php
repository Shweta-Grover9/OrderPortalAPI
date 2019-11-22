<?php

namespace App\Libraries;

use Illuminate\Support\Facades\Log;

class RestAPILIbrary
{
    /**
     *Function to execute curl.
     *
     * @param string $url
     * @param array $headers
     * @param array $parameters
     * @throws \Exception
     */
    public function execute($url, $headers, $parameters)
    {
        try {
            //log request parameters
            Log::info('Distane API Request:---'.json_encode($parameters));
            
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $url = $url."?".$parameters;

            curl_setopt($ch, CURLOPT_URL, $url);
            
            //The number of seconds to wait while trying to connect
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
            
            //The maximum number of seconds to allow cURL functions to execute
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            
            $result = curl_exec($ch);

            if (curl_errno($ch)) {
                $errorMsg = curl_error($ch);
                Log::info('Distane API Error:---'.$errorMsg);
                throw new \Exception($errorMsg);
            }
            Log::info('Distane API Response:---'.$result);
            return $result;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
