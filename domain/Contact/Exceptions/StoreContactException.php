<?php

namespace Domain\Contact\Exceptions;

use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StoreContactException extends HttpException
{
    /**
     * Constructor
     *
     * @param  array<mixed, mixed>  $responseBody
     */
    public function __construct(protected string $reason = '', ?\Throwable $previous = null)
    {
        parent::__construct(
            statusCode: Response::HTTP_INTERNAL_SERVER_ERROR,
            message: $reason,
            previous: $previous,
        );
    }

    /**
     * Report the exception.
     */
    public function report(): void
    {
        Log::warning('Store Contact Error', [
            'reason' => $this->getPrevious()->getMessage(),
            'status' => $this->getCode(),
        ]);
    }
}
