<?php

namespace App\Http\Controllers;

use App\Models\PaymentHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PaymentHistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Middleware de autenticación
    }

    // Obtener todos los pagos
    public function index()
    {
        try {
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

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'users_id' => 'required|exists:users,id',
                'description' => 'required|string|max:512',
                'payment_date' => 'required|date_format:Y-m-d H:i:s', // Validación para formato correcto
                'amount' => 'required|numeric|min:0',
                'payment_method' => 'required|in:credit_card,debit_card,paypal,bank_transfer',
                'transaction_status' => 'required|in:pending,completed,failed',
                'encrypted_card_details' => 'required|string|max:255',
                'container_name' => 'required|string|max:100',
            ]);
    
            if ($validator->fails()) {
                Log::error('Validation error: ', $validator->errors()->toArray());
                return response()->json(['error' => $validator->errors()], 422);
            }
    
            $payment = PaymentHistory::create($request->only([
                'users_id',
                'description',
                'payment_date',
                'amount',
                'payment_method',
                'transaction_status',
                'encrypted_card_details',
                'container_name'
            ]));
    
            return response()->json(['message' => 'Payment created successfully', 'data' => $payment], 201);
        } catch (\Exception $e) {
            Log::error('Error creating payment: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'input_data' => $request->all()
            ]);
    
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
            $payments = PaymentHistory::with('user')->where('users_id', $userId)->get();

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
