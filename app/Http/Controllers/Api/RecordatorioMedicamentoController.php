<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RecordatorioMedicamento;
use App\Models\Medicamento;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RecordatorioMedicamentoController extends Controller
{
    // Listar recordatorios pendientes del usuario
    public function index(Request $request)
    {
        try {
            $usuarioId = $request->user()->id;
            
            $recordatorios = RecordatorioMedicamento::whereHas('medicamento', function($query) use ($usuarioId) {
                $query->where('usuario_id', $usuarioId);
            })
            ->with('medicamento')
            ->where('estado', 'pendiente')
            ->where('fecha_hora_recordatorio', '>=', Carbon::now())
            ->orderBy('fecha_hora_recordatorio', 'asc')
            ->get();

            return response()->json($recordatorios, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener recordatorios'], 500);
        }
    }

    // Obtener recordatorios de hoy
    public function recordatoriosHoy(Request $request)
    {
        try {
            $usuarioId = $request->user()->id;
            $hoy = Carbon::today();
            
            $recordatorios = RecordatorioMedicamento::whereHas('medicamento', function($query) use ($usuarioId) {
                $query->where('usuario_id', $usuarioId);
            })
            ->with('medicamento')
            ->whereDate('fecha_hora_recordatorio', $hoy)
            ->orderBy('fecha_hora_recordatorio', 'asc')
            ->get();

            return response()->json([
                'fecha' => $hoy->format('Y-m-d'),
                'total' => $recordatorios->count(),
                'pendientes' => $recordatorios->where('estado', 'pendiente')->count(),
                'tomados' => $recordatorios->where('estado', 'tomado')->count(),
                'omitidos' => $recordatorios->where('estado', 'omitido')->count(),
                'recordatorios' => $recordatorios,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener recordatorios de hoy'], 500);
        }
    }

    // Marcar medicamento como tomado
    public function marcarTomado(Request $request, $id)
    {
        try {
            $recordatorio = RecordatorioMedicamento::findOrFail($id);
            
            $recordatorio->update([
                'estado' => 'tomado',
                'fecha_hora_tomado' => Carbon::now(),
                'notas' => $request->notas ?? null,
            ]);

            return response()->json([
                'message' => 'Medicamento marcado como tomado',
                'recordatorio' => $recordatorio,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar recordatorio'], 500);
        }
    }

    // Marcar medicamento como omitido
    public function marcarOmitido(Request $request, $id)
    {
        try {
            $recordatorio = RecordatorioMedicamento::findOrFail($id);
            
            $recordatorio->update([
                'estado' => 'omitido',
                'notas' => $request->notas ?? null,
            ]);

            return response()->json([
                'message' => 'Medicamento marcado como omitido',
                'recordatorio' => $recordatorio,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar recordatorio'], 500);
        }
    }

    // Historial de recordatorios (últimos 30 días)
    public function historial(Request $request)
    {
        try {
            $usuarioId = $request->user()->id;
            $hace30Dias = Carbon::now()->subDays(30);
            
            $recordatorios = RecordatorioMedicamento::whereHas('medicamento', function($query) use ($usuarioId) {
                $query->where('usuario_id', $usuarioId);
            })
            ->with('medicamento')
            ->where('fecha_hora_recordatorio', '>=', $hace30Dias)
            ->orderBy('fecha_hora_recordatorio', 'desc')
            ->get();

            return response()->json([
                'periodo' => 'Últimos 30 días',
                'total' => $recordatorios->count(),
                'tomados' => $recordatorios->where('estado', 'tomado')->count(),
                'omitidos' => $recordatorios->where('estado', 'omitido')->count(),
                'adherencia' => $recordatorios->count() > 0 
                    ? round(($recordatorios->where('estado', 'tomado')->count() / $recordatorios->count()) * 100, 2) 
                    : 0,
                'recordatorios' => $recordatorios,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener historial'], 500);
        }
    }
}