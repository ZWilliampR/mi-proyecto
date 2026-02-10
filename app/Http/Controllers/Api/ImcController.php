<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ImcRegistro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ImcController extends Controller
{
    // Listar registros de IMC del usuario
    public function index(Request $request)
    {
        try {
            $registros = ImcRegistro::where('usuario_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($registros, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener registros'], 500);
        }
    }

    // Calcular y guardar nuevo registro de IMC
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'peso' => 'required|numeric|min:1|max:500',
            'altura' => 'required|numeric|min:0.5|max:3',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $peso = $request->peso;
            $altura = $request->altura;

            // Calcular IMC
            $imc = $peso / ($altura * $altura);
            $imc = round($imc, 2);

            // Clasificación según OMS
            $clasificacion = $this->obtenerClasificacion($imc);
            $recomendaciones = $this->obtenerRecomendaciones($clasificacion);

            $registro = ImcRegistro::create([
                'usuario_id' => $request->user()->id,
                'peso' => $peso,
                'altura' => $altura,
                'imc_calculado' => $imc,
                'clasificacion' => $clasificacion,
                'recomendaciones' => $recomendaciones,
            ]);

            return response()->json([
                'message' => 'IMC calculado exitosamente',
                'registro' => $registro,
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al calcular IMC: ' . $e->getMessage()], 500);
        }
    }

    // Ver registro específico
    public function show($id)
    {
        try {
            $registro = ImcRegistro::findOrFail($id);
            return response()->json($registro, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }
    }

    // Eliminar registro
    public function destroy($id)
    {
        try {
            $registro = ImcRegistro::findOrFail($id);
            $registro->delete();

            return response()->json(['message' => 'Registro eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar registro'], 500);
        }
    }

    // Obtener historial con gráfica
    public function historial(Request $request)
    {
        try {
            $registros = ImcRegistro::where('usuario_id', $request->user()->id)
                ->orderBy('created_at', 'asc')
                ->get();

            $ultimoRegistro = $registros->last();

            return response()->json([
                'total_registros' => $registros->count(),
                'ultimo_imc' => $ultimoRegistro ? $ultimoRegistro->imc_calculado : null,
                'ultima_clasificacion' => $ultimoRegistro ? $ultimoRegistro->clasificacion : null,
                'historial' => $registros,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener historial'], 500);
        }
    }

    // Métodos privados auxiliares
    private function obtenerClasificacion($imc)
    {
        if ($imc < 18.5) return 'bajo_peso';
        if ($imc >= 18.5 && $imc < 25) return 'peso_normal';
        if ($imc >= 25 && $imc < 30) return 'sobrepeso';
        if ($imc >= 30 && $imc < 35) return 'obesidad_1';
        if ($imc >= 35 && $imc < 40) return 'obesidad_2';
        return 'obesidad_3';
    }

    private function obtenerRecomendaciones($clasificacion)
    {
        $recomendaciones = [
            'bajo_peso' => 'Se recomienda consultar con un nutriólogo para aumentar peso de manera saludable.',
            'peso_normal' => '¡Excelente! Mantén una alimentación balanceada y actividad física regular.',
            'sobrepeso' => 'Considera mejorar tus hábitos alimenticios y aumentar la actividad física.',
            'obesidad_1' => 'Es importante consultar con un médico y nutriólogo para un plan de pérdida de peso.',
            'obesidad_2' => 'Se recomienda atención médica inmediata para evitar complicaciones de salud.',
            'obesidad_3' => 'Necesitas atención médica urgente. Consulta con un especialista lo antes posible.',
        ];

        return $recomendaciones[$clasificacion] ?? 'Consulta con un profesional de la salud.';
    }
}