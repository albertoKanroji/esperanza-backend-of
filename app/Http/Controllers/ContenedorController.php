<?php
// app/Http/Controllers/ContenedorController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ContenedorController extends Controller
{
    /**
     * Método centralizado para realizar solicitudes HTTP desactivando la verificación SSL.
     *
     * @param string $url La URL a la que se enviará la solicitud.
     * @param array $data Los datos que se enviarán en la solicitud.
     * @return \Illuminate\Http\Client\Response La respuesta de la solicitud HTTP.
     */
    private function makeHttpRequest(string $url, array $data)
    {
        return Http::withOptions(['verify' => false])
            ->withHeaders(['Content-Type' => 'application/json'])
            ->put($url, $data);
    }

    private function makeHttpRequest2(string $url, array $data)
    {
        return Http::withOptions(['verify' => false])
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $data);
    }

    /**
     * Actualiza el contenedor y envía la información a la API externa.
     */
    public function update(Request $request, $folio)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'error'   => 'Unauthorized',
                    'message' => 'You must be authenticated to view this resource.'
                ], 401);
            }

            $pagado = $request->input('_pagado');
            $folio  = (string) $folio;

            $data = [
                'folio'    => $folio,
                '_pagado'  => $pagado,
            ];

            $response = $this->makeHttpRequest('https://esperanza.xromsys.com/nucleo/var/receive_data_update.php', $data);

            if ($response->successful()) {
                return response()->json(['message' => 'Contenedor actualizado y datos enviados'], 200);
            }

            return response()->json(['message' => 'Contenedor actualizado, pero error al enviar datos'], $response->status());
            
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error en update: ' . $e->getMessage());
            return response()->json(['message' => 'Error interno del servidor', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualiza información adicional del contenedor y la envía a la API externa.
     */
    public function updateAddContenedor(Request $request, $folio)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'error'   => 'Unauthorized',
                    'message' => 'You must be authenticated to view this resource.'
                ], 401);
            }

            $idSalida = $request->input('id_salida');
            $estado   = $request->input('estado');
            $fSalida  = $request->input('f_salida');
            $folio    = (string) $folio;

            $data = [
                'folio'     => $folio,
                'id_salida' => $idSalida,
                'estado'    => $estado,
                'f_salida'  => $fSalida,
            ];

            $response = $this->makeHttpRequest('https://esperanza.xromsys.com/nucleo/var/receive_data_updateAddContenedor.php', $data);

            if ($response->successful()) {
                return response()->json(['message' => 'Contenedor actualizado y datos enviados'], 200);
            }

            return response()->json(['message' => 'Contenedor actualizado, pero error al enviar datos'], $response->status());
            
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error en updateAddContenedor: ' . $e->getMessage());
            return response()->json(['message' => 'Error interno del servidor', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cierra la salida del contenedor enviando los datos a la API externa.
     * Se espera que la API requiera que el folio sea un entero.
     */
    public function cerrarSalida(Request $request, $folio)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'error'   => 'Unauthorized',
                    'message' => 'You must be authenticated to view this resource.'
                ], 401);
            }

            $folio = filter_var($folio, FILTER_VALIDATE_INT);
            if (!$folio) {
                return response()->json(['message' => 'Folio inválido'], 400);
            }

            $estado = $request->input('estado');

            $data = [
                'folio'  => $folio,
                'estado' => $estado,
            ];

            $response = $this->makeHttpRequest('https://esperanza.xromsys.com/nucleo/var/receive_data_updateCerrarSalida.php', $data);

            if ($response->successful()) {
                return response()->json(['message' => 'Contenedor actualizado y datos enviados'], 200);
            }

            return response()->json([
                'message'       => 'Contenedor actualizado, pero error al enviar datos',
                'response_body' => $response->body(),
                'status_code'   => $response->status()
            ], $response->status());
            
        } catch (\Exception $e) {
            Log::error('Error en cerrarSalida: ' . $e->getMessage());
            return response()->json(['message' => 'Error interno del servidor', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualiza el estado de las entradas por pagar y envía la información a la API externa.
     */
    public function updateEntradasPagar(Request $request, $folio)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'error'   => 'Unauthorized',
                    'message' => 'You must be authenticated to view this resource.'
                ], 401);
            }

            $pagado = $request->input('_pagado');
            $folio  = (string) $folio;

            $data = [
                'folio'   => $folio,
                '_pagado' => $pagado,
            ];

            $response = $this->makeHttpRequest('https://esperanza.xromsys.com/nucleo/var/receive_data_updateEntradasPagar.php', $data);

            if ($response->successful()) {
                return response()->json(['message' => 'Contenedor actualizado y datos enviados'], 200);
            }

            return response()->json(['message' => 'Contenedor actualizado, pero error al enviar datos'], $response->status());
            
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error en updateEntradasPagar: ' . $e->getMessage());
            return response()->json(['message' => 'Error interno del servidor', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualiza el status del contenedor y envía los datos a la API externa.
     */
    public function updateContenedorStatus(Request $request, $folio)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'error'   => 'Unauthorized',
                    'message' => 'You must be authenticated to view this resource.'
                ], 401);
            }

            $status = $request->input('status');
            $folio  = (string) $folio;

            $data = [
                'folio'  => $folio,
                'status' => $status,
            ];

            $response = $this->makeHttpRequest('https://esperanza.xromsys.com/nucleo/var/receive_data_updateContenedorStatus.php', $data);

            if ($response->successful()) {
                return response()->json(['message' => 'Contenedor actualizado y datos enviados'], 200);
            }

            return response()->json([
                'message'         => 'Contenedor actualizado, pero error al enviar datos',
                'response_body'   => $response->body(),
                'response_status' => $response->status()
            ], $response->status());
            
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error en updateContenedorStatus: ' . $e->getMessage());
            return response()->json(['message' => 'Error interno del servidor', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Crea una entrada de contenedores y envía la información a la API externa.
     */
    public function createEntradaContenedoresS(Request $request)
    {
        try {
            $validated = $request->validate([
                'fecha_i'       => 'required|date',
                'hora_i'        => 'required|date_format:H:i',
                'fecha_s'       => 'required|date',
                'hora_s'        => 'required|date_format:H:i',
                'id_cliente'    => 'required|integer',
                'id_transportista' => 'required|integer',
                'placas'        => 'required|string',
                'eco'           => 'required|string',
                'licencia'      => 'required|string',
                'operador'      => 'required|string',
                'observaciones' => 'required|string',
                '_key'          => 'required|string',
                'estado'        => 'required|string',
                'tipo'          => 'required|string'
            ]);

            $response = $this->makeHttpRequest2('https://esperanza.xromsys.com/nucleo/var/receive_data_createEntrada.php', $validated);

            if ($response->successful()) {
                return response()->json(['message' => 'Entrada creada y datos enviados'], 201);
            }

            return response()->json([
                'message'       => 'Entrada creada, pero error al enviar datos',
                'response_body' => $response->body()
            ], $response->status());
            
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error en createEntradaContenedoresS: ' . $e->getMessage());
            return response()->json(['message' => 'Error interno del servidor', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Crea un contenedor y envía la información a la API externa.
     */
    public function createContenedoresS(Request $request)
    {
        try {
            $validated = $request->validate([
                'no_contenedor'  => 'required|string',
                'sello'          => 'required|string',
                'ct'             => 'required|string',
                'estado'         => 'required|string',
                'pedimento'      => 'required|string',
                'tipo_contenedor'=> 'required|string',
                'id_cliente'     => 'required|string',
                'id_ingreso'     => 'required|string',
                'f_ingreso'      => 'required|string',
            ]);

            $response = $this->makeHttpRequest2('https://esperanza.xromsys.com/nucleo/var/receive_data_createContenedores.php', $validated);

            if ($response->successful()) {
                return response()->json(['message' => 'Contenedor creado y datos enviados'], 201);
            }

            return response()->json([
                'message'       => 'Contenedor creado, pero error al enviar datos',
                'response_body' => $response->body()
            ], $response->status());
            
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error en createContenedoresS: ' . $e->getMessage());
            return response()->json(['message' => 'Error interno del servidor', 'error' => $e->getMessage()], 500);
        }
    }
}
