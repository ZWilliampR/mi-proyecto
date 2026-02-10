<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VisitaDomiciliaria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VisitaDomiciliariaController extends Controller
{
    // Listar visitas del usuario
    public function index(Request $request)
    {
        try {
            $visitas = VisitaDomiciliaria::where('usuario_id', $request->user()->id)
                ->with('medico')
                ->orderBy('fecha_solicitada', 'desc')
                ->get();

            return response()->json($visitas, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener visitas'], 500);
        }
    }

    // Listar todas las visitas (solo hospital/médicos)
    public function todas(Request $request)
    {
        try {
            // Verificar que sea hospital o administrador
            if (!in_array($request->user()->role_id, [3, 4])) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $visitas = VisitaDomiciliaria::with(['usuario', 'medico'])
                ->orderBy('fecha_solicitada', 'asc')
                ->get();

            return response()->json($visitas, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener visitas'], 500);
        }
    }

    // Solicitar nueva visita domiciliaria
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha_solicitada' => 'required|date|after_or_equal:today',
            'hora_solicitada' => 'required|date_format:H:i',
            'direccion' => 'required|string',
            'ciudad' => 'required|string|max:100',
            'estado' => 'required|string|max:100',
            'codigo_postal' => 'required|string|max:10',
            'motivo_visita' => 'required|string',
            'tipo_servicio' => 'required|in:consulta_general,urgencia,seguimiento',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $visita = VisitaDomiciliaria::create([
                'usuario_id' => $request->user()->id,
                'fecha_solicitada' => $request->fecha_solicitada,
                'hora_solicitada' => $request->hora_solicitada,
                'direccion' => $request->direccion,
                'ciudad' => $request->ciudad,
                'estado' => $request->estado,
                'codigo_postal' => $request->codigo_postal,
                'motivo_visita' => $request->motivo_visita,
                'tipo_servicio' => $request->tipo_servicio,
                'estado_solicitud' => 'pendiente',
            ]);

            return response()->json([
                'message' => 'Solicitud de visita domiciliaria creada exitosamente',
                'visita' => $visita,
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear solicitud: ' . $e->getMessage()], 500);
        }
    }

    // Ver visita específica
    public function show($id)
    {
        try {
            $visita = VisitaDomiciliaria::with(['usuario', 'medico'])->findOrFail($id);
            return response()->json($visita, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Visita no encontrada'], 404);
        }
    }

    // Confirmar visita (solo hospital/médico)
    public function confirmar(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'medico_id' => 'required|exists:usuarios,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Verificar que sea hospital o administrador
            if (!in_array($request->user()->role_id, [3, 4])) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $visita = VisitaDomiciliaria::findOrFail($id);
            
            $visita->update([
                'medico_id' => $request->medico_id,
                'estado_solicitud' => 'confirmada',
                'fecha_confirmacion' => now(),
            ]);

            return response()->json([
                'message' => 'Visita confirmada exitosamente',
                'visita' => $visita->load('medico'),
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al confirmar visita'], 500);
        }
    }

    // Actualizar estado de la visita
    public function actualizarEstado(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'estado_solicitud' => 'required|in:pendiente,confirmada,en_camino,completada,cancelada',
            'notas_medico' => 'nullable|string',
            'motivo_cancelacion' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $visita = VisitaDomiciliaria::findOrFail($id);
            
            $data = [
                'estado_solicitud' => $request->estado_solicitud,
                'notas_medico' => $request->notas_medico,
            ];

            if ($request->estado_solicitud == 'completada') {
                $data['fecha_completada'] = now();
            }

            if ($request->estado_solicitud == 'cancelada') {
                $data['motivo_cancelacion'] = $request->motivo_cancelacion;
            }

            $visita->update($data);

            return response()->json([
                'message' => 'Estado de la visita actualizado exitosamente',
                'visita' => $visita,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar estado'], 500);
        }
    }

    // Cancelar visita
    public function cancelar(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'motivo_cancelacion' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $visita = VisitaDomiciliaria::findOrFail($id);
            
            $visita->update([
                'estado_solicitud' => 'cancelada',
                'motivo_cancelacion' => $request->motivo_cancelacion,
            ]);

            return response()->json([
                'message' => 'Visita cancelada exitosamente',
                'visita' => $visita,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al cancelar visita'], 500);
        }
    }

    // Eliminar visita
    public function destroy($id)
    {
        try {
            $visita = VisitaDomiciliaria::findOrFail($id);
            $visita->delete();

            return response()->json(['message' => 'Visita eliminada exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar visita'], 500);
        }
    }
}