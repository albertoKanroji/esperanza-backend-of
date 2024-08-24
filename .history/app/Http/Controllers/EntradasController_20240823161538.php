<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EntradaProducto;
use App\Models\Product;
use App\Models\Entrada;
use App\Models\SalidaRecogerProducto;
use Illuminate\Support\Facades\Validator;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Stripe;
use Stripe\Stripe as StripeStripe;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
class EntradasController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'clientes_id' => 'required|exists:clientes,id',
            'trabajadores_id' => 'required|exists:trabajadores,id',
            'productos_id' => 'required|exists:productos,id',
            'camiones_id' => 'required|exists:camiones,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'data' => $validator->errors()], 400);
        }

        $producto = Product::findOrFail($request->productos_id);

        // Verifica si el producto ya ha sido escaneado
        if ($producto->estado_escaneo === 'SI') {
            return response()->json([
                'status' => 'error',
                'data' => [
                    'message' => 'El producto ya ha sido escaneado.'
                ]
            ], 400);
        }

        try {
            // Verifica si el producto tiene deuda pendiente
            if ($producto->estado_deuda === 'NO') {
                $entradas = EntradaProducto::where('clientes_id', $request->clientes_id)
                    ->where('productos_id', $request->productos_id)
                    ->get();

                $totalDeuda = $entradas->sum('total_deuda');

                if ($totalDeuda > 0) {
                    return response()->json([
                        'status' => 'error',
                        'data' => [
                            'message' => 'El producto tiene deuda.',
                            'total_deuda' => $totalDeuda
                        ]
                    ], 400);
                }
            }

            // Marca el producto como escaneado
            $producto->estado_escaneo = 'SI';
            $producto->save();

            // Crea la nueva entrada
            $entrada = Entrada::create($request->all());

            return response()->json(['status' => 'success', 'data' => $entrada], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }
    public function generateQrCode(Request $request)
    {
        try {
            $productos_id = $request->input('productId');
            // Encuentra la entrada del producto con sus relaciones
            $entradaProducto = EntradaProducto::where('products_id', $productos_id)
                ->with(['user',  'product'])
                ->first();

            // Verifica que la entrada exista y que las relaciones existan
            if (!$entradaProducto) {
                return response()->json([
                    'status' => 'error',
                    'data' => 'No se encontró la entrada del producto.',
                    'sql' => $entradaProducto
                ], 404);
            }

            $cliente = $entradaProducto->user;
            // $trabajador = $entradaProducto->trabajador;
            $producto = $entradaProducto->product;

            if (!$cliente || !$producto) {
                return response()->json([
                    'status' => 'error',
                    'data' => 'Faltan datos necesarios para generar el código QR.',
                    'cliente' => $cliente,
                    'producto' => $producto
                ], 400);
            }

            // Verifica si el producto tiene deuda pendiente
            $totalDeuda = Entrada::where('products_id', $productos_id)
                ->where('clientes_id', $cliente->id)
                ->sum('total_deuda');

            if ($totalDeuda > 0) {
                return response()->json([
                    'status' => 'error',
                    'data' => [
                        'message' => 'El producto tiene deuda pendiente.',
                        'total_deuda' => $totalDeuda,

                    ]
                ], 400);
            }

            // Construye los datos en formato JSON
            $data = [
                'user_id' => $cliente->id,
                // 'trabajadores_id' => $trabajador->id,
                'productos_id' => $producto->id,
                'estado' => 'entrada'
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
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }

    public function createCharge(Request $request)
    {
        try {
            $stripe = new StripeClient(env('STRIPE_SECRET'));

            // Primero crea un cliente
            $customer = $stripe->customers->create([
                'description' => 'Customer for ' . $request->description,
                'email' => 'customer@example.com', // O el correo electrónico proporcionado por el usuario
            ]);

            // Luego asocia la tarjeta al cliente
            $stripe->customers->createSource($customer->id, [
                'source' => $request->source, // Token recibido del frontend
            ]);

            // Finalmente, crea el cargo usando el cliente y la fuente asociada
            $response = $stripe->charges->create([
                'amount' => $request->amount,
                'currency' => 'mxn',
                'customer' => $customer->id,
                'description' => $request->description,
            ]);

            return response()->json([$response->status], 201);
        } catch (ApiErrorException $e) {
            return response()->json([
                'response' => 'error',
                'message' => $e->getError()->message,
                'type' => $e->getError()->type,
                'code' => $e->getError()->code,
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'response' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
