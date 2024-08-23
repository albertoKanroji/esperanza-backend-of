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

class UsersController extends Controller
{
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
                'message' => 'An error occurred while retrieving the user balance.'
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
        return $this->getUsersByProfile('SUPPORT');
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
        $request->validate([
            'number' => 'required|string|max:255',
            'year' => 'required|integer',
            'mes' => 'required|integer',
            'cvv' => 'required|string|max:255',
            'id' => 'required|string|max:255',
        ]);

        try {
            // Buscar el usuario por ID
            $user = User::findOrFail($userId);

            // Crear una nueva tarjeta
            $tarjeta = Tarjetas::create([
                'number' => $request->input('number'),
                'year' => $request->input('year'),
                'mes' => $request->input('mes'),
                'cvv' => $request->input('cvv'),
            ]);

            // Relacionar la tarjeta con el usuario
            $user->tarjetas()->attach($tarjeta->id);

            // Retornar una respuesta exitosa
            return response()->json([
                'status' => 'success',
                'message' => 'Tarjeta añadida exitosamente al usuario.',
                'data' => [
                    'user' => $user,
                    'tarjeta' => $tarjeta,
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
    public function getUserTarjetas(Request $request)
    {
        try {
            $clientes_id = $request->input('clientes_id');

            // Buscar el usuario por ID
            $user = User::findOrFail($clientes_id);

            // Obtener todas las tarjetas del usuario
            $tarjetas = $user->tarjetas->map(function ($tarjeta) {
                return [
                    'id' => $tarjeta->id,
                    'number' => $tarjeta->number,
                    'year' => $tarjeta->year,
                    'mes' => $tarjeta->mes,
                    'cvv' => $tarjeta->cvv,
                    // Agrega aquí cualquier otro campo que desees incluir en la respuesta
                ];
            });

            // Retornar una respuesta exitosa
            return response()->json([
                'status' => 'success',
                'data' => $tarjetas,
            ], 200);
        } catch (\Exception $e) {
            // Manejar cualquier error
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
}
