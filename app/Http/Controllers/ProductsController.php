<?php

namespace App\Http\Controllers;

use App\Models\EntradaProducto;
use App\Models\Product;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductsController extends Controller
{
    public function show(Request $request)
    {
        try {
            $id = $request->input('productId');
            $data = Product::findOrFail($id);

            // Obtener la deuda total del producto
            $totalDeuda = EntradaProducto::where('products_id', $id)->sum('total_deuda');
            $entrada = EntradaProducto::where('products_id', $id)->first();
            // Agregar la deuda total a la respuesta
            return response()->json([
                'status' => 'success',
                'data' => [
                    'product' => $data,
                    'total_deuda' => $totalDeuda,
                    'entrada' => $entrada
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }
    public function pagarDeuda(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'clientes_id' => 'required|exists:users,id',
            'productos_id' => 'required|exists:products,id',
            'monto_pago' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'data' => $validator->errors()], 400);
        }

        try {
            $producto = Product::findOrFail($request->productos_id);
            $cliente = User::findOrFail($request->clientes_id);
            // Verifica si el producto ya ha sido pagado
            if ($producto->estado_deuda === 'SI') {
                return response()->json([
                    'status' => 'error',
                    'data' => ['message' => 'La deuda del producto ya ha sido pagada.']
                ], 400);
            }

            // Verifica si hay entradas de productos para el cliente
            $entradas = EntradaProducto::where('clientes_id', $request->clientes_id)
                ->where('products_id', $request->productos_id)
                ->get();

            // Calcula la deuda total
            $totalDeuda = $entradas->sum('total_deuda');

            if ($cliente->saldo < $request->monto_pago) {
                return response()->json([
                    'status' => 'error',
                    'data' => ['message' => 'El cliente no tiene suficiente saldo.']
                ], 400);
            }

            $cliente->saldo -= $request->monto_pago;
            $cliente->save();

            // Si el monto de pago cubre la deuda, actualiza el estado del producto y la deuda
            $producto->estado_deuda = 'SI';
            $producto->save();

            // Establece la deuda total a 0 para las entradas del producto
            $entradas->each(function ($entrada) {
                $entrada->total_deuda = 0;
                $entrada->save();
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'message' => 'Deuda pagada completamente y estado del producto actualizado.',
                    'producto' => $producto,
                    'entradas' => $entradas
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }
}
