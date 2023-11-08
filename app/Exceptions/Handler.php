<?php

namespace App\Exceptions;

use Exception;
use GuzzleHttp\Psr7\Response;
use Illuminate\Auth\AuthenticationException;
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

        $this->renderable(function (Exception $e, Request $request) {

            if ($e instanceof AuthenticationException) {
                return $this->unauthenticated($request, $e);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json(['message' => $e->getMessage()], 403);
            }

            if ($e instanceof ValidationException) {
                return $this->convertValidationExceptionToResponse($e, $request);
            }

            /*  if ($request->is('api/*')) {

                if ($e->getPrevious() instanceof ModelNotFoundException) {
                    return response()->json([
                        'message' => $e->getMessage(),
                    ], 404);
                }

                if ($e instanceof NotFoundHttpException) {
                    return response()->json(['message' => $e->getMessage()], 404);
                }

                return response()->json([
                    'message' => $e->getMessage(),
                ], 404);
            } */
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        });
    }
}
