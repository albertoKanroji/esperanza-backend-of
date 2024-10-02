<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Google\Client;


class ChatController extends Controller
{
    // Listar todos los chats de un usuario
    public function index($userId)
    {
        $chats = Chat::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with('user1', 'user2', 'messages')
            ->get();
        return response()->json($chats);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
        ]);

        // Verificar si ya existe un chat entre estos dos usuarios
        $existingChat = Chat::where(function ($query) use ($request) {
            $query->where('sender_id', $request->sender_id)
                ->where('receiver_id', $request->receiver_id);
        })->orWhere(function ($query) use ($request) {
            $query->where('sender_id', $request->receiver_id)
                ->where('receiver_id', $request->sender_id);
        })->first();

        if ($existingChat) {
            return response()->json(['message' => 'El chat ya existe.'], 200);
        }

        $chat = Chat::create([
            'sender_id' => $request->sender_id,
            'receiver_id' => $request->receiver_id,
        ]);

        return response()->json($chat, 201);
    }
    
    public function getUserChats(Request $request, $id)
    {
        $clientes_id = $request->input('clientes_id');

        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You must be authenticated to view this resource.'
            ], 401);
        }

        $chats = Chat::where('sender_id', $id)
            ->orWhere('receiver_id', $id)
            ->get(['id']);

        return response()->json($chats);
    }
    
    public function getUserChatsByEmail(Request $request)
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You must be authenticated to view this resource.'
            ], 401);
        }
    
        try {
            // Validar que el email esté presente en la solicitud
            $request->validate([
                'email' => 'required|string|email'
            ]);
    
            // Buscar el usuario por su correo electrónico
            $user = User::where('email', $request->email)->first();
    
            if (!$user) {
                return response()->json(['message' => 'Usuario no encontrado'], 404);
            }
    
            // Obtener el ID del usuario
            $userId = $user->id;
    
            // Buscar los chats del usuario
            $chats = Chat::where('sender_id', $userId)
                ->orWhere('receiver_id', $userId)
                ->get(['id', 'sender_id', 'receiver_id']);
    
            if ($chats->isEmpty()) {
                return response()->json(['message' => 'El usuario no tiene chats'], 200);
            }
    
            // Obtener detalles de los otros usuarios en cada chat y el último mensaje
            $chatsWithDetails = $chats->map(function ($chat) use ($userId) {
                // Determinar el ID del otro usuario en el chat
                $otherUserId = $chat->sender_id === $userId ? $chat->receiver_id : $chat->sender_id;
                $otherUser = User::find($otherUserId);
    
                // Obtener el último mensaje del chat
                $lastMessage = $chat->messages()->latest('created_at')->first();
    
                return [
                    'chat_id' => $chat->id,
                    'other_user' => [
                        'id' => $otherUser ? $otherUser->id : null,
                        'name' => $otherUser ? $otherUser->name : null,
                        'imageUrl' => $otherUser ? $otherUser->imageUrl : null,
                        'email' => $otherUser ? $otherUser->email : null,
                        'last_message' => $lastMessage ? $lastMessage->content : null,
                    ]
                ];
            });
    
            return response()->json($chatsWithDetails, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Datos de solicitud inválidos', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ocurrió un error al procesar la solicitud'], 500);
        }
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'sender_id' => 'required|exists:users,id',
            'content' => 'required|string',
        ]);
    
        // Crear mensaje
        $message = Message::create([
            'chat_id' => $request->chat_id,
            'sender_id' => $request->sender_id,
            'content' => $request->content,
        ]);
    
        // Obtener el chat y determinar el receptor
        $chat = Chat::find($request->chat_id);
        $receiverId = $chat->sender_id === $request->sender_id ? $chat->receiver_id : $chat->sender_id;
        $receiver = User::find($receiverId);
    
        // Emitir el evento MessageSent para Pusher
        broadcast(new MessageSent($message))->toOthers();
    
        // Si el receptor tiene un token FCM, enviar una notificación push
        if ($receiver && $receiver->fcm_token) {
            $this->sendPushNotification($receiver->fcm_token, 'Nuevo mensaje', 'Tienes un nuevo mensaje de ' . $message->sender->name);
        }
    
        return response()->json($message, 201);
    }
    

    public function sendPushNotification($deviceToken, $title, $body)
    {
        // Ruta del archivo de credenciales JSON descargado desde Firebase Console
        $credentialsPath = storage_path('app/google_service_account.json'); 
    
        // Crear cliente de Google para obtener el token de acceso OAuth 2.0
        $client = new Client();
        $client->setAuthConfig($credentialsPath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
    
        // Obtener el token de acceso OAuth 2.0
        $token = $client->fetchAccessTokenWithAssertion()['access_token'];
    
        // URL del nuevo endpoint HTTP v1
        $projectId = 'closca-conect'; // Cambia esto por tu project ID de Firebase
        $url = "https://fcm.googleapis.com/v1/projects/$projectId/messages:send";
    
        // Cuerpo del mensaje de notificación
        $data = [
            'message' => [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
            ],
        ];
    
        // Encabezados de la solicitud
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ];
    
        // Enviar la solicitud mediante cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
        $result = curl_exec($ch);
    
        // Registrar cualquier error cURL
        if (curl_errno($ch)) {
            \Log::error('Error en cURL: ' . curl_error($ch));
        }
    
        // Obtener código de respuesta HTTP
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        \Log::info('Código de respuesta HTTP: ' . $httpCode);
        \Log::info('Respuesta de Firebase: ' . $result);
    
        curl_close($ch);
    
        return $result;
    }
    
    // Puedes llamarlo cuando sea necesario, por ejemplo, después de crear un nuevo mensaje o chat
    public function notifyUserOfNewMessage($receiverToken, $message)
    {
        $title = 'Nuevo mensaje';
        $body = 'Tienes un nuevo mensaje: ' . $message;

        // Llamar a la función de envío de notificación push
        $this->sendPushNotification($receiverToken, $title, $body);
    }

        // Función para almacenar el token FCM
        public function storeToken(Request $request)
        {

                    // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You must be authenticated to view this resource.'
            ], 401);
        }
    
            
            // Validar que el token sea proporcionado
            $request->validate([
                'token' => 'required|string',
            ]);
    
                // Almacenar el token FCM en la base de datos
                $user->fcm_token = $request->token;
                $user->save();
    
                return response()->json([
                    'message' => 'FCM token almacenado correctamente',
                ], 200);

        }
}