<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public static function makeError($message, array $data = [])
    {
        $response = [
            'status' => false,
            'message' => $message,
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        return $response;
    }

    /**
     * @param  $error
     * @param  int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendError($error, $code = 404)
    {
        return Response::json($this->makeError($error), $code);
    }


    /**
     * @param string $message
     * @param mixed  $data
     *
     * @return array
     */
    public static function makeResponse($message, $data)
    {
        return [
            'status' => true,
            'data'    => $data,
            'message' => $message,
        ];
    }


    /**
     * @param  $result
     * @param  $message
     * @return mixed
     */
    public function sendResponse($result, $message, $code = 200)
    {
        return Response::json($this->makeResponse($message, $result), $code, [], JSON_UNESCAPED_UNICODE);
    }
}
