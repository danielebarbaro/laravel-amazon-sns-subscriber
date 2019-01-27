<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof MethodNotAllowedHttpException) {
            abort(JsonResponse::HTTP_METHOD_NOT_ALLOWED, 'Method not allowed');
        }
        if ($request->isJson() && $exception instanceof ValidationException) {
            return response()->json([
                'status' => 'error',
                'message' => [
                    'errors' => $exception->getMessage(),
                    'fields' => $exception->validator->getMessageBag()->toArray()
                ]
            ], JsonResponse::HTTP_PRECONDITION_FAILED);
        }

        if ($request->isJson() && $exception instanceof UnauthorizedHttpException) {
            $previous_exception = $exception->getPrevious();
            switch ($previous_exception) {
                case $previous_exception instanceof TokenExpiredException:
                case $previous_exception instanceof TokenInvalidException:
                case $previous_exception instanceof TokenBlacklistedException:
                    return response()->json([
                        'status' => 'error',
                        'message' => "{$exception->getMessage()}"
                    ], JsonResponse::HTTP_UNAUTHORIZED);
                    break;
                default:
                    return response()->json([
                        'status' => 'error',
                        'message' => "{$exception->getMessage()}"
                    ], JsonResponse::HTTP_UNAUTHORIZED);
                    break;
            }
        }

        return parent::render($request, $exception);
    }
}
