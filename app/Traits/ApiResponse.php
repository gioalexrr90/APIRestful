<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait ApiResponse
{
    private function successResponse($data, int $code)
    {
        return response()->json($data, $code);
    }

    protected function errorRespose(string $message, int $code)
    {
        return response()->json(['error' => $message, 'code' => $code], $code);
    }

    protected function showAll(Collection $collection, int $code = 200)
    {
        return $this->successResponse(['data' => $collection], $code);
    }

    protected function showOne(Model $instance, int $code = 200)
    {
        return $this->successResponse(['data' => $instance], $code);
    }
}
