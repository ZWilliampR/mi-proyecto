<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OpenFdaController extends Controller
{
    private $baseUrl = 'https://api.fda.gov/drug';

    // Buscar medicamento por nombre
    public function buscarMedicamento(Request $request)
    {
        try {
            $nombreMedicamento = $request->input('nombre');

            if (!$nombreMedicamento) {
                return response()->json(['error' => 'El nombre del medicamento es requerido'], 400);
            }

            $response = Http::get("{$this->baseUrl}/label.json", [
                'search' => 'openfda.brand_name:"' . $nombreMedicamento . '" OR openfda.generic_name:"' . $nombreMedicamento . '"',
                'limit' => 1
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Error al consultar OpenFDA'], 500);
            }

            $data = $response->json();

            if (empty($data['results'])) {
                return response()->json([
                    'message' => 'Medicamento no encontrado en la base de datos de la FDA',
                    'nombre_buscado' => $nombreMedicamento
                ], 404);
            }

            $medicamento = $data['results'][0];

            // Extraer información relevante
            $info = [
                'nombre_comercial' => $medicamento['openfda']['brand_name'][0] ?? 'No disponible',
                'nombre_generico' => $medicamento['openfda']['generic_name'][0] ?? 'No disponible',
                'fabricante' => $medicamento['openfda']['manufacturer_name'][0] ?? 'No disponible',
                'ingredientes_activos' => $medicamento['active_ingredient'][0] ?? 'No disponible',
                'proposito' => $medicamento['purpose'][0] ?? 'No disponible',
                'indicaciones' => $medicamento['indications_and_usage'][0] ?? 'No disponible',
                'advertencias' => $medicamento['warnings'][0] ?? 'No disponible',
                'efectos_secundarios' => $medicamento['adverse_reactions'][0] ?? 'No disponible',
                'dosis' => $medicamento['dosage_and_administration'][0] ?? 'No disponible',
                'interacciones' => $medicamento['drug_interactions'][0] ?? 'No disponible',
                'contraindicaciones' => $medicamento['contraindications'][0] ?? 'No disponible',
            ];

            return response()->json([
                'message' => 'Medicamento encontrado',
                'medicamento' => $info
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al buscar medicamento',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Verificar interacciones entre medicamentos
    public function verificarInteracciones(Request $request)
    {
        try {
            $medicamentos = $request->input('medicamentos'); // Array de nombres

            if (!is_array($medicamentos) || count($medicamentos) < 2) {
                return response()->json(['error' => 'Debes proporcionar al menos 2 medicamentos'], 400);
            }

            $interacciones = [];

            foreach ($medicamentos as $medicamento) {
                $response = Http::get("{$this->baseUrl}/label.json", [
                    'search' => 'openfda.brand_name:"' . $medicamento . '"',
                    'limit' => 1
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (!empty($data['results'])) {
                        $interacciones[$medicamento] = [
                            'nombre' => $medicamento,
                            'interacciones_conocidas' => $data['results'][0]['drug_interactions'][0] ?? 'No disponible'
                        ];
                    }
                }
            }

            return response()->json([
                'message' => 'Análisis de interacciones completado',
                'medicamentos_analizados' => $medicamentos,
                'resultados' => $interacciones,
                'recomendacion' => 'Consulta con tu médico antes de combinar estos medicamentos'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al verificar interacciones',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Buscar eventos adversos reportados
    public function eventosAdversos(Request $request)
    {
        try {
            $nombreMedicamento = $request->input('nombre');

            if (!$nombreMedicamento) {
                return response()->json(['error' => 'El nombre del medicamento es requerido'], 400);
            }

            $response = Http::get("{$this->baseUrl}/event.json", [
                'search' => 'patient.drug.openfda.brand_name:"' . $nombreMedicamento . '"',
                'limit' => 10
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Error al consultar eventos adversos'], 500);
            }

            $data = $response->json();

            if (empty($data['results'])) {
                return response()->json([
                    'message' => 'No se encontraron eventos adversos reportados',
                    'medicamento' => $nombreMedicamento
                ], 200);
            }

            $eventos = [];
            foreach ($data['results'] as $evento) {
                $eventos[] = [
                    'reacciones' => $evento['patient']['reaction'] ?? [],
                    'gravedad' => $evento['serious'] ?? 'No especificado',
                    'pais' => $evento['occurcountry'] ?? 'No especificado',
                ];
            }

            return response()->json([
                'message' => 'Eventos adversos encontrados',
                'medicamento' => $nombreMedicamento,
                'total_eventos' => count($eventos),
                'eventos' => $eventos,
                'nota' => 'Estos son reportes, no necesariamente comprobados. Consulta con tu médico.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al buscar eventos adversos',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}