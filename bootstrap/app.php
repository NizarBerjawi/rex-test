<?php

use Domain\Shared\Resources\ErrorResource;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        api: __DIR__.'/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // We force Laravel to always return json responses for the API
        $exceptions->shouldRenderJsonWhen(fn (Request $request, Throwable $e) => $request->is('api/*'));

        $exceptions->render(
            function (Throwable $e, Request $request) {
                $status = match (true) {
                    $e instanceof HttpException => $e->getStatusCode(),
                    $e instanceof ValidationException => $e->status,
                    // We can add any other exceptions over here
                    // so we can convert all of them to the same
                    // response structure with additional details
                    default => Response::HTTP_INTERNAL_SERVER_ERROR
                };

                return ErrorResource::make($e, $status)
                    ->toResponse($request)
                    ->setStatusCode($status);
            });
    })->create();
