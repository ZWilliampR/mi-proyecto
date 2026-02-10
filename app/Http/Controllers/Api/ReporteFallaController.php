<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReporteFalla;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReporteFallaController extends Controller
{
    // Listar reportes del usuario
    public function index(Request $request)
    {
        try {
            $reportes = ReporteFalla::where('usuario_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($reportes, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener reportes'], 500);
        }
    }

    // Listar todos los reportes (solo admin)
    public function todos(Request $request)
    {
        try {
            // Verificar que sea administrador
            if ($request->user()->role_id != 3) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $reportes = ReporteFalla::with('usuario')
                ->orderBy('prioridad', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($reportes, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener reportes'], 500);
        }
    }

    // Crear nuevo reporte de falla
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:200',
            'descripcion' => 'required|string',
            'tipo_falla' => 'required|in:bug,funcionalidad,diseño,otro',
            'prioridad' => 'nullable|in:baja,media,alta,critica',
            'captura_pantalla' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $reporte = ReporteFalla::create([
                'usuario_id' => $request->user()->id,
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'tipo_falla' => $request->tipo_falla,
                'prioridad' => $request->prioridad ?? 'media',
                'captura_pantalla' => $request->captura_pantalla,
                'estado' => 'pendiente',
            ]);

            return response()->json([
                'message' => 'Reporte creado exitosamente. Nuestro equipo lo revisará pronto.',
                'reporte' => $reporte,
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear reporte: ' . $e->getMessage()], 500);
        }
    }

    // Ver reporte específico
    public function show($id)
    {
        try {
            $reporte = ReporteFalla::with('usuario')->findOrFail($id);
            return response()->json($reporte, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Reporte no encontrado'], 404);
        }
    }

    // Actualizar estado del reporte (solo admin)
    public function actualizarEstado(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'estado' => 'required|in:pendiente,en_revision,resuelto,rechazado',
            'respuesta_soporte' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Verificar que sea administrador
            if ($request->user()->role_id != 3) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $reporte = ReporteFalla::findOrFail($id);
            
            $data = [
                'estado' => $request->estado,
                'respuesta_soporte' => $request->respuesta_soporte,
            ];

            if ($request->estado == 'resuelto') {
                $data['fecha_resolucion'] = now();
            }

            $reporte->update($data);

            return response()->json([
                'message' => 'Estado del reporte actualizado exitosamente',
                'reporte' => $reporte,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar reporte'], 500);
        }
    }

    // Estadísticas de reportes (solo admin)
    public function estadisticas(Request $request)
    {
        try {
            // Verificar que sea administrador
            if ($request->user()->role_id != 3) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $total = ReporteFalla::count();
            $pendientes = ReporteFalla::where('estado', 'pendiente')->count();
            $enRevision = ReporteFalla::where('estado', 'en_revision')->count();
            $resueltos = ReporteFalla::where('estado', 'resuelto')->count();
            $rechazados = ReporteFalla::where('estado', 'rechazado')->count();

            $porTipo = ReporteFalla::selectRaw('tipo_falla, COUNT(*) as cantidad')
                ->groupBy('tipo_falla')
                ->get();

            $porPrioridad = ReporteFalla::selectRaw('prioridad, COUNT(*) as cantidad')
                ->groupBy('prioridad')
                ->get();

            return response()->json([
                'total' => $total,
                'por_estado' => [
                    'pendientes' => $pendientes,
                    'en_revision' => $enRevision,
                    'resueltos' => $resueltos,
                    'rechazados' => $rechazados,
                ],
                'por_tipo' => $porTipo,
                'por_prioridad' => $porPrioridad,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener estadísticas'], 500);
        }
    }

    // Eliminar reporte
    public function destroy($id)
    {
        try {
            $reporte = ReporteFalla::findOrFail($id);
            $reporte->delete();

            return response()->json(['message' => 'Reporte eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar reporte'], 500);
        }
    }
}