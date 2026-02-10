<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TestSalud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TestSaludController extends Controller
{
    // Listar todos los tests del usuario
    public function index(Request $request)
    {
        try {
            $tests = TestSalud::where('usuario_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($tests, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener tests'], 500);
        }
    }

    // Crear nuevo test de salud
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo_test' => 'required|in:estres,apnea_sueno,depresion,podometro',
            'respuestas' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $tipoTest = $request->tipo_test;
            $respuestas = $request->respuestas;

            // Calcular puntuación según tipo de test
            $resultado = $this->calcularResultado($tipoTest, $respuestas);

            $test = TestSalud::create([
                'usuario_id' => $request->user()->id,
                'tipo_test' => $tipoTest,
                'respuestas' => $respuestas,
                'puntuacion' => $resultado['puntuacion'],
                'nivel_resultado' => $resultado['nivel'],
                'interpretacion' => $resultado['interpretacion'],
                'recomendaciones' => $resultado['recomendaciones'],
            ]);

            return response()->json([
                'message' => 'Test completado exitosamente',
                'test' => $test,
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al guardar test: ' . $e->getMessage()], 500);
        }
    }

    // Ver test específico
    public function show($id)
    {
        try {
            $test = TestSalud::findOrFail($id);
            return response()->json($test, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Test no encontrado'], 404);
        }
    }

    // Obtener tests por tipo
    public function porTipo(Request $request, $tipo)
    {
        try {
            $tests = TestSalud::where('usuario_id', $request->user()->id)
                ->where('tipo_test', $tipo)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'tipo' => $tipo,
                'total' => $tests->count(),
                'tests' => $tests,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener tests'], 500);
        }
    }

    // Eliminar test
    public function destroy($id)
    {
        try {
            $test = TestSalud::findOrFail($id);
            $test->delete();

            return response()->json(['message' => 'Test eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar test'], 500);
        }
    }

    // Método privado para calcular resultados
    private function calcularResultado($tipoTest, $respuestas)
    {
        switch ($tipoTest) {
            case 'estres':
                return $this->calcularEstres($respuestas);
            case 'apnea_sueno':
                return $this->calcularApnea($respuestas);
            case 'depresion':
                return $this->calcularDepresion($respuestas);
            case 'podometro':
                return $this->calcularPodometro($respuestas);
            default:
                return [
                    'puntuacion' => 0,
                    'nivel' => 'bajo',
                    'interpretacion' => 'Test no reconocido',
                    'recomendaciones' => 'Consulte con un profesional',
                ];
        }
    }

    private function calcularEstres($respuestas)
    {
        $puntuacion = array_sum(array_values($respuestas));
        
        if ($puntuacion <= 10) {
            $nivel = 'bajo';
            $interpretacion = 'Tu nivel de estrés es bajo. Continúa con tus hábitos saludables.';
            $recomendaciones = 'Mantén una rutina de ejercicio y descanso adecuado.';
        } elseif ($puntuacion <= 20) {
            $nivel = 'moderado';
            $interpretacion = 'Tu nivel de estrés es moderado. Es importante prestar atención.';
            $recomendaciones = 'Practica técnicas de relajación, meditación o yoga.';
        } elseif ($puntuacion <= 30) {
            $nivel = 'alto';
            $interpretacion = 'Tu nivel de estrés es alto. Se recomienda tomar medidas.';
            $recomendaciones = 'Considera consultar con un psicólogo o terapeuta.';
        } else {
            $nivel = 'muy_alto';
            $interpretacion = 'Tu nivel de estrés es muy alto. Requiere atención inmediata.';
            $recomendaciones = 'Busca ayuda profesional lo antes posible.';
        }

        return compact('puntuacion', 'nivel', 'interpretacion', 'recomendaciones');
    }

    private function calcularApnea($respuestas)
    {
        $puntuacion = array_sum(array_values($respuestas));
        
        if ($puntuacion <= 5) {
            $nivel = 'bajo';
            $interpretacion = 'Bajo riesgo de apnea del sueño.';
            $recomendaciones = 'Mantén buenos hábitos de sueño.';
        } elseif ($puntuacion <= 10) {
            $nivel = 'moderado';
            $interpretacion = 'Riesgo moderado de apnea del sueño.';
            $recomendaciones = 'Consulta con un especialista en trastornos del sueño.';
        } else {
            $nivel = 'alto';
            $interpretacion = 'Alto riesgo de apnea del sueño.';
            $recomendaciones = 'Es importante realizar un estudio del sueño con un especialista.';
        }

        return compact('puntuacion', 'nivel', 'interpretacion', 'recomendaciones');
    }

    private function calcularDepresion($respuestas)
    {
        $puntuacion = array_sum(array_values($respuestas));
        
        if ($puntuacion <= 5) {
            $nivel = 'bajo';
            $interpretacion = 'No se detectan síntomas significativos de depresión.';
            $recomendaciones = 'Mantén conexiones sociales y actividades que disfrutes.';
        } elseif ($puntuacion <= 10) {
            $nivel = 'moderado';
            $interpretacion = 'Síntomas leves de depresión detectados.';
            $recomendaciones = 'Considera hablar con un profesional de salud mental.';
        } elseif ($puntuacion <= 15) {
            $nivel = 'alto';
            $interpretacion = 'Síntomas moderados de depresión.';
            $recomendaciones = 'Se recomienda consultar con un psicólogo o psiquiatra.';
        } else {
            $nivel = 'muy_alto';
            $interpretacion = 'Síntomas severos de depresión detectados.';
            $recomendaciones = 'Busca ayuda profesional inmediatamente. No estás solo.';
        }

        return compact('puntuacion', 'nivel', 'interpretacion', 'recomendaciones');
    }

    private function calcularPodometro($respuestas)
    {
        $pasos = $respuestas['pasos_diarios'] ?? 0;
        $puntuacion = $pasos;
        
        if ($pasos < 5000) {
            $nivel = 'bajo';
            $interpretacion = 'Actividad física insuficiente.';
            $recomendaciones = 'Intenta aumentar gradualmente tus pasos diarios hasta 10,000.';
        } elseif ($pasos < 10000) {
            $nivel = 'moderado';
            $interpretacion = 'Nivel de actividad física moderado.';
            $recomendaciones = 'Buen progreso. Intenta alcanzar 10,000 pasos diarios.';
        } else {
            $nivel = 'alto';
            $interpretacion = '¡Excelente nivel de actividad física!';
            $recomendaciones = 'Mantén este ritmo. Tu salud cardiovascular te lo agradecerá.';
        }

        return compact('puntuacion', 'nivel', 'interpretacion', 'recomendaciones');
    }
}