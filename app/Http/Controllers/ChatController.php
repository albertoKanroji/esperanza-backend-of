<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


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

    // Crear un nuevo chat
    public function store(Request $request)
    {
        $request->validate([
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
        ]);

        $chat = Chat::create([
            'sender_id' => $request->sender_id,
            'receiver_id' => $request->receiver_id,
        ]);

        return response()->json($chat, 201);
    }

    public function getUserChats(Request $request, $id) // Añadir Request como parámetro
    {
        $clientes_id = $request->input('clientes_id');
        // Verificar si el usuario está autenticado
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
    
    
}