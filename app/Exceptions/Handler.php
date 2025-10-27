<?php
// app/Exceptions/Handler.php
namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Database\QueryException;

class Handler extends ExceptionHandler
{
    // Si quieres ocultar datos sensibles de validación:
    protected $dontFlash = ['current_password','password','password_confirmation'];

    public function register(): void
    {
        // Reportables: para enviar a Sentry/Rollbar, etc.
        $this->reportable(function (Throwable $e) {
            // ej: if(app()->bound('sentry')) app('sentry')->captureException($e);
        });

        // Manejo elegante de algunas excepciones:
        $this->renderable(function (ModelNotFoundException $e, $request) {
            return $this->respond($request, 'Recurso no encontrado', 404);
        });

        $this->renderable(function (QueryException $e, $request) {
            return $this->respond($request, 'Error de base de datos. Intenta de nuevo.', 500);
        });

        $this->renderable(function (ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message'=>'Datos inválidos','errors'=>$e->errors()], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        });
    }

    // Punto único para responder Web vs JSON
    protected function respond($request, string $message, int $status)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }
        // Web: usa tu layout con flash message (ya lo agregamos en el layout)
        return response()->view("errors.$status", ['message'=>$message], $status)
               ?: back()->with('error', $message);
    }

    // Si quieres sobreescribir completamente:
    public function render($request, Throwable $e)
    {
        // HttpException (403, 404, 500, etc.)
        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            return $this->respond($request, $e->getMessage() ?: 'Error', $status);
        }

        // En local, deja que APP_DEBUG muestre el stack trace
        if (config('app.debug')) {
            return parent::render($request, $e);
        }

        // En producción, mensaje genérico
        return $this->respond($request, 'Ha ocurrido un error inesperado.', 500);
    }
}
