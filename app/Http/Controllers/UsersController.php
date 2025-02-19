<?php

namespace App\Http\Controllers;

use App\Models\EntradaProducto;
use App\Models\Tarjetas;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use App\Models\Transfers;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;




class UsersController extends Controller
{
    public function updateUserInfo(Request $request, $id)
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You must be authenticated to view this resource.'
            ], 401);
        }
    
        // Obtener el usuario que se quiere actualizar
        $user = User::findOrFail($id); // Busca el usuario por ID
    
        // Validar los datos recibidos
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|string|email|max:255',
            'profile' => 'nullable|string|max:100',
            'rfc' => 'nullable|string|max:13',
            'status' => 'nullable|string|max:50',
        ]);
    
        // Actualiza los campos del usuario
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->email = $request->email;
        $user->profile = $request->profile;
        $user->status = $request->status;
        $user->rfc = $request->rfc;
    
        try {
            // Guardar cambios
            $user->save();
    
            // Retornar el usuario actualizado
            return response()->json([
                'message' => 'Usuario actualizado con éxito.',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar usuario: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al actualizar el usuario.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    
    
    
    public function porRfc(Request $request, $rfc)
    {
        try {
            // Verificar si el usuario está autenticado
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'You must be authenticated to view this resource.'
                ], 401);
            }
    
            // Obtener el clienteId y el RFC de la solicitud
            $clienteId = $request->query('clientes_id'); // Cambia aquí a 'clientes_id'
            // El RFC se pasa como parte de la ruta
            
            // Verificar si se proporcionan el clienteId y el RFC
            if (!$clienteId || !$rfc) {
                return response()->json([
                    'error' => 'Bad Request',
                    'message' => 'Both clienteId and RFC are required to retrieve users.'
                ], 400);
            }
    
            // Filtrar los usuarios por clienteId y RFC
            $user = User::where('id', $clienteId)
                        ->where('rfc', $rfc)
                        ->first();
    
            // Verificar si se encontró el usuario
            if (!$user) {
                return response()->json([
                    'message' => 'No users found for the provided clienteId and RFC.'
                ], 404);
            }
    
            // Retornar el usuario encontrado como JSON
            return response()->json($user);
        } catch (QueryException $e) {
            // Manejar errores de consulta a la base de datos
            return response()->json([
                'error' => 'Database Error',
                'message' => 'An error occurred while retrieving the user.'
            ], 500);
        } catch (\Exception $e) {
            // Manejar otros errores generales
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred.'
            ], 500);
        }
    }

    private function makeHttpRequest($url, $data)
    {
        try {
            // Realiza la solicitud HTTP con Laravel HTTP Client
            $response = Http::withOptions([
                'verify' => false // Desactiva la verificación SSL
            ])->post($url, $data);
    
            // Verifica si la respuesta fue exitosa (código HTTP 200-299)
            if ($response->failed()) {
                throw new \Exception('Error en la solicitud HTTP: ' . $response->status());
            }
    
            return $response->body();
        } catch (\Exception $e) {
            // Captura cualquier error y lanza una excepción
            throw new \Exception('Error en la solicitud HTTP: ' . $e->getMessage());
        }
    }
    

    public function obtenerContenedores(Request $request, $rfc)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'You must be authenticated to view this resource.'
                ], 401);
            }
    
            $clienteId = $request->query('clientes_id');
    
            if (empty($clienteId) || empty($rfc)) {
                return response()->json([
                    'error' => 'Bad Request',
                    'message' => 'Se requieren tanto clienteId como RFC para recuperar los contenedores.'
                ], 400);
            }
    
            $response = $this->makeHttpRequest('https://esperanza.xromsys.com/nucleo/var/consultasDev.php', [
                'rfc' => $rfc,
                'opcion' => 1,
            ]);
    
            if (empty($response)) {
                return response()->json([
                    'error' => 'No Response',
                    'message' => 'The external service did not return any response.'
                ], 500);
            }
    
            Log::info('Respuesta del servicio externo:', ['response' => $response]);
    
            $data = json_decode($response, true);
    
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'Invalid Response',
                    'message' => 'The external service returned invalid JSON.',
                    'raw_response' => $response,
                    'json_error' => json_last_error_msg()
                ], 500);
            }
    
            if (!isset($data['registros'])) {
                return response()->json([
                    'error' => 'No Data',
                    'message' => 'No se encontraron contenedores para los criterios proporcionados.'
                ], 404);
            }
    
            return response()->json([
                'status' => $data['status'],
                'total' => $data['total'],
                'n_page' => $data['n_page'],
                'paginado' => $data['paginado'],
                'registros' => $data['registros'],
            ], 200);
    
        } catch (QueryException $e) {
            Log::error('Error en la consulta de base de datos:', ['exception' => $e]);
            return response()->json([
                'error' => 'Database Error',
                'message' => 'An error occurred while retrieving the data.'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error en el servidor:', ['exception' => $e]);
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function obtenerContenedoresPorEntrada(Request $request, $rfc)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'You must be authenticated to view this resource.'
                ], 401);
            }
    
            $folio = $request->query('folio');
    
            if (empty($folio) || empty($rfc)) {
                return response()->json([
                    'error' => 'Bad Request',
                    'message' => 'Se requieren tanto clienteId como RFC para recuperar los contenedores.'
                ], 400);
            }
    
            $response = $this->makeHttpRequest('https://esperanza.xromsys.com/nucleo/var/consultasDev.php', [
                'rfc' => $rfc,
                'opcion' => 4,
                'folioEntrada' => $folio,
            ]);
    
            if (empty($response)) {
                return response()->json([
                    'error' => 'No Response',
                    'message' => 'The external service did not return any response.'
                ], 500);
            }
    
            Log::info('Respuesta del servicio externo:', ['response' => $response]);
    
            $data = json_decode($response, true);
    
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'Invalid Response',
                    'message' => 'The external service returned invalid JSON.',
                    'raw_response' => $response,
                    'json_error' => json_last_error_msg()
                ], 500);
            }
    
            if (!isset($data['registros'])) {
                return response()->json([
                    'error' => 'No Data',
                    'message' => 'No se encontraron contenedores para los criterios proporcionados.'
                ], 404);
            }
    
            return response()->json([
                'status' => $data['status'],
                'total' => $data['total'],
                'n_page' => $data['n_page'],
                'paginado' => $data['paginado'],
                'registros' => $data['registros'],
            ], 200);
    
        } catch (QueryException $e) {
            Log::error('Error en la consulta de base de datos:', ['exception' => $e]);
            return response()->json([
                'error' => 'Database Error',
                'message' => 'An error occurred while retrieving the data.'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error en el servidor:', ['exception' => $e]);
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function obtenerContenedoresPorSalida(Request $request, $rfc)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'You must be authenticated to view this resource.'
                ], 401);
            }
    
            $folio = $request->query('folio');
    
            if (empty($folio) || empty($rfc)) {
                return response()->json([
                    'error' => 'Bad Request',
                    'message' => 'Se requieren tanto clienteId como RFC para recuperar los contenedores.'
                ], 400);
            }
    
            $response = $this->makeHttpRequest('https://esperanza.xromsys.com/nucleo/var/consultasDev.php', [
                'rfc' => $rfc,
                'opcion' => 5,
                'folioSalida' => $folio,
            ]);
    
            if (empty($response)) {
                return response()->json([
                    'error' => 'No Response',
                    'message' => 'The external service did not return any response.'
                ], 500);
            }
    
            Log::info('Respuesta del servicio externo:', ['response' => $response]);
    
            $data = json_decode($response, true);
    
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'Invalid Response',
                    'message' => 'The external service returned invalid JSON.',
                    'raw_response' => $response,
                    'json_error' => json_last_error_msg()
                ], 500);
            }
    
            if (!isset($data['registros'])) {
                return response()->json([
                    'error' => 'No Data',
                    'message' => 'No se encontraron contenedores para los criterios proporcionados.'
                ], 404);
            }
    
            return response()->json([
                'status' => $data['status'],
                'total' => $data['total'],
                'n_page' => $data['n_page'],
                'paginado' => $data['paginado'],
                'registros' => $data['registros'],
            ], 200);
    
        } catch (QueryException $e) {
            Log::error('Error en la consulta de base de datos:', ['exception' => $e]);
            return response()->json([
                'error' => 'Database Error',
                'message' => 'An error occurred while retrieving the data.'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error en el servidor:', ['exception' => $e]);
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function obtenerContenedoresPorSalidaAbierta(Request $request, $rfc)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'You must be authenticated to view this resource.'
                ], 401);
            }
    
            $folio = $request->query('folio');
    
            if (empty($folio) || empty($rfc)) {
                return response()->json([
                    'error' => 'Bad Request',
                    'message' => 'Se requieren tanto clienteId como RFC para recuperar los contenedores.'
                ], 400);
            }
    
            $response = $this->makeHttpRequest('https://esperanza.xromsys.com/nucleo/var/consultasDev.php', [
                'rfc' => $rfc,
                'opcion' => 8,
                'folioSalida' => $folio,
            ]);
    
            if (empty($response)) {
                return response()->json([
                    'error' => 'No Response',
                    'message' => 'The external service did not return any response.'
                ], 500);
            }
    
            Log::info('Respuesta del servicio externo:', ['response' => $response]);
    
            $data = json_decode($response, true);
    
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'Invalid Response',
                    'message' => 'The external service returned invalid JSON.',
                    'raw_response' => $response,
                    'json_error' => json_last_error_msg()
                ], 500);
            }
    
            if (!isset($data['registros'])) {
                return response()->json([
                    'error' => 'No Data',
                    'message' => 'No se encontraron contenedores para los criterios proporcionados.'
                ], 404);
            }
    
            return response()->json([
                'status' => $data['status'],
                'total' => $data['total'],
                'n_page' => $data['n_page'],
                'paginado' => $data['paginado'],
                'registros' => $data['registros'],
            ], 200);
    
        } catch (QueryException $e) {
            Log::error('Error en la consulta de base de datos:', ['exception' => $e]);
            return response()->json([
                'error' => 'Database Error',
                'message' => 'An error occurred while retrieving the data.'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error en el servidor:', ['exception' => $e]);
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function obtenerContenedoresPorSalidaCerrada(Request $request, $rfc)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'You must be authenticated to view this resource.'
                ], 401);
            }
    
            $folio = $request->query('folio');
    
            if (empty($folio) || empty($rfc)) {
                return response()->json([
                    'error' => 'Bad Request',
                    'message' => 'Se requieren tanto clienteId como RFC para recuperar los contenedores.'
                ], 400);
            }
    
            $response = $this->makeHttpRequest('https://esperanza.xromsys.com/nucleo/var/consultasDev.php', [
                'rfc' => $rfc,
                'opcion' => 10,
                'folioSalida' => $folio,
            ]);
    
            if (empty($response)) {
                return response()->json([
                    'error' => 'No Response',
                    'message' => 'The external service did not return any response.'
                ], 500);
            }
    
            Log::info('Respuesta del servicio externo:', ['response' => $response]);
    
            $data = json_decode($response, true);
    
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'Invalid Response',
                    'message' => 'The external service returned invalid JSON.',
                    'raw_response' => $response,
                    'json_error' => json_last_error_msg()
                ], 500);
            }
    
            if (!isset($data['registros'])) {
                return response()->json([
                    'error' => 'No Data',
                    'message' => 'No se encontraron contenedores para los criterios proporcionados.'
                ], 404);
            }
    
            return response()->json([
                'status' => $data['status'],
                'total' => $data['total'],
                'n_page' => $data['n_page'],
                'paginado' => $data['paginado'],
                'registros' => $data['registros'],
            ], 200);
    
        } catch (QueryException $e) {
            Log::error('Error en la consulta de base de datos:', ['exception' => $e]);
            return response()->json([
                'error' => 'Database Error',
                'message' => 'An error occurred while retrieving the data.'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error en el servidor:', ['exception' => $e]);
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function obtenerContenedoresPorTraspaleo(Request $request, $rfc)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'You must be authenticated to view this resource.'
                ], 401);
            }
    
            $folio = $request->query('folio');
    
            if (empty($folio) || empty($rfc)) {
                return response()->json([
                    'error' => 'Bad Request',
                    'message' => 'Se requieren tanto clienteId como RFC para recuperar los contenedores.'
                ], 400);
            }
    
            $response = $this->makeHttpRequest('https://esperanza.xromsys.com/nucleo/var/consultasDev.php', [
                'rfc' => $rfc,
                'opcion' => 7,
                'folioTrasoaleo' => $folio,
            ]);
    
            if (empty($response)) {
                return response()->json([
                    'error' => 'No Response',
                    'message' => 'The external service did not return any response.'
                ], 500);
            }
    
            Log::info('Respuesta del servicio externo:', ['response' => $response]);
    
            $data = json_decode($response, true);
    
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'Invalid Response',
                    'message' => 'The external service returned invalid JSON.',
                    'raw_response' => $response,
                    'json_error' => json_last_error_msg()
                ], 500);
            }
    
            if (!isset($data['registros'])) {
                return response()->json([
                    'error' => 'No Data',
                    'message' => 'No se encontraron contenedores para los criterios proporcionados.'
                ], 404);
            }
    
            return response()->json([
                'status' => $data['status'],
                'total' => $data['total'],
                'n_page' => $data['n_page'],
                'paginado' => $data['paginado'],
                'registros' => $data['registros'],
            ], 200);
    
        } catch (QueryException $e) {
            Log::error('Error en la consulta de base de datos:', ['exception' => $e]);
            return response()->json([
                'error' => 'Database Error',
                'message' => 'An error occurred while retrieving the data.'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error en el servidor:', ['exception' => $e]);
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function obtenerEntradas(Request $request, $rfc)
    {
        try {
            // Verificar autenticación del usuario
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'You must be authenticated to view this resource.'
                ], 401);
            }
    
            // Validar clienteId y RFC
            $clienteId = $request->query('clientes_id');
            if (empty($clienteId) || empty($rfc)) {
                return response()->json([
                    'error' => 'Bad Request',
                    'message' => 'Se requieren tanto clienteId como RFC para recuperar los contenedores.'
                ], 400);
            }
    
            // Preparar datos para la solicitud al servicio externo
            $payload = [
                'rfc' => $rfc,
                'opcion' => 2,
            ];
    
            // Realizar solicitud HTTP al servicio externo
            $response = $this->makeHttpRequest('https://esperanza.xromsys.com/nucleo/var/consultasDev.php', $payload);
    
            // Verificar respuesta del servicio externo
            if (empty($response)) {
                return response()->json([
                    'error' => 'No Response',
                    'message' => 'The external service did not return any response.'
                ], 500);
            }
    
            Log::info('Respuesta del servicio externo:', ['response' => $response]);
    
            // Decodificar respuesta JSON
            $data = json_decode($response, true);
    
            // Validar formato de JSON
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'Invalid Response',
                    'message' => 'The external service returned invalid JSON.',
                    'raw_response' => $response,
                    'json_error' => json_last_error_msg()
                ], 500);
            }
    
            // Validar datos de respuesta
            if (!isset($data['data']) || empty($data['data'])) {
                return response()->json([
                    'error' => 'No Data',
                    'message' => 'No se encontraron contenedores para los criterios proporcionados.'
                ], 404);
            }
    
            // Retornar datos exitosos
            return response()->json([
                'registros' => $data['data'],
            ], 200);
    
        } catch (QueryException $e) {
            // Manejo de errores de base de datos
            Log::error('Error en la consulta de base de datos:', ['exception' => $e]);
            return response()->json([
                'error' => 'Database Error',
                'message' => 'An error occurred while retrieving the data.'
            ], 500);
        } catch (\Exception $e) {
            // Manejo de otros errores
            Log::error('Error en el servidor:', ['exception' => $e]);
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function obtenerSalidas(Request $request, $rfc)
    {
        try {
            // Verificar autenticación del usuario
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'You must be authenticated to view this resource.'
                ], 401);
            }
    
            // Validar clienteId y RFC
            $clienteId = $request->query('clientes_id');
            if (empty($clienteId) || empty($rfc)) {
                return response()->json([
                    'error' => 'Bad Request',
                    'message' => 'Se requieren tanto clienteId como RFC para recuperar los contenedores.'
                ], 400);
            }
    
            // Preparar datos para la solicitud al servicio externo
            $payload = [
                'rfc' => $rfc,
                'opcion' => 3,
            ];
    
            // Realizar solicitud HTTP al servicio externo
            $response = $this->makeHttpRequest('https://esperanza.xromsys.com/nucleo/var/consultasDev.php', $payload);
    
            // Verificar respuesta del servicio externo
            if (empty($response)) {
                return response()->json([
                    'error' => 'No Response',
                    'message' => 'The external service did not return any response.'
                ], 500);
            }
    
            Log::info('Respuesta del servicio externo:', ['response' => $response]);
    
            // Decodificar respuesta JSON
            $data = json_decode($response, true);
    
            // Validar formato de JSON
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'Invalid Response',
                    'message' => 'The external service returned invalid JSON.',
                    'raw_response' => $response,
                    'json_error' => json_last_error_msg()
                ], 500);
            }
    
            // Validar datos de respuesta
            if (!isset($data['data']) || empty($data['data'])) {
                return response()->json([
                    'error' => 'No Data',
                    'message' => 'No se encontraron contenedores para los criterios proporcionados.'
                ], 404);
            }
    
            // Retornar datos exitosos
            return response()->json([
                'registros' => $data['data'],
            ], 200);
    
        } catch (QueryException $e) {
            // Manejo de errores de base de datos
            Log::error('Error en la consulta de base de datos:', ['exception' => $e]);
            return response()->json([
                'error' => 'Database Error',
                'message' => 'An error occurred while retrieving the data.'
            ], 500);
        } catch (\Exception $e) {
            // Manejo de otros errores
            Log::error('Error en el servidor:', ['exception' => $e]);
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function obtenerTraspaleos(Request $request, $rfc)
    {
        try {
            // Verificar autenticación del usuario
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'You must be authenticated to view this resource.'
                ], 401);
            }
    
            // Validar clienteId y RFC
            $clienteId = $request->query('clientes_id');
            if (empty($clienteId) || empty($rfc)) {
                return response()->json([
                    'error' => 'Bad Request',
                    'message' => 'Se requieren tanto clienteId como RFC para recuperar los contenedores.'
                ], 400);
            }
    
            // Preparar datos para la solicitud al servicio externo
            $payload = [
                'rfc' => $rfc,
                'opcion' => 6,
            ];
    
            // Realizar solicitud HTTP al servicio externo
            $response = $this->makeHttpRequest('https://esperanza.xromsys.com/nucleo/var/consultasDev.php', $payload);
    
            // Verificar respuesta del servicio externo
            if (empty($response)) {
                return response()->json([
                    'error' => 'No Response',
                    'message' => 'The external service did not return any response.'
                ], 500);
            }
    
            Log::info('Respuesta del servicio externo:', ['response' => $response]);
    
            // Decodificar respuesta JSON
            $data = json_decode($response, true);
    
            // Validar formato de JSON
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'Invalid Response',
                    'message' => 'The external service returned invalid JSON.',
                    'raw_response' => $response,
                    'json_error' => json_last_error_msg()
                ], 500);
            }
    
            // Validar datos de respuesta
            if (!isset($data['data']) || empty($data['data'])) {
                return response()->json([
                    'error' => 'No Data',
                    'message' => 'No se encontraron contenedores para los criterios proporcionados.'
                ], 404);
            }
    
            // Retornar datos exitosos
            return response()->json([
                'registros' => $data['data'],
            ], 200);
    
        } catch (QueryException $e) {
            // Manejo de errores de base de datos
            Log::error('Error en la consulta de base de datos:', ['exception' => $e]);
            return response()->json([
                'error' => 'Database Error',
                'message' => 'An error occurred while retrieving the data.'
            ], 500);
        } catch (\Exception $e) {
            // Manejo de otros errores
            Log::error('Error en el servidor:', ['exception' => $e]);
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    
    public function transferFunds(Request $request)
    {
            // Verificar si el usuario está autenticado
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'You must be authenticated to view this resource.'
                ], 401);
            }

        $request->validate([
            'id_sender' => 'required|integer|exists:users,id',
            'id_receiver' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|min:0.01', // Asume que la cantidad mínima a transferir es 0.01
        ]);

        $senderId = $request->id_sender;
        $receiverId = $request->id_receiver;
        $amount = $request->amount;

        // Verificar que el remitente y el receptor no sean el mismo usuario
        if ($senderId == $receiverId) {
            return response()->json(['message' => 'No puedes transferir dinero a ti mismo'], 400);
        }

        $sender = User::find($senderId);
        $receiver = User::find($receiverId);

        // Verificar si el usuario que envía tiene suficiente saldo
        if ($sender->saldo < $amount) {
            return response()->json(['message' => 'Saldo insuficiente'], 400);
        }

        try {
            // Descontar el monto del saldo del remitente
            $sender->saldo -= $amount;
            $sender->save();

            // Aumentar el monto del saldo del receptor
            $receiver->saldo += $amount;
            $receiver->save();

            // Registrar la transferencia
            Transfers::create([
                'id_sender' => $senderId,
                'id_receiver' => $receiverId,
                'amount' => $amount,
                'description' => $request->description ?? 'Transferencia realizada',
                'status' => 'completed', // O el estado que consideres adecuado
            ]);

            return response()->json(['message' => 'Transferencia realizada con éxito'], 200);
        } catch (\Exception $e) {
            Log::error('Transfer Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error en la transferencia', 'error' => $e->getMessage()], 500);
        }
        
    }

    public function index()
    {
        try {
            // Verificar si el usuario está autenticado
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'You must be authenticated to view this resource.'
                ], 401);
            }

            // Obtener todos los usuarios
            $users = User::all();

            // Retornar la lista de usuarios como JSON
            return response()->json($users);
        } catch (QueryException $e) {
            // Manejar errores de consulta a la base de datos
            return response()->json([
                'error' => 'Database Error',
                'message' => 'An error occurred while retrieving the users.'
            ], 500);
        } catch (\Exception $e) {
            // Manejar otros errores generales
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred.'
            ], 500);
        }
    }

    public function getDebtsUsers()
    {
        try {
            // Verificar si el usuario está autenticado
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'You must be authenticated to view this resource.'
                ], 401);
            }
    
            $payload = [
                'opcion' => 9,
                'page' => 0, // Puedes cambiarlo según sea necesario
                'n_page' => 30 // Puedes cambiarlo según sea necesario
            ];
    
            $response = $this->makeHttpRequest('https://esperanza.xromsys.com/nucleo/var/consultasDev.php', $payload);
    
            if (empty($response)) {
                return response()->json([
                    'error' => 'No Response',
                    'message' => 'The external service did not return any response.'
                ], 500);
            }
    
            $data = json_decode($response, true);
    
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'Invalid Response',
                    'message' => 'The external service returned invalid JSON.',
                    'raw_response' => $response,
                    'json_error' => json_last_error_msg()
                ], 500);
            }
    
            return response()->json($data, 200);
        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Database Error',
                'message' => 'An error occurred while retrieving the users.'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred.'
            ], 500);
        }
    }
    

    public function getSaldo(Request $request)
    {
        try {
            // Verificar si el usuario está autenticado
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'You must be authenticated to view this resource.'
                ], 401);
            }

            // Obtener el ID del usuario del request
            $id = $request->input('id');

            // Obtener el usuario por ID
            $user = User::find($id);

            // Verificar si el usuario existe
            if (!$user) {
                return response()->json([
                    'error' => 'Not Found',
                    'message' => 'User not found.'
                ], 404);
            }

            // Retornar el saldo del usuario como JSON
            return response()->json([
                'id' => $user->id,
                'saldo' => $user->saldo
            ]);
        } catch (QueryException $e) {
            // Manejar errores de consulta a la base de datos
            return response()->json([
                'error' => 'Database Error',
                'message' => 'An error occurred while retrieving the user saldo.'
            ], 500);
        } catch (\Exception $e) {
            // Manejar otros errores generales
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred.'
            ], 500);
        }
    }
    // Función para obtener usuarios con perfil ADMIN
    public function getAdmins()
    {
        return $this->getUsersByProfile('ADMIN');
    }

    // Función para obtener usuarios con perfil EMPLOYEE
    public function getEmployees()
    {
        return $this->getUsersByProfile('EMPLOYEE');
    }

    // Función para obtener usuarios con perfil CLIENT
    public function getClients()
    {
        return $this->getUsersByProfile('CLIENT');
    }

    // Función para obtener usuarios con perfil SUPPORT
    public function getSupports()
    {
        return $this->getUsersByProfiles(['SUPPORT', 'ADMIN']);
    }

    private function getUsersByProfiles(array $profiles)
{
    try {
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You must be authenticated to view this resource.'
            ], 401);
        }

        $users = User::whereIn('profile', $profiles)->get();

        return response()->json($users);
    } catch (QueryException $e) {
        return response()->json([
            'error' => 'Database Error',
            'message' => 'An error occurred while retrieving the users.'
        ], 500);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Server Error',
            'message' => 'An unexpected error occurred.'
        ], 500);
    }
}


    // Función general para obtener usuarios por perfil
    private function getUsersByProfile($profile)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'You must be authenticated to view this resource.'
                ], 401);
            }

            $users = User::where('profile', $profile)->get();

            return response()->json($users);
        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Database Error',
                'message' => 'An error occurred while retrieving the users.'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred.'
            ], 500);
        }
    }
    public function getProductsByClientWithDebtSum(Request $request)
    {
        try {
            $clientes_id = $request->input('clientes_id');
            $productos = EntradaProducto::where('clientes_id', $clientes_id)
                ->whereHas('producto', function ($query) {
                    $query->where('estado_deuda', 'NO');
                })
                ->with('producto')
                ->get();

            $totalDeuda = EntradaProducto::where('clientes_id', $clientes_id)
                ->sum('total_deuda');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'productos' => $productos,
                    'total_deuda' => $totalDeuda
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function getProductsByClientWithSum(Request $request)
    {
        try {
            $clientes_id = $request->input('clientes_id');
            $productos = EntradaProducto::where('clientes_id', $clientes_id)
                ->whereHas('producto', function ($query) {
                    $query->where('estado_deuda', 'SI');
                })
                ->with('producto')
                ->get();

            $totalDeuda = EntradaProducto::where('clientes_id', $clientes_id)
                ->sum('total_deuda');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'productos' => $productos,
                    'total_deuda' => $totalDeuda
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function getProductosSinDeuda(Request $request)
    {
        try {
            $clientes_id = $request->input('clientes_id');
            // Verificar si el usuario está autenticado
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'You must be authenticated to view this resource.'
                ], 401);
            }

            // Obtener productos del cliente con total_deuda igual a 0
            $productos = EntradaProducto::where('clientes_id', $clientes_id)
                ->where('total_deuda', 0)
                ->with('producto') // Asegúrate de que la relación 'producto' esté definida en el modelo EntradaProducto
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $productos
            ], 200);
        } catch (QueryException $e) {
            // Manejar errores de consulta a la base de datos
            return response()->json([
                'error' => 'Database Error',
                'message' => 'An error occurred while retrieving the products.'
            ], 500);
        } catch (\Exception $e) {
            // Manejar otros errores generales
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred.'
            ], 500);
        }
    }
    public function getDetalleProducto(Request $request)
    {
        try {
            $clientes_id = $request->input('clientes_id');
            $productoId = $request->input('productoId');
            // Verificar si el usuario está autenticado
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'You must be authenticated to view this resource.'
                ], 401);
            }

            // Obtener el detalle del producto específico del cliente
            $entradaProducto = EntradaProducto::where('clientes_id', $clientes_id)
                ->where('products_id', $productoId)
                ->with('producto') // Asegúrate de que la relación 'producto' esté definida en el modelo EntradaProducto
                ->first();

            // Verificar si el producto existe
            if (!$entradaProducto) {
                return response()->json([
                    'error' => 'Not Found',
                    'message' => 'Product not found for this client.'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $entradaProducto
            ], 200);
        } catch (QueryException $e) {
            // Manejar errores de consulta a la base de datos
            return response()->json([
                'error' => 'Database Error',
                'message' => 'An error occurred while retrieving the product details.'
            ], 500);
        } catch (\Exception $e) {
            // Manejar otros errores generales
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred.'
            ], 500);
        }
    }
    public function addTarjetaToUser(Request $request, $userId)
    {
        // Validar la solicitud
        $validatedData = $request->validate([
            'token' => 'required|string', // El token de la tarjeta
            'card' => 'required|string|min:13|max:16', // Número de tarjeta entre 13 y 16 dígitos
            'year' => 'required|string|digits:4', // Año de expiración con 4 dígitos
            'mes' => 'required|string|size:2', // Mes de expiración en formato de 2 dígitos, // Mes de expiración entre 1 y 12
            'ccv' => 'required|string|min:3|max:4', // CVV de 3 o 4 dígitos
            'cardHolder' => 'required|string|max:255', // Nombre del titular de la tarjeta
        ]);
    
        try {
            // Buscar el usuario por ID
            $user = User::findOrFail($userId);
    
            // Crear una nueva tarjeta
            $tarjeta = new Tarjetas([
                'number' => encrypt($validatedData['card']), // Encriptar el número de la tarjeta
                'year' => $validatedData['year'],
                'mes' => $validatedData['mes'],
                'cvv' => encrypt($validatedData['ccv']), // Encriptar el CVV
                'titular' => $validatedData['cardHolder'], // Guardar el nombre del titular
                'token' => $validatedData['token'], // Guardar el token de la tarjeta
            ]);
            $tarjeta->save();
    
            // Relacionar la tarjeta con el usuario
            $user->tarjetas()->attach($tarjeta->id);
    
            // Retornar una respuesta exitosa
            return response()->json([
                'status' => 'success',
                'message' => 'Tarjeta añadida exitosamente al usuario.',
                'data' => [
                    'user' => $user,
                    'tarjeta' => [
                        'id' => $tarjeta->id,
                        'last_four_digits' => substr($validatedData['card'], -4), // Mostrar solo los últimos 4 dígitos
                        'year' => $tarjeta->year,
                        'mes' => $tarjeta->mes,
                        'holder_name' => $tarjeta->holder_name,
                    ],
                ],
            ], 201);
    
        } catch (\Exception $e) {
            // Manejar cualquier error
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al agregar la tarjeta al usuario.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteTarjetaFromUser(Request $request, $userId, $tarjetaId)
{
    try {
        // Find the user by ID
        $user = User::findOrFail($userId);

        // Find the tarjeta (card) by ID
        $tarjeta = Tarjetas::findOrFail($tarjetaId);

        // Remove the relationship between the user and the card
        $user->tarjetas()->detach($tarjetaId);

        // Delete the card from the tarjetas table
        $tarjeta->delete();

        // Return a success response
        return response()->json([
            'status' => 'success',
            'message' => 'Tarjeta eliminada exitosamente.',
        ], 200);

    } catch (\Exception $e) {
        // Handle any errors during the deletion process
        return response()->json([
            'status' => 'error',
            'message' => 'Ocurrió un error al eliminar la tarjeta.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    
    public function getUserTarjetas(Request $request)
    {
        try {
            $clientes_id = $request->input('clientes_id');
    
            // Buscar el usuario por ID
            $user = User::findOrFail($clientes_id);
    
            // Obtener todas las tarjetas del usuario
            $tarjetas = $user->tarjetas->map(function ($tarjeta) {
                try {
                    // Attempt to decrypt the number and CVV fields
                    $decryptedCardNumber = Crypt::decryptString($tarjeta->number);
                    $decryptedCVV = Crypt::decryptString($tarjeta->cvv);
                } catch (\Exception $e) {
                    // If decryption fails, assume the data is already in plain text or in s:16:"..." format
                    $decryptedCardNumber = $tarjeta->number;
                    $decryptedCVV = $tarjeta->cvv;
                }
    
                // Remove s:16:"..." and s:3:"..." formatting if present
                if (preg_match('/^s:\d+:"(.*)";$/', $decryptedCardNumber, $matches)) {
                    $decryptedCardNumber = $matches[1];
                }
                if (preg_match('/^s:\d+:"(.*)";$/', $decryptedCVV, $matches)) {
                    $decryptedCVV = $matches[1];
                }
    
                // Return the cleaned-up or decrypted values
                return [
                    'id' => $tarjeta->id,
                    'token' => $tarjeta->token,
                    'number' => $decryptedCardNumber, // Cleaned-up card number
                    'cvv' => $decryptedCVV,           // Cleaned-up CVV
                    'year' => $tarjeta->year,
                    'mes' => $tarjeta->mes,
                    'titular' => $tarjeta->titular,
                ];
            });
    
            // Return a successful response
            return response()->json([
                'status' => 'success',
                'data' => $tarjetas,
            ], 200);
        } catch (\Exception $e) {
            // Handle any errors that occur during the process
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al obtener las tarjetas del usuario.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    

    
    

    public function getTarjetaDetail($tarjetaId)
    {
        try {
            // Buscar la tarjeta por ID
            $tarjeta = Tarjetas::findOrFail($tarjetaId);

            // Retornar una respuesta exitosa
            return response()->json([
                'status' => 'success',
                'data' => $tarjeta,
            ], 200);
        } catch (\Exception $e) {
            // Manejar cualquier error
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al obtener el detalle de la tarjeta.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getUserIdByEmail(Request $request)
    {
        try {
            $clientes_id = $request->input('clientes_id');
            // Verificar si el usuario está autenticado
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'You must be authenticated to view this resource.'
                ], 401);
            }
    
            $request->validate([
                'email' => 'required|string|email|max:255',
            ]);
    
            $user = User::where('email', $request->email)->first();
    
            if ($user) {
                return response()->json(['id' => $user->id], 200);
            } else {
                return response()->json(['message' => 'User not found'], 404);
            }
        } catch (\Exception $e) {
            // Manejar cualquier excepción que ocurra
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An error occurred while processing your request.',
                'details' => $e->getMessage() // Puedes comentar esta línea en producción para no exponer detalles sensibles
            ], 500);
        }
    }
    
    public function show(Request $request, $id) // Añadir Request como parámetro
    {
        $clientes_id = $request->input('clientes_id');
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You must be authenticated to view this resource.'
            ], 401);
        }

        $user = User::find($id);

        if ($user) {
            return response()->json($user, 200);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    public function getInfoUser(Request $request, $id) {
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You must be authenticated to view this resource.'
            ], 401);
        }
        
        $user = User::find($id);
    
        if ($user) {
            return response()->json($user, 200);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }
    
    public function updateName(Request $request, $id)
    {
        // Verificar si el usuario autenticado es el mismo que se quiere actualizar
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You must be authenticated to view this resource.'
            ], 401);
        }
        // Validar los datos de entrada
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            // Agrega otras validaciones si es necesario
        ]);

        try {
            // Buscar al usuario por ID
            $user = User::findOrFail($id);

            // Actualizar el nombre del usuario
            $user->name = $validatedData['name'];
            $user->save();

            return response()->json([
                'message' => 'Nombre actualizado correctamente',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar el nombre'], 500);
        }
    }

    public function updateEmail(Request $request, $id)
    {
        // Verificar si el usuario autenticado es el mismo que se quiere actualizar
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You must be authenticated to view this resource.'
            ], 401);
        }
        // Validar los datos de entrada
        $validatedData = $request->validate([
            'email' => 'required|string|max:255',
            // Agrega otras validaciones si es necesario
        ]);

        try {
            // Buscar al usuario por ID
            $user = User::findOrFail($id);

            // Actualizar el nombre del usuario
            $user->email = $validatedData['email'];
            $user->save();

            return response()->json([
                'message' => 'Email actualizado correctamente',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar el Email'], 500);
        }
    }

    public function updateRfc(Request $request, $id)
    {
        // Verificar si el usuario autenticado es el mismo que se quiere actualizar
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You must be authenticated to view this resource.'
            ], 401);
        }
        // Validar los datos de entrada
        $validatedData = $request->validate([
            'rfc' => 'required|string|max:255',
            // Agrega otras validaciones si es necesario
        ]);

        try {
            // Buscar al usuario por ID
            $user = User::findOrFail($id);

            // Actualizar el nombre del usuario
            $user->rfc = $validatedData['rfc'];
            $user->save();

            return response()->json([
                'message' => 'Rfc actualizado correctamente',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar el Rfc'], 500);
        }
    }

    public function updatePassword(Request $request, $id)
    {
        // Verificar si el usuario autenticado es el mismo que se quiere actualizar
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You must be authenticated to view this resource.'
            ], 401);
        }
    
        // Validar los datos de entrada
        $validatedData = $request->validate([
            'password' => 'required|string|min:8',
        ]);
    
        try {
            // Buscar al usuario por ID
            $user = User::findOrFail($id);
    
            // Encriptar la nueva contraseña
            $hashedPassword = Hash::make($validatedData['password']);
    
            // Actualizar la contraseña del usuario
            $user->password = $hashedPassword;
            $user->save();
    
            return response()->json([
                'message' => 'Contraseña actualizada correctamente',
                'user' => $user
            ], 200);
        } catch (ValidationException $e) {
            // Manejar errores de validación
            return response()->json([
                'error' => 'Validation Error',
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Manejar otros errores
            return response()->json(['error' => 'Error al actualizar la contraseña'], 500);
        }
    }
    
}
