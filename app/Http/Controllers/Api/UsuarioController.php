<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsuarioController extends Controller
{
    // Listar todos los usuarios (solo admin)
    public function index(Request $request)
    {
        try {
            $usuarios = Usuario::with('role')->get();
            return response()->json($usuarios, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener usuarios'], 500);
        }
    }

    // Obtener un usuario específico
    public function show($id)
    {
        try {
            $usuario = Usuario::with(['role', 'miembrosFamiliares'])->findOrFail($id);
            return response()->json($usuario, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }
    }

    // Agregar miembro familiar (solo titular)
    public function agregarMiembroFamiliar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:100',
                'apellido_paterno' => 'required|string|max:100',
                'apellido_materno' => 'nullable|string|max:100',
                'email' => 'required|email|unique:usuarios,email',
                'password' => 'required|string|min:8|confirmed',
                'telefono' => 'nullable|string|max:20',
                'fecha_nacimiento' => 'nullable|date',
                'genero' => 'nullable|in:masculino,femenino,otro',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $titular = $request->user();

            // Verificar que sea titular
            if ($titular->role_id != 1) {
                return response()->json(['error' => 'Solo los titulares pueden agregar miembros familiares'], 403);
            }

            // Verificar límite de 3 miembros
            $miembrosActuales = Usuario::where('titular_id', $titular->id)->count();
            if ($miembrosActuales >= 3) {
                return response()->json(['error' => 'Has alcanzado el límite de 3 miembros familiares'], 403);
            }

            $miembro = Usuario::create([
                'nombre' => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'telefono' => $request->telefono,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'genero' => $request->genero,
                'role_id' => 2, // Rol miembro_familiar
                'titular_id' => $titular->id,
                'membresia_activa' => true,
            ]);

            return response()->json([
                'message' => 'Miembro familiar agregado exitosamente',
                'miembro' => $miembro,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al agregar miembro',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    // Listar miembros familiares del titular
    public function miembrosFamiliares(Request $request)
    {
        try {
            $titular = $request->user();
            $miembros = Usuario::where('titular_id', $titular->id)->get();
            
            return response()->json([
                'total' => $miembros->count(),
                'limite' => 3,
                'disponibles' => 3 - $miembros->count(),
                'miembros' => $miembros,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener miembros',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    // Actualizar perfil de usuario
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|string|max:100',
            'apellido_paterno' => 'sometimes|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'genero' => 'nullable|in:masculino,femenino,otro',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $usuario = Usuario::findOrFail($id);
            $usuario->update($request->all());

            return response()->json([
                'message' => 'Usuario actualizado exitosamente',
                'usuario' => $usuario,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar usuario'], 500);
        }
    }

    // Eliminar miembro familiar (solo titular puede eliminar sus miembros)
    public function destroy(Request $request, $id)
    {
        try {
            $usuario = Usuario::findOrFail($id);
            $titular = $request->user();

            // Verificar que el titular solo pueda eliminar a sus propios miembros
            if ($usuario->titular_id != $titular->id) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $usuario->delete();

            return response()->json(['message' => 'Miembro familiar eliminado exitosamente'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar miembro'], 500);
        }
    }
}