<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Exception $e, Request $request) {

            if ($e instanceof ValidationException) {
                return $this->convertValidationExceptionToResponse($e, $request);
            }

            if ($e instanceof ModelNotFoundException) {
                $model = $e->getModel();
                return response()->json([
                    'message' => 'Model or instance ' . $model . 'not found',
                ], 404);
            }

            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Record not found',
                ], 404);
            }
        });
    }
}
