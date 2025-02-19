<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EntradasController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SalidasController;
use App\Http\Controllers\ContenedorController;
use App\Http\Controllers\PaymentHistoryController;
use App\Http\Controllers\ProfilePhotoController;
use App\Models\Chat;
use App\Models\Message;
use App\Events\MessageSent;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;

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



// Rutas protegidas por autenticación
Route::middleware('auth:sanctum')->group(function () {

    // Rutas relacionadas con usuarios
    Route::prefix('users')->group(function () {
        Route::get('/', [UsersController::class, 'index']);
        Route::get('getDebtsUsers', [UsersController::class, 'getDebtsUsers']);
        Route::get('porRfc/{rfc}', [UsersController::class, 'porRfc']);
        Route::get('obtenerContenedores/{rfc}', [UsersController::class, 'obtenerContenedores']);

        Route::get('obtenerEntradas/{rfc}', [UsersController::class, 'obtenerEntradas']);
        Route::get('obtenerContenedoresPorEntrada/{rfc}', [UsersController::class, 'obtenerContenedoresPorEntrada']);

        Route::get('obtenerSalidas/{rfc}', [UsersController::class, 'obtenerSalidas']);
        Route::get('obtenerContenedoresPorSalida/{rfc}', [UsersController::class, 'obtenerContenedoresPorSalida']);
        Route::get('obtenerContenedoresPorSalidaAbierta/{rfc}', [UsersController::class, 'obtenerContenedoresPorSalidaAbierta']);
        Route::get('obtenerContenedoresPorSalidaCerrada/{rfc}', [UsersController::class, 'obtenerContenedoresPorSalidaCerrada']);



        Route::get('obtenerTraspaleos/{rfc}', [UsersController::class, 'obtenerTraspaleos']);
        Route::get('obtenerContenedoresPorTraspaleo/{rfc}', [UsersController::class, 'obtenerContenedoresPorTraspaleo']);



        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('saldo', [UsersController::class, 'getSaldo']);
        Route::post('admins', [UsersController::class, 'getAdmins']);
        Route::post('employees', [UsersController::class, 'getEmployees']);
        Route::post('clients', [UsersController::class, 'getClients']);
        Route::post('soporte', [UsersController::class, 'getSupports']);

        Route::post('/store-token', [NotificationController::class, 'storeToken']);


        route::post('/upload-profile-photo', [ProfilePhotoController::class, 'upload']);
        route::get('/get-user-profile-photo', [ProfilePhotoController::class, 'getProfilePhoto']);
        
        Route::put('/contenedor/{id}', [ContenedorController::class, 'update']);
        Route::put('/entradasPagar/{id}', [ContenedorController::class, 'updateEntradasPagar']);
        Route::put('/contenedorStatus/{id}', [ContenedorController::class, 'updateContenedorStatus']);
        Route::put('/addContendedor/{folio}', [ContenedorController::class, 'updateAddContenedor']);
        Route::put('/CerrarSalida/{folio}', [ContenedorController::class, 'CerrarSalida']);
        Route::put('/updateName/{id}', [UsersController::class, 'updateName']);
        Route::put('/updateEmail/{id}', [UsersController::class, 'updateEmail']);
        Route::put('/updateRfc/{id}', [UsersController::class, 'updateRfc']);
        Route::put('/updatePassword/{id}', [UsersController::class, 'updatePassword']);
        Route::put('/{id}', [UsersController::class, 'updateUserInfo']);

        Route::post('/get-user-id', [UsersController::class, 'getUserIdByEmail']);
        Route::get('/{id}', [UsersController::class, 'show']);
        Route::get('{user}/chats', [ChatController::class, 'getUserChats']);
        Route::post('/createEntradaContenedores', [ContenedorController::class, 'createEntradaContenedoresS']);
        Route::post('/createSalidaContenedores', [ContenedorController::class, 'createEntradaContenedoresSS']);
        Route::post('/createTraspaleoContenedores', [ContenedorController::class, 'createEntradaContenedoresSSS']);

        Route::post('/createContenedores', [ContenedorController::class, 'createContenedoresS']);

        route::get('/info/{id}', [UsersController::class, 'getInfoUser']);
    });

    // Rutas para PaymentHistory
    Route::prefix('payment-history')->group(function () {
        Route::get('/', [PaymentHistoryController::class, 'index']); // Obtener todos los pagos
        Route::post('create', [PaymentHistoryController::class, 'store']); // Crear un nuevo registro de pago
        Route::get('obtenerHistorial/{userId}', [PaymentHistoryController::class, 'show']); // Obtener los pagos de un usuario específico
    });
    

    Route::prefix('chats')->group(function () {
        Route::get('/{userId}', [ChatController::class, 'index']);
        Route::post('/store', [ChatController::class, 'store']);
        Route::get('/{chat}/messages', [MessageController::class, 'index']);
        Route::get('/user/{id}/chats', [ChatController::class, 'getUserChats']);
        Route::post('/user-chats-by-email', [ChatController::class, 'getUserChatsByEmail']);
    });

    // Rutas para mensajes
    Route::prefix('messages')->group(function () {
        Route::post('/', [MessageController::class, 'store']);
        Route::post('/check', [MessageController::class, 'findOrCreateChat']);
        Route::post('/read/{chat}/messages/mark-as-read', [MessageController::class, 'markChatMessagesAsRead']);
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
        Route::post('transfer-funds', [UsersController::class, 'transferFunds']);

        // Rutas para gestión de tarjetas
        Route::prefix('tarjetas')->group(function () {
            Route::post('crear/{userId}', [UsersController::class, 'addTarjetaToUser']);
            Route::post('/eliminar/{userId}/{tarjetaId}', [UsersController::class, 'deleteTarjetaFromUser']);
            Route::post('detalle', [UsersController::class, 'getTarjetaDetail']);
            Route::post('listar', [UsersController::class, 'getUserTarjetas']);


        });
    });

    // Rutas relacionadas con productos
    Route::prefix('producto')->group(function () {
        Route::post('detalle', [ProductsController::class, 'show']);
        Route::post('pagar', [EntradasController::class, 'createCharge']);
    });

    // Rutas de historial
    Route::prefix('historial')->group(function () {
        Route::post('transferencias', [ProductsController::class, 'show']);
        Route::post('pagos', [UsersController::class, 'getProductosSinDeuda']);
        Route::post('detalle-pago', [UsersController::class, 'getDetalleProducto']);
    });

    // Ruta para actualizar contenedores


});
