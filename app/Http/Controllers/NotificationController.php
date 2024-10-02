<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    
    public function sendNotification($deviceToken) {
        $message = FcmMessage::create()
            ->setData(['key' => 'value'])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle('Nuevo mensaje')
                ->setBody('Tienes un nuevo mensaje en tu app'));

        // Enviar notificación al token FCM
        Notification::route('fcm', $deviceToken)->notify(new FcmNotification($message));
    }

/*     public function sendNotification($deviceToken, $title, $body) {
        $message = FcmMessage::create()
            ->setData(['key' => 'value']) // Agrega datos opcionales
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($title)
                ->setBody($body));

        $deviceToken->notify(new FcmNotification($message));
    } */
    public function storeToken(Request $request)
    {
                    // Verificar si el usuario está autenticado
                    if (!Auth::check()) {
                        return response()->json([
                            'error' => 'Unauthorized',
                            'message' => 'You must be authenticated to view this resource.'
                        ], 401);
                    }
            

        $request->validate([
            'token' => 'required|string',
        ]);

        $user = auth()->user(); // Si está autenticado, puedes asociar el token con el usuario

        if ($user) {
            $user->fcm_token = $request->token;
            $user->save();

            return response()->json(['message' => 'Token almacenado correctamente']);
        }

        return response()->json(['error' => 'Usuario no autenticado'], 401);
    }
}
