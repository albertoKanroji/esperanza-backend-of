<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EntradasController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\SalidasController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('password/email', [PasswordResetController::class, 'sendResetLinkEmail']);
    Route::post('password/reset', [PasswordResetController::class, 'reset']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('users')->group(function () {
        Route::get('index', [UsersController::class, 'index']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('saldo', [UsersController::class, 'getSaldo']);
        Route::post('admins', [UsersController::class, 'getAdmins']);
        Route::post('employees', [UsersController::class, 'getEmployees']);
        Route::post('clients', [UsersController::class, 'getClients']);
        Route::post('soporte', [UsersController::class, 'getSupports']);
    });
    Route::prefix('qr')->group(function () {
        Route::post('entrada', [EntradasController::class, 'generateQrCode']);
        Route::post('salida', [SalidasController::class, 'generateQrForSalida']);
    });
    Route::prefix('clientes')->group(function () {
        Route::post('deudas', [UsersController::class, 'getProductsByClientWithDebtSum']);
        Route::post('pagados', [UsersController::class, 'getProductsByClientWithSum']);
        Route::post('pagar', [ProductsController::class, 'pagarDeuda']);

        Route::prefix('tarjetas')->group(function () {
            Route::post('crear', [ProductsController::class, 'addTarjetaToUser']);
            Route::post('detalle', [UsersController::class, 'getTarjetaDetail']);
            Route::post('listar', [UsersController::class, 'getUserTarjetas']);
        });
    });
    Route::prefix('producto')->group(function () {
        Route::post('detalle', [ProductsController::class, 'show']);
    });
    Route::prefix('historial')->group(function () {
        Route::post('transferencias', [ProductsController::class, 'show']);
        Route::post('pagos', [UsersController::class, 'getProductosSinDeuda']);
        Route::post('detalle-pago', [UsersController::class, 'getDetalleProducto']);
    });
});
