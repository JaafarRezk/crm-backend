<?php

namespace App\Http\Resources;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

trait ApiResponseTrait
{

    protected function success($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $code);
    }


    protected function error(string $message = 'Error', int $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'status'  => 'error',
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        if ($code >= 500) {
            Log::error($message, ['errors' => $errors]);
        }

        return response()->json($response, $code);
    }


    protected function handleException(Throwable $e): JsonResponse
    {
        // Validation
        if ($e instanceof ValidationException) {
            return $this->validationError($e->errors(), $e->getMessage());
        }

        // Authorization (403)
        if ($e instanceof AuthorizationException) {
            return $this->forbidden($e->getMessage() ?: 'This action is unauthorized.');
        }

        // Model not found -> 404
        if ($e instanceof ModelNotFoundException) {
            return $this->notFound('Resource not found');
        }

        // HTTP exceptions (status codes)
        if ($e instanceof HttpExceptionInterface) {
            return $this->error(
                $e->getMessage() ?: 'HTTP Error',
                $e->getStatusCode()
            );
        }

        // Query/database errors -> 400 or 500
        if ($e instanceof QueryException) {
            Log::error('DB error: ' . $e->getMessage(), ['code' => $e->getCode()]);
            return $this->error('Database error', 500, config('app.debug') ? $e->getMessage() : null);
        }

        // fallback unexpected
        Log::error('Unhandled exception', ['exception' => $e]);
        return $this->error(
            'Unexpected server error',
            500,
            config('app.debug') ? $e->getMessage() : null
        );
    }


    protected function validationError(array $errors, string $message = 'Validation failed', int $code = 422): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'errors'  => $errors,
        ], $code);
    }


    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 401);
    }


    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403);
    }

    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, 404);
    }


    protected function conflict(string $message = 'Conflict detected'): JsonResponse
    {
        return $this->error($message, 409);
    }
}
