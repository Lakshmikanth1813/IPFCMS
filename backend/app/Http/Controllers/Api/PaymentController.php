<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IpApplication;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments.
     */
    public function index()
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return response()->json(Payment::with(['user', 'ipApplication'])->latest()->paginate(10));
        }
        return response()->json(Payment::where('user_id', $user->id)->with('ipApplication')->latest()->paginate(10));
    }

    /**
     * Initiate a payment (Simulated).
     */
    public function store(Request $request)
    {
        $request->validate([
            'ip_application_id' => 'required|exists:ip_applications,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
        ]);

        $application = IpApplication::findOrFail($request->ip_application_id);

        // Simulated Transaction
        $payment = Payment::create([
            'user_id' => Auth::id(),
            'ip_application_id' => $application->id,
            'amount' => $request->amount,
            'currency' => 'INR',
            'transaction_id' => 'TXN-' . strtoupper(Str::random(12)),
            'payment_method' => $request->payment_method,
            'status' => 'completed', // In real world, this starts as 'pending'
            'payment_details' => [
                'simulated' => true,
                'gateway' => 'internal'
            ]
        ]);

        // Update application payment status
        $application->update([
            'payment_status' => 'paid',
            'payment_amount' => $request->amount
        ]);

        return response()->json($payment, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $payment = Payment::with(['user', 'ipApplication'])->findOrFail($id);
        
        if (Auth::user()->role === 'client' && $payment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($payment);
    }
}
