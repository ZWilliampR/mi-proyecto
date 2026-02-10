<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\LogAcceso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Registro de nuevo usuario (Titular)
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|string|min:8|confirmed',
            'telefono' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'genero' => 'nullable|in:masculino,femenino,otro',
            'id_socio_costamed' => 'required|string|max:50|unique:usuarios,id_socio_costamed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $usuario = Usuario::create([
                'nombre' => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'telefono' => $request->telefono,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'genero' => $request->genero,
                'role_id' => 1, // Rol titular por defecto
                'id_socio_costamed' => $request->id_socio_costamed,
                'membresia_activa' => true,
            ]);

            $token = $usuario->createToken('auth_token')->plainTextToken;

            // Registrar log de acceso
            $this->registrarLog($usuario, 'login', 'exitoso', $request);

            return response()->json([
                'message' => 'Usuario registrado exitosamente',
                'user' => $usuario,
                'token' => $token,
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al registrar usuario: ' . $e->getMessage()], 500);
        }
    }

    // Login
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            // Registrar intento fallido
            $this->registrarLog(null, 'intento_fallido', 'fallido', $request, $request->email);
            
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        if (!$usuario->membresia_activa) {
            return response()->json(['message' => 'Membresía inactiva'], 403);
        }

        $token = $usuario->createToken('auth_token')->plainTextToken;

        // Registrar log de acceso exitoso
        $this->registrarLog($usuario, 'login', 'exitoso', $request);

        return response()->json([
            'message' => 'Login exitoso',
            'user' => $usuario->load('role'),
            'token' => $token,
        ], 200);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        // Registrar log de logout
        $this->registrarLog($request->user(), 'logout', 'exitoso', $request);

        return response()->json(['message' => 'Logout exitoso'], 200);
    }

    // Obtener usuario autenticado
    public function me(Request $request)
    {
        return response()->json($request->user()->load('role'), 200);
    }

    // Método privado para registrar logs
    private function registrarLog($usuario, $accion, $resultado, $request, $email = null)
    {
        LogAcceso::create([
            'usuario_id' => $usuario ? $usuario->id : null,
            'email' => $email ?? ($usuario ? $usuario->email : null),
            'ip_address' => $request->ip(),
            'dispositivo' => $request->userAgent(),
            'accion' => $accion,
            'endpoint' => $request->path(),
            'resultado' => $resultado,
            'detalles' => null,
        ]);
    }
}