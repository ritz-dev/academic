<?php

namespace App\Http\Controllers;

use Exception;

abstract class Controller
{
    protected function handleException(Exception $e, string $customMessage = 'An error occurred')
    {
        // Handle specific exceptions
        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['message' => $customMessage ?: 'Resource not found'], 404);
        }

        if ($e instanceof \Illuminate\Validation\ValidationException) {
            return response()->json([
                'message' => $customMessage ?: 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        // Generic exception
        return response()->json([
            'message' => $customMessage,
            'error' => $e->getMessage()
        ], 500);
    }
}
