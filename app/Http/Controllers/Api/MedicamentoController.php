<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicamento;
use App\Models\RecordatorioMedicamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MedicamentoController extends Controller
{
    // Listar medicamentos del usuario autenticado
    public function index(Request $request)
    {
        try {
            $medicamentos = Medicamento::where('usuario_id', $request->user()->id)
                    ->orderBy('created_at', 'desc')
                    ->get();

            return response()->json($medicamentos, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener medicamentos',
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    // Crear nuevo medicamento
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre_medicamento' => 'required|string|max:150',
                'dosis' => 'required|string|max:50',
                'frecuencia' => 'required|in:cada_4_horas,cada_6_horas,cada_8_horas,cada_12_horas,cada_24_horas,personalizado',
                'hora_inicio' => 'required|date_format:H:i',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
                'notas' => 'nullable|string',
                'notificacion_activa' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $medicamento = Medicamento::create([
                'usuario_id' => $request->user()->id,
                'nombre_medicamento' => $request->nombre_medicamento,
                'dosis' => $request->dosis,
                'frecuencia' => $request->frecuencia,
                'hora_inicio' => $request->hora_inicio,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'notas' => $request->notas,
                'notificacion_activa' => $request->notificacion_activa ?? true,
            ]);

            // Generar recordatorios automáticamente
            $this->generarRecordatorios($medicamento);

            return response()->json([
                'message' => 'Medicamento creado exitosamente',
                'medicamento' => $medicamento->load('recordatorios'),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al crear medicamento',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    // Ver un medicamento específico
    public function show($id)
    {
        try {
            $medicamento = Medicamento::with('recordatorios')->findOrFail($id);
            return response()->json($medicamento, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Medicamento no encontrado'], 404);
        }
    }

    // Actualizar medicamento
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre_medicamento' => 'sometimes|string|max:150',
            'dosis' => 'sometimes|string|max:50',
            'frecuencia' => 'sometimes|in:cada_4_horas,cada_6_horas,cada_8_horas,cada_12_horas,cada_24_horas,personalizado',
            'notificacion_activa' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $medicamento = Medicamento::findOrFail($id);
            $medicamento->update($request->all());

            return response()->json([
                'message' => 'Medicamento actualizado exitosamente',
                'medicamento' => $medicamento,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar medicamento'], 500);
        }
    }

    // Eliminar medicamento
    public function destroy($id)
    {
        try {
            $medicamento = Medicamento::findOrFail($id);
            $medicamento->delete();

            return response()->json(['message' => 'Medicamento eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar medicamento'], 500);
        }
    }

    // Método privado para generar recordatorios automáticos
    private function generarRecordatorios($medicamento)
    {
        $horasIntervalo = [
            'cada_4_horas' => 4,
            'cada_6_horas' => 6,
            'cada_8_horas' => 8,
            'cada_12_horas' => 12,
            'cada_24_horas' => 24,
        ];

        $intervalo = $horasIntervalo[$medicamento->frecuencia] ?? 24;

        // Parseo simplificado
        $fechaInicio = Carbon::parse($medicamento->fecha_inicio)->setTimeFromTimeString($medicamento->hora_inicio);
        $fechaFin = $medicamento->fecha_fin 
            ? Carbon::parse($medicamento->fecha_fin)->endOfDay() 
            : $fechaInicio->copy()->addDays(30);

        $fechaActual = $fechaInicio->copy();

        while ($fechaActual <= $fechaFin) {
            RecordatorioMedicamento::create([
                'medicamento_id' => $medicamento->id,
                'fecha_hora_recordatorio' => $fechaActual,
                'estado' => 'pendiente',
            ]);

            $fechaActual->addHours($intervalo);
        }
    }
}