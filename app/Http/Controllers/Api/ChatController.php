<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Mensaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    // Listar chats del usuario
    public function index(Request $request)
    {
        try {
            $chats = Chat::where('usuario_id', $request->user()->id)
                ->with(['medico', 'mensajes' => function($query) {
                    $query->orderBy('created_at', 'desc')->limit(1);
                }])
                ->orderBy('updated_at', 'desc')
                ->get();

            return response()->json($chats, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener chats'], 500);
        }
    }

    // Crear nuevo chat
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo_chat' => 'required|in:normal,emergencia',
            'mensaje_inicial' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $chat = Chat::create([
                'usuario_id' => $request->user()->id,
                'tipo_chat' => $request->tipo_chat,
                'estado' => 'activo',
            ]);

            // Crear mensaje inicial
            Mensaje::create([
                'chat_id' => $chat->id,
                'usuario_id' => $request->user()->id,
                'mensaje' => $request->mensaje_inicial,
            ]);

            return response()->json([
                'message' => 'Chat creado exitosamente',
                'chat' => $chat->load('mensajes'),
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear chat: ' . $e->getMessage()], 500);
        }
    }

    // Ver chat específico con todos sus mensajes
    public function show($id)
    {
        try {
            $chat = Chat::with(['mensajes.usuario', 'medico'])->findOrFail($id);
            
            // Marcar mensajes como leídos
            Mensaje::where('chat_id', $id)
                ->where('usuario_id', '!=', auth()->id())
                ->where('leido', false)
                ->update([
                    'leido' => true,
                    'fecha_leido' => now(),
                ]);

            return response()->json($chat, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Chat no encontrado'], 404);
        }
    }

    // Enviar mensaje en un chat
    public function enviarMensaje(Request $request, $chatId)
    {
        $validator = Validator::make($request->all(), [
            'mensaje' => 'required|string',
            'archivo_adjunto' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $chat = Chat::findOrFail($chatId);

            if ($chat->estado == 'cerrado') {
                return response()->json(['error' => 'Este chat está cerrado'], 403);
            }

            $mensaje = Mensaje::create([
                'chat_id' => $chatId,
                'usuario_id' => $request->user()->id,
                'mensaje' => $request->mensaje,
                'archivo_adjunto' => $request->archivo_adjunto,
            ]);

            // Actualizar timestamp del chat
            $chat->touch();

            return response()->json([
                'message' => 'Mensaje enviado exitosamente',
                'mensaje' => $mensaje->load('usuario'),
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al enviar mensaje'], 500);
        }
    }

    // Obtener mensajes de un chat
    public function mensajes($chatId)
    {
        try {
            $mensajes = Mensaje::where('chat_id', $chatId)
                ->with('usuario')
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json($mensajes, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener mensajes'], 500);
        }
    }

    // Cerrar chat
    public function cerrar($id)
    {
        try {
            $chat = Chat::findOrFail($id);
            
            $chat->update([
                'estado' => 'cerrado',
                'fecha_cierre' => now(),
            ]);

            return response()->json([
                'message' => 'Chat cerrado exitosamente',
                'chat' => $chat,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al cerrar chat'], 500);
        }
    }

    // Obtener mensajes no leídos
    public function noLeidos(Request $request)
    {
        try {
            $chats = Chat::where('usuario_id', $request->user()->id)
                ->where('estado', 'activo')
                ->with(['mensajes' => function($query) {
                    $query->where('usuario_id', '!=', auth()->id())
                          ->where('leido', false);
                }])
                ->get();

            $totalNoLeidos = $chats->sum(function($chat) {
                return $chat->mensajes->count();
            });

            return response()->json([
                'total_no_leidos' => $totalNoLeidos,
                'chats_con_mensajes_nuevos' => $chats->filter(function($chat) {
                    return $chat->mensajes->count() > 0;
                })->values(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener mensajes no leídos'], 500);
        }
    }
}