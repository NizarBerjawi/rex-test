<?php

namespace Domain\Contact\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class ContactCallException extends Exception
{
    /**
     * Constructor
     *
     * @param  array<mixed, mixed>  $responseBody
     */
    public function __construct(protected string $reason = '', ?\Throwable $previous = null)
    {
        parent::__construct(
            message: $reason,
            previous: $previous,
        );
    }

    /**
     * Report the exception.
     */
    public function report(): void
    {
        Log::warning('Contact call Error', [
            'reason' => $this->getPrevious()->getMessage(),
            'status' => $this->getCode(),
        ]);
    }
}
