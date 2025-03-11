<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\StorePaymentRequest;

class PaymentController extends Controller
{
    public function storePayment(StorePaymentRequest $request)
    {
        try {

            $transaction = Transaction::create([
                'payment_gateway' => $request['pay-method'],
                'amount' => $request->amount,
                'currency' => $request->currency,
                'status' => 'pending',
                'request_data' => json_encode($request->all()),
            ]);

            if ($request['pay-method'] == 'easymoney') {
                $paymentStatus = $this->processEasyMoneyPayment($transaction);
            } else if ($request['pay-method'] == 'superwalletz' ) {
                $paymentStatus = $this->processSuperWalletzPayment($transaction);
            }

            if ($paymentStatus === 200) {
                return redirect()->route('home')->with('success', "Payment N° {$transaction->id} processed successfully.");
            } else {
                $transaction->update(['status' => 'failed']);
                return redirect()->route('home')->with('error', "Payment failed. Please try again.");
            }

        } catch (\Throwable $th) {          
            $transaction->update([
                'status' => 'failed',
                'response_data' => json_encode(["error" => $th->getMessage()]),
            ]);

            return redirect()->route('home')->with('error', 'An error occurred while processing the payment');
        }
    }

    private function processEasyMoneyPayment(Transaction $transaction)
    {
        $data = [
            'amount' => floatval($transaction->amount),
            'currency' => $transaction->currency,
        ];

        try {
            $response = Http::post('http://localhost:3000/process', $data)->throw();
            $transaction->update([
                'status' => 'success',
                'response_data' => $response->json()
            ]);

            return $response->status();

        } catch (\Exception $e) {

            $transaction->update([ 'response_data' => json_encode(["error" => $e->getMessage()]) ]);           
            return 500;
        }
    }

    private function processSuperWalletzPayment(Transaction $transaction)
    {
        $data = [
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'callback_url' => route('webhook.superwalletz'),
        ];

        try {
            $response = Http::post('http://localhost:3003/pay', $data)->throw();

            $transaction->update([
                'status' => 'processing',
                'transaction_id' => $response->json('transaction_id'),
                'response_data' => $response->body(),
            ]);

            return $response->status();
        } catch (\Exception $e) {

            $transaction->update([ 'response_data' => json_encode(["error" => $e->getMessage()]) ]);
            return 500;
        }
    }


    public function storeSuperWalletzWebhook(Request $request)
    {
        $transaction = Transaction::where('transaction_id', $request->transaction_id)->first();

        if ($transaction) {
            $transaction->update([
                'status' => 'success',
                'response_data' => json_encode($request->all()),
            ]);
        }

        return response()->json(['message' => 'Webhook processed']);
    }
}