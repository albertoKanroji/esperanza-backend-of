<?php
// app/Http/Controllers/ContenedorController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Contenedor;
use App\Models\EntradaContenedores;
use App\Models\createContenedores;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log; // Aquí la importación correcta de Log
use Illuminate\Support\Facades\DB;


class ContenedorController extends Controller
{
    
    public function update(Request $request, $folio)
    {
        try {
            // Validar la solicitud
            $validated = $request->validate([
                '_pagado' => 'required|string', // Espera 'SI' o 'NO' en el request
            ]);
    
            // Convertir $folio a string si es necesario
            $folio = (string) $folio;
    
            // Buscar el contenedor por el folio
            $contenedor = Contenedor::find($folio);
    
            if (!$contenedor) {
                return response()->json(['message' => 'Contenedor no encontrado', 'folio' => $folio], 404);
            }
    
            // Convertir 'SI' o 'NO' a 1 o 0, según sea necesario
            $estado = ($validated['_pagado'] === 'SI') ? 1 : 0;
    
            // Actualizar el campo estado
            $contenedor->estado = $estado;
            $contenedor->save();
    
            // Enviar los datos a otro servidor (si es necesario)
            $response = Http::post('http://demo11.xrom.cc/nucleo/var/receive_data_update.php', [
                'folio' => $folio,
                '_pagado' => $validated['_pagado'],
            ]);
    
            if ($response->successful()) {
                return response()->json(['message' => 'Contenedor actualizado y datos enviados'], 200);
            } else {
                return response()->json(['message' => 'Contenedor actualizado, pero error al enviar datos'], $response->status());
            }
    
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error interno del servidor', 'error' => $e->getMessage()], 500);
        }
    }
    
    
    public function updateContenedorStatus(Request $request, $folio)
    {
        try {
            // Validar la solicitud
            $validated = $request->validate([
                'status' => 'required|string',
                // Agrega otras validaciones si es necesario
            ]);

            // Buscar el contenedor por la clave primaria
            $contenedor = Contenedor::find($folio);

            // Verificar si el contenedor existe
            if (!$contenedor) {
                return response()->json(['message' => 'Contenedor no encontrado'], 404);
            }

            // Actualizar el campo _pagado
            $contenedor->status = $validated['status'];

            // Enviar los datos a otro servidor
            $response = Http::post('http://demo11.xrom.cc/nucleo/var/receive_data_updateContenedorStatus.php', [
                'folio' => $folio,
                'status' => $validated['status'],
                // Agrega otros campos si es necesario
            ]);

            if ($response->successful()) {
                return response()->json(['message' => 'Contenedor actualizado y datos enviados'], 200);
            } else {
                return response()->json([
                    'message' => 'Contenedor actualizado, pero error al enviar datos',
                    'response_body' => $response->body(), // Include the response body
                    'response_status' => $response->status() // Include the status code
                ], $response->status());
            }
            

        } catch (ValidationException $e) {
            // Manejar errores de validación
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Manejar otros errores
            return response()->json(['message' => 'Error interno del servidor', 'error' => $e->getMessage()], 500);
        }
    }

    public function createEntradaContenedoresS(Request $request)
    {
        try {
            // Validar la solicitud
            $validated = $request->validate([
                'fecha_i' => 'required|date', 
                'hora_i' => 'required|date_format:H:i', 
                'fecha_s' => 'required|date', 
                'hora_s' => 'required|date_format:H:i', 
                'id_cliente' => 'required|integer',
                'id_transportista' => 'required|integer',
                'placas' => 'required|string',
                'eco' => 'required|string',
                'licencia' => 'required|string',
                'operador' => 'required|string',
                'observaciones' => 'required|string',
                '_key' => 'required|string',
                'estado' => 'required|string',
                'tipo' => 'required|string'
            ]);
    
            // Guardar los datos usando el modelo EntradaContenedores
            $entrada = EntradaContenedores::create($validated);
    
            // Enviar los datos a la API externa en PHP
            $response = Http::post('http://demo11.xrom.cc/nucleo/var/receive_data_createEntrada.php', $validated);
    
            // Verificar si la API respondió exitosamente
            if ($response->successful()) {
                return response()->json(['message' => 'Entrada creada y datos enviados'], 201);
            } else {
                return response()->json(['message' => 'Entrada creada, pero error al enviar datos', 'response_body' => $response->body()], $response->status());
            }
    
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error interno del servidor', 'error' => $e->getMessage()], 500);
        }
    }

    public function createContenedoresS(Request $request)
    {
        try {
            // Validar la solicitud
            $validated = $request->validate([
                'no_contenedor' => 'required|string', 
                'sello' => 'required|string', 
                'estado' => 'required|string', 
                'pedimento' => 'required|string', 
                'tipo_contenedor' => 'required|string',
                'id_patio' => 'required|string',
                'posicion' => 'required|string',
                'fila' => 'required|string',
                'nivel' => 'required|string',
                'id_cliente' => 'required|string',
                'id_ingreso' => 'required|string',
                'f_ingreso' => 'required|string',
                'f_salida' => 'required|string'
            ]);
    
            // Guardar los datos usando el modelo EntradaContenedores
            $entrada = createContenedores::create($validated);
    
            // Enviar los datos a la API externa en PHP
            $response = Http::post('http://demo11.xrom.cc/nucleo/var/receive_data_createContenedores.php', $validated);
    
            // Verificar si la API respondió exitosamente
            if ($response->successful()) {
                return response()->json(['message' => 'Contenedor creada y datos enviados'], 201);
            } else {
                return response()->json(['message' => 'Contenedor creada, pero error al enviar datos', 'response_body' => $response->body()], $response->status());
            }
    
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error interno del servidor', 'error' => $e->getMessage()], 500);
        }
    }

}
