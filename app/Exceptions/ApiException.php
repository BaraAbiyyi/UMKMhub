<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Basis exception domain yang selalu dijawab JSON dengan kode error stabil
 * (dipakai lewat render() sehingga tidak perlu try/catch di controller).
 */
abstract class ApiException extends Exception
{
    public function __construct(
        string $message,
        private string $errorCode,
        private int $status = 422,
    ) {
        parent::__construct($message);
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'code' => $this->errorCode,
            'message' => $this->getMessage(),
        ], $this->status);
    }
}
