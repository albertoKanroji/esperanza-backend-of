<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;

use App\Models\Message;
use App\Models\Chat;
use Illuminate\Http\Request;
use Pusher\Pusher;

use Illuminate\Support\Facades\Auth;


class MessageController extends Controller
{
    // Listar mensajes de un chat
    public function index(Request $request, $chatId) // Añadir Request como parámetro
    {
        $clientes_id = $request->input('clientes_id');
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You must be authenticated to view this resource.'
            ], 401);
        }

        $chat = Chat::with(['messages.sender', 'messages.receiver'])->findOrFail($chatId);
        return response()->json($chat->messages);
    }

    // Enviar un mensaje
    public function store(Request $request)
    {
        $clientes_id = $request->input('clientes_id');
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You must be authenticated to view this resource.'
            ], 401);
        }


        $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string',
        ]);

        // Verificar si hay un archivo adjunto
        if ($request->hasFile('content')) {
            $file = $request->file('content');
            $filePath = $file->store('uploads', 'public');
            $content = asset('storage/' . $filePath);
        } else {
            $content = $request->content;
        }

        $message = Message::create([
            'sender_id' => $request->sender_id,
            'receiver_id' => $request->receiver_id,
            'content' => $content,
            'chat_id' => $request->chat_id,
        ]);

        $pusherKey = '2d9e88bba0a843077168';
        $pusherSecret = 'b19d7806000eb939ea7a';
        $pusherId = '1836300';
        $pusherCluster = 'us2';

        if (!$pusherKey || !$pusherSecret || !$pusherId || !$pusherCluster) {
            return response()->json(['error' => 'Pusher configuration is missing', 'key' => $pusherKey, 'secret' => $pusherSecret, 'id' => $pusherId, 'cluster' => $pusherCluster], 500);
        }

        $pusher = new Pusher($pusherKey, $pusherSecret, $pusherId, [
            'cluster' => $pusherCluster,
            'useTLS' => true,
        ]);

        $eventData = [
            'event' => [
                'channel' => 'chat.' . $request->chat_id,
                'event_name' => 'nuevo mensaje',
                'data' => $message,
            ],
        ];

        // Enviar el evento a Pusher
        $pusher->trigger('chat.' . $request->chat_id, 'new-message', $eventData);

        return response()->json($message, 201);
    }

    public function findOrCreateChat(Request $request)
    {
        $clientes_id = $request->input('clientes_id');
    
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You must be authenticated to view this resource.'
            ], 401);
        }
    
        // Validar los datos de entrada
        $validated = $request->validate([
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
        ]);
    
        $senderId = $validated['sender_id'];
        $receiverId = $validated['receiver_id'];
    
        // Asegurarse de que senderId y receiverId no sean iguales
        if ($senderId == $receiverId) {
            return response()->json(['error' => 'Sender and receiver cannot be the same'], 400);
        }
    
        // Buscar chat existente
        $chat = Chat::where(function ($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $senderId)
                  ->where('receiver_id', $receiverId);
        })->orWhere(function ($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $receiverId)
                  ->where('receiver_id', $senderId);
        })->first();
    
        // Si no existe un chat, crear uno nuevo
        if (!$chat) {
            $chat = Chat::create([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
            ]);
        }
    
        // Retornar el ID del chat
        return response()->json(['chat_id' => $chat->id], 200);
    }
    
    public function markChatMessagesAsRead(Request $request, $chatId)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $userId = $validated['user_id'];

        Message::where('chat_id', $chatId)
            ->where('receiver_id', $userId)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'Messages marked as read successfully'], 200);
    }
}