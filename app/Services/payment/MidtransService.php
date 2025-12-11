<?php

namespace App\Services\Payment;

use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    public function __construct()
    {
        $this->configure();
    }

    /**
     * Konfigurasi library Midtrans
     */
    protected function configure()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Membuat Snap Token
     */
    public function createSnapToken(array $params)
    {
        try {
            $transaction = Snap::createTransaction($params);
            
            return [
                'token' => $transaction->token,
                'redirect_url' => $transaction->redirect_url
            ];
        } catch (\Throwable $e) {
            Log::error("Midtrans Snap Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verifikasi Signature Key untuk keamanan Webhook
     */
    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $inputSignature): bool
    {
        $serverKey = config('midtrans.server_key');
        
        // Rumus SHA512: order_id + status_code + gross_amount + ServerKey
        $stringToHash = $orderId . $statusCode . $grossAmount . $serverKey;
        $mySignature = hash("sha512", $stringToHash);
        
        return $inputSignature === $mySignature;
    }
}