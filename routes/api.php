<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EntradasController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\SalidasController;
use App\Http\Controllers\ContenedorController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí puedes registrar las rutas de la API para tu aplicación. Estas rutas
| son cargadas por el RouteServiceProvider dentro de un grupo que
| se asigna al grupo de middleware "api". ¡Disfruta construyendo tu API!
|
*/

Route::post('saldo', [UsersController::class, 'getSaldo']);

// Rutas de autenticación
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('password/email', [PasswordResetController::class, 'sendResetLinkEmail']);
    Route::post('password/reset', [PasswordResetController::class, 'reset']);
});

    Route::put('/contenedor/{id}', [ContenedorController::class, 'update']);
    Route::put('/contenedorStatus/{id}', [ContenedorController::class, 'updateContenedorStatus']);

// Rutas protegidas por autenticación
Route::middleware('auth:sanctum')->group(function () {

    // Rutas relacionadas con usuarios
    Route::prefix('users')->group(function () {
        Route::get('index', [UsersController::class, 'index']);
        Route::get('porRfc/{rfc}', [UsersController::class, 'porRfc']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('saldo', [UsersController::class, 'getSaldo']);
        Route::post('admins', [UsersController::class, 'getAdmins']);
        Route::post('employees', [UsersController::class, 'getEmployees']);
        Route::post('clients', [UsersController::class, 'getClients']);
        Route::post('soporte', [UsersController::class, 'getSupports']);
    });

    // Rutas para generación de códigos QR
    Route::prefix('qr')->group(function () {
        Route::post('entrada', [EntradasController::class, 'generateQrCode']);
        Route::post('salida', [SalidasController::class, 'generateQrForSalida']);
    });

    // Rutas relacionadas con clientes
    Route::prefix('clientes')->group(function () {
        Route::post('deudas', [UsersController::class, 'getProductsByClientWithDebtSum']);
        Route::post('pagados', [UsersController::class, 'getProductsByClientWithSum']);
        Route::post('pagar', [ProductsController::class, 'pagarDeuda']);

        // Rutas para gestión de tarjetas
        Route::prefix('tarjetas')->group(function () {
            Route::post('crear', [UsersController::class, 'addTarjetaToUser']);
            Route::post('detalle', [UsersController::class, 'getTarjetaDetail']);
            Route::post('listar', [UsersController::class, 'getUserTarjetas']);
        });
    });

    // Rutas relacionadas con productos
    Route::prefix('producto')->group(function () {
        Route::post('detalle', [ProductsController::class, 'show']);
    });

    // Rutas de historial
    Route::prefix('historial')->group(function () {
        Route::post('transferencias', [ProductsController::class, 'show']);
        Route::post('pagos', [UsersController::class, 'getProductosSinDeuda']);
        Route::post('detalle-pago', [UsersController::class, 'getDetalleProducto']);
    });

    // Ruta para actualizar contenedores


});
