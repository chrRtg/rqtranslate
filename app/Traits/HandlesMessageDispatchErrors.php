<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use React\Promise\PromiseInterface;
use Throwable;

trait HandlesMessageDispatchErrors
{
    /**
     * Execute a Discord message dispatch operation safely and log meaningful errors.
     *
     * @param callable():mixed $dispatch
     * @param string $operation
     * @param array<string,mixed> $context
     * @return mixed
     */
    protected function safeMessageDispatch(callable $dispatch, string $operation = 'dispatch', array $context = [])
    {
        try {
            $result = $dispatch();
        } catch (Throwable $exception) {
            $this->logMessageDispatchError($operation, $exception, $context);
            return null;
        }

        if ($result instanceof PromiseInterface || (is_object($result) && method_exists($result, 'otherwise'))) {
            return $result->otherwise(function (Throwable $exception) use ($operation, $context) {
                $this->logMessageDispatchError($operation, $exception, $context);
                return null;
            });
        }

        return $result;
    }

    /**
     * Log message dispatch errors in a consistent, meaningful format.
     */
    protected function logMessageDispatchError(string $operation, Throwable $exception, array $context = []): void
    {
        $payload = [
            'source' => static::class,
            'operation' => $operation,
            'error' => $exception->getMessage(),
            'context' => $context,
        ];

        Log::error('Discord message dispatch failed.', $payload);
    }
}
