<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Log;

class MidtransService
{
    public function __construct()
    {
        // Tidak perlu configure jika library tidak tersedia
    }

    /**
     * Membuat Snap Token
     */
    public function createSnapToken(array $params)
    {
        // Cek apakah library Midtrans tersedia
        if (!class_exists('\Midtrans\Config')) {
            Log::error("Midtrans library not found. Please install midtrans/midtrans-php package.");
            throw new \Exception("Midtrans library not found. Please install midtrans/midtrans-php package.");
        }

        $this->configure();
        try {
            $transaction = \Midtrans\Snap::createTransaction($params);

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
     * Ambil URL Snap untuk pembayaran
     */
    public function getSnapUrl(string $orderId, float $grossAmount, string $customerName, string $customerEmail, string $customerPhone, array $items)
    {
        // Cek apakah library Midtrans tersedia
        if (!class_exists('\Midtrans\Config')) {
            Log::error("Midtrans library not found. Please install midtrans/midtrans-php package.");
            return null; // Kembalikan null jika library tidak tersedia
        }

        $this->configure();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $customerName,
                'email' => $customerEmail,
                'phone' => $customerPhone,
            ],
            'item_details' => $items
        ];

        try {
            $snapToken = \Midtrans\Snap::createTransaction($params);
            return $snapToken->redirect_url;
        } catch (\Throwable $e) {
            Log::error("Midtrans Snap Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Konfigurasi library Midtrans
     */
    protected function configure()
    {
        if (!class_exists('\Midtrans\Config')) {
            return;
        }

        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$clientKey = config('midtrans.client_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Verifikasi Signature Key untuk keamanan Webhook
     */
    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $inputSignature): bool
    {
        $serverKey = config('midtrans.server_key');
        if (empty($serverKey)) {
            Log::error('Midtrans server key not configured.');
            return false;
        }

        // Rumus SHA512: order_id + status_code + gross_amount + ServerKey
        $stringToHash = $orderId . $statusCode . $grossAmount . $serverKey;
        $mySignature = hash("sha512", $stringToHash);

        // Use hash_equals to avoid timing attacks and trim incoming signature
        return hash_equals($mySignature, trim((string) $inputSignature));
    }

    /**
     * Cek Status Transaksi Manual ke API Midtrans
     */
    public function getTransactionStatus(string $orderId)
    {
        if (!class_exists('\Midtrans\Transaction')) {
            Log::error("Midtrans library not found. Please install midtrans/midtrans-php package.");
            return null;
        }

        try {
            return \Midtrans\Transaction::status($orderId);
        } catch (\Throwable $e) {
            Log::error("Midtrans Status Error: " . $e->getMessage());
            return null;
        }
    }
}