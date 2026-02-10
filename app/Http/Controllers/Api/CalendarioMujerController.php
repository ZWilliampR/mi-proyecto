<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CalendarioMujer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CalendarioMujerController extends Controller
{
    // Listar registros del calendario
    public function index(Request $request)
    {
        try {
            $registros = CalendarioMujer::where('usuario_id', $request->user()->id)
                ->orderBy('fecha_inicio_periodo', 'desc')
                ->get();

            return response()->json($registros, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener registros'], 500);
        }
    }

    // Crear nuevo registro de periodo
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha_inicio_periodo' => 'required|date',
            'fecha_fin_periodo' => 'nullable|date|after_or_equal:fecha_inicio_periodo',
            'duracion_ciclo' => 'nullable|integer|min:20|max:45',
            'duracion_periodo' => 'nullable|integer|min:2|max:10',
            'sintomas' => 'nullable|array',
            'notas' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $duracionCiclo = $request->duracion_ciclo ?? 28;
            $duracionPeriodo = $request->duracion_periodo ?? 5;
            $fechaInicio = Carbon::parse($request->fecha_inicio_periodo);

            // Calcular próxima fecha estimada y ovulación
            $proximaFecha = $fechaInicio->copy()->addDays($duracionCiclo);
            $fechaOvulacion = $fechaInicio->copy()->addDays($duracionCiclo - 14);

            $registro = CalendarioMujer::create([
                'usuario_id' => $request->user()->id,
                'fecha_inicio_periodo' => $request->fecha_inicio_periodo,
                'fecha_fin_periodo' => $request->fecha_fin_periodo,
                'duracion_ciclo' => $duracionCiclo,
                'duracion_periodo' => $duracionPeriodo,
                'proxima_fecha_estimada' => $proximaFecha,
                'fecha_ovulacion_estimada' => $fechaOvulacion,
                'sintomas' => $request->sintomas,
                'notas' => $request->notas,
            ]);

            return response()->json([
                'message' => 'Registro creado exitosamente',
                'registro' => $registro,
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear registro: ' . $e->getMessage()], 500);
        }
    }

    // Obtener predicción del próximo periodo
    public function prediccion(Request $request)
    {
        try {
            $ultimoRegistro = CalendarioMujer::where('usuario_id', $request->user()->id)
                ->orderBy('fecha_inicio_periodo', 'desc')
                ->first();

            if (!$ultimoRegistro) {
                return response()->json(['message' => 'No hay registros previos'], 404);
            }

            $proximoPeriodo = Carbon::parse($ultimoRegistro->proxima_fecha_estimada);
            $ovulacion = Carbon::parse($ultimoRegistro->fecha_ovulacion_estimada);
            $ventanaFertil = [
                'inicio' => $ovulacion->copy()->subDays(3)->format('Y-m-d'),
                'fin' => $ovulacion->copy()->addDays(1)->format('Y-m-d'),
            ];

            return response()->json([
                'proximo_periodo' => $proximoPeriodo->format('Y-m-d'),
                'dias_faltantes' => Carbon::now()->diffInDays($proximoPeriodo, false),
                'fecha_ovulacion' => $ovulacion->format('Y-m-d'),
                'ventana_fertil' => $ventanaFertil,
                'duracion_ciclo_promedio' => $ultimoRegistro->duracion_ciclo,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener predicción'], 500);
        }
    }

    // Actualizar registro
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'fecha_fin_periodo' => 'nullable|date',
            'sintomas' => 'nullable|array',
            'notas' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $registro = CalendarioMujer::findOrFail($id);
            $registro->update($request->all());

            return response()->json([
                'message' => 'Registro actualizado exitosamente',
                'registro' => $registro,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar registro'], 500);
        }
    }

    // Eliminar registro
    public function destroy($id)
    {
        try {
            $registro = CalendarioMujer::findOrFail($id);
            $registro->delete();

            return response()->json(['message' => 'Registro eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar registro'], 500);
        }
    }
}