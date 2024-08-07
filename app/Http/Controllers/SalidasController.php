<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EntradaProducto;
use App\Models\Product;
use App\Models\Entrada;
use App\Models\Salida;
use App\Models\SalidaRecogerProducto;
use Illuminate\Support\Facades\Validator;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class SalidasController extends Controller
{
    public function generateQrForSalida(Request $request)
    {
        try {
            $productos_id = $request->input('productId');
            // Encuentra el producto
            $producto = Product::findOrFail($productos_id);

            // Verifica si el producto ha sido escaneado en la salida
            $existingSalida = Salida::where('products_id', $productos_id)
                ->first();

            if ($existingSalida) {
                return response()->json([
                    'status' => 'error',
                    'data' => ['message' => 'El producto ya ha sido registrado como salido.']
                ], 400);
            }

            // Obtiene las entradas del producto para calcular la deuda
            $entradas = EntradaProducto::where('products_id', $productos_id)->get();
            $totalDeuda = $entradas->sum('total_deuda');

            if ($totalDeuda > 0) {
                return response()->json([
                    'status' => 'error',
                    'data' => [
                        'message' => 'El producto tiene deuda pendiente.',
                        'total_deuda' => $totalDeuda,
                        'estado' => 'salida'
                    ]
                ], 400);
            }

            // Encuentra la entrada más reciente para obtener los datos necesarios
            $entradaProducto = EntradaProducto::where('products_id', $productos_id)
                ->with(['cliente', 'trabajador'])
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$entradaProducto || !$entradaProducto->cliente || !$entradaProducto->trabajador) {
                return response()->json([
                    'status' => 'error',
                    'data' => 'No se encontró la entrada del producto o faltan datos necesarios.',
                    'entradaProducto' => $entradaProducto,
                    'trabajador' => $entradaProducto->trabajador,
                    'cliente' => $entradaProducto->cliente
                ], 400);
            }

            $cliente = $entradaProducto->cliente;
            $trabajador = $entradaProducto->trabajador;
            $camion = $producto->camiones_id; // Asumiendo que `camiones_id` es una columna en la tabla `productos`

            // Construye los datos en formato JSON
            $data = [
                'trabajadores_id' => $trabajador->id,
                'productos_id' => $producto->id,
                'clientes_id' => $cliente->id,
                'camiones_id' => $camion,
                'estado' => 'salida'
            ];

            // Convierte los datos a JSON
            $jsonData = json_encode($data);

            // Genera el código QR
            $qrCode = new QrCode($jsonData);
            $writer = new PngWriter();
            $qrCodeImage = $writer->write($qrCode);

            // Establece la respuesta con el código QR en formato PNG
            return response($qrCodeImage->getString(), 200)
                ->header('Content-Type', 'image/png');
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
