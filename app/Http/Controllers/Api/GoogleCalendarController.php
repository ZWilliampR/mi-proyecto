<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GoogleCalendarController extends Controller
{
    private function getClient()
    {
        $client = new GoogleClient();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $client->addScope(Calendar::CALENDAR);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        
        return $client;
    }

    // Paso 1: Generar URL de autorización
    public function authorize(Request $request)
    {
        $client = $this->getClient();
        $authUrl = $client->createAuthUrl();

        return response()->json([
            'authorization_url' => $authUrl,
            'message' => 'Abre esta URL en tu navegador para autorizar el acceso a Google Calendar'
        ], 200);
    }

    // Paso 2: Callback de Google (guardar tokens)
    public function callback(Request $request)
    {
        try {
            $code = $request->input('code');
            
            if (!$code) {
                return response()->json(['error' => 'Código de autorización no proporcionado'], 400);
            }

            $client = $this->getClient();
            $token = $client->fetchAccessTokenWithAuthCode($code);

            if (isset($token['error'])) {
                return response()->json(['error' => $token['error']], 400);
            }

            // Devolver los tokens para que el usuario los guarde manualmente
            return response()->json([
                'message' => 'Autorización exitosa. Copia estos tokens y úsalos en el endpoint /api/google/save-tokens',
                'tokens' => [
                    'access_token' => $token['access_token'],
                    'refresh_token' => $token['refresh_token'] ?? null,
                    'expires_in' => $token['expires_in'] ?? 3600,
                ],
                'instrucciones' => 'Llama a POST /api/google/save-tokens con estos tokens y tu Bearer token de autenticación'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al conectar con Google Calendar',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Guardar tokens manualmente
    public function saveTokens(Request $request)
    {
        try {
            $usuario = auth()->user();
            
            $usuario->update([
                'google_access_token' => json_encode([
                    'access_token' => $request->input('access_token'),
                    'refresh_token' => $request->input('refresh_token'),
                    'expires_in' => $request->input('expires_in', 3600),
                ]),
                'google_refresh_token' => $request->input('refresh_token'),
                'google_token_expires_at' => Carbon::now()->addSeconds($request->input('expires_in', 3600)),
            ]);

            return response()->json([
                'message' => 'Tokens guardados exitosamente. Ahora puedes crear eventos en Google Calendar.',
                'user' => $usuario
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al guardar tokens',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Crear evento en Google Calendar
    public function createEvent(Request $request)
    {
        try {
            $usuario = auth()->user();

            if (!$usuario->google_access_token) {
                return response()->json([
                    'error' => 'Debes autorizar Google Calendar primero',
                    'message' => 'Llama al endpoint /api/google/authorize'
                ], 401);
            }

            $client = $this->getClient();
            $client->setAccessToken(json_decode($usuario->google_access_token, true));

            // Verificar si el token expiró y renovarlo
            if ($client->isAccessTokenExpired()) {
                if ($usuario->google_refresh_token) {
                    $client->fetchAccessTokenWithRefreshToken($usuario->google_refresh_token);
                    $newToken = $client->getAccessToken();
                    
                    $usuario->update([
                        'google_access_token' => json_encode($newToken),
                        'google_token_expires_at' => Carbon::now()->addSeconds($newToken['expires_in'] ?? 3600),
                    ]);
                } else {
                    return response()->json(['error' => 'Token expirado, autoriza nuevamente'], 401);
                }
            }

            $service = new Calendar($client);

            // Crear evento
            $event = new \Google\Service\Calendar\Event([
                'summary' => $request->input('titulo'),
                'description' => $request->input('descripcion'),
                'start' => [
                    'dateTime' => Carbon::parse($request->input('fecha_inicio'))->toRfc3339String(),
                    'timeZone' => 'America/Cancun',
                ],
                'end' => [
                    'dateTime' => Carbon::parse($request->input('fecha_fin'))->toRfc3339String(),
                    'timeZone' => 'America/Cancun',
                ],
                'reminders' => [
                    'useDefault' => false,
                    'overrides' => [
                        ['method' => 'popup', 'minutes' => $request->input('recordatorio_minutos', 30)],
                    ],
                ],
            ]);

            $calendarId = 'primary';
            $event = $service->events->insert($calendarId, $event);

            return response()->json([
                'message' => 'Evento creado exitosamente en Google Calendar',
                'event' => [
                    'id' => $event->getId(),
                    'link' => $event->getHtmlLink(),
                    'titulo' => $event->getSummary(),
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al crear evento',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Listar próximos eventos
    public function listEvents(Request $request)
    {
        try {
            $usuario = auth()->user();

            if (!$usuario->google_access_token) {
                return response()->json(['error' => 'Debes autorizar Google Calendar primero'], 401);
            }

            $client = $this->getClient();
            $client->setAccessToken(json_decode($usuario->google_access_token, true));

            if ($client->isAccessTokenExpired()) {
                if ($usuario->google_refresh_token) {
                    $client->fetchAccessTokenWithRefreshToken($usuario->google_refresh_token);
                    $newToken = $client->getAccessToken();
                    
                    $usuario->update([
                        'google_access_token' => json_encode($newToken),
                        'google_token_expires_at' => Carbon::now()->addSeconds($newToken['expires_in'] ?? 3600),
                    ]);
                }
            }

            $service = new Calendar($client);
            
            $optParams = [
                'maxResults' => 10,
                'orderBy' => 'startTime',
                'singleEvents' => true,
                'timeMin' => Carbon::now()->toRfc3339String(),
            ];

            $results = $service->events->listEvents('primary', $optParams);
            $events = $results->getItems();

            $eventList = [];
            foreach ($events as $event) {
                $eventList[] = [
                    'id' => $event->getId(),
                    'titulo' => $event->getSummary(),
                    'descripcion' => $event->getDescription(),
                    'inicio' => $event->getStart()->getDateTime(),
                    'fin' => $event->getEnd()->getDateTime(),
                    'link' => $event->getHtmlLink(),
                ];
            }

            return response()->json([
                'total' => count($eventList),
                'eventos' => $eventList
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener eventos',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Desconectar Google Calendar
    public function disconnect(Request $request)
    {
        try {
            $usuario = auth()->user();
            
            $usuario->update([
                'google_access_token' => null,
                'google_refresh_token' => null,
                'google_token_expires_at' => null,
            ]);

            return response()->json(['message' => 'Google Calendar desconectado exitosamente'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al desconectar'], 500);
        }
    }
}