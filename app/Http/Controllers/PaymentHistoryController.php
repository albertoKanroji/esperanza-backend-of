<?php

namespace App\Http\Controllers;

use App\Models\PaymentHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PaymentHistoryController extends Controller
{
    // Obtener todos los pagos
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

            $payments = PaymentHistory::with('user')->get();

            return response()->json($payments, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching payment history: ' . $e->getMessage());

            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred while fetching the payment history.'
            ], 500);
        }
    }

    // Crear un nuevo registro de pago
    public function store(Request $request)
    {
        try {
            // Verificar si el usuario está autenticado
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'You must be authenticated to perform this action.'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'description' => 'required|string',
                'payment_date' => 'required|date',
                'amount' => 'required|numeric|min:0',
                'payment_method' => 'required|in:credit_card,debit_card,paypal,bank_transfer',
                'transaction_status' => 'required|in:pending,completed,failed',
                'encrypted_card_details' => 'required|string',
                'container_name' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $payment = PaymentHistory::create($request->all());

            return response()->json(['message' => 'Payment created successfully', 'data' => $payment], 201);
        } catch (\Exception $e) {
            Log::error('Error creating payment: ' . $e->getMessage());

            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred while creating the payment.'
            ], 500);
        }
    }

    // Obtener los pagos de un usuario específico
    public function show($userId)
    {
        try {
            // Verificar si el usuario está autenticado
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'You must be authenticated to view this resource.'
                ], 401);
            }

            // Buscar los pagos relacionados con el usuario especificado
            $payments = PaymentHistory::with('user')->where('user_id', $userId)->get();

            // Verificar si hay pagos para el usuario
            if ($payments->isEmpty()) {
                return response()->json(['error' => 'No payments found for this user'], 404);
            }

            return response()->json($payments, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching payments for user ' . $userId . ': ' . $e->getMessage());

            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred while fetching the user payments.'
            ], 500);
        }
    }
}
