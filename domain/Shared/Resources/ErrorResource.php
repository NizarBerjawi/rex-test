<?php

namespace Domain\Shared\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @see https://app.swaggerhub.com/domains/smartbear-public/ProblemDetails/1.0.0
 * @see https://www.rfc-editor.org/rfc/rfc9457
 */
class ErrorResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = null;

    public function __construct(Throwable $resource, protected int $status)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof ValidationException) {
            return [
                'status' => $this->status,
                'title' => Response::$statusTexts[$this->status],
                'detail' => $this->resource->getMessage(),
                'errors' => $this->errors($this->resource),
            ];
        }

        return [
            'status' => $this->status,
            'title' => Response::$statusTexts[$this->status],
            'detail' => $this->resource->getMessage(),
        ];
    }

    /**
     * Convert Laravel validation errors into and error format that
     * matches rfc9457
     *
     * @return Collection<int, array{detail: string, pointer: string}>
     */
    public function errors(ValidationException $exception): Collection
    {
        return Collection::make($exception->errors())
            ->flatMap(
                fn (array $errors, $key) => Arr::map($errors, fn ($error) => [
                    'detail' => $error,
                    'pointer' => Str::of('#/')
                        ->append($key)
                        ->trim('.')
                        ->replace('.', '/')
                        ->toString(),
                ])
            );
    }
}
