<?php

class ErrorHandler {

    public static function handleException(Throwable $exception): void {
        new ApiResponse(500, $exception->getMessage(), null, null);
    }

    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
}
