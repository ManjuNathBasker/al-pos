<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QZSignController extends Controller
{
    /**
     * Get the public digital certificate for QZ Tray authentication.
     */
    public function getCertificate()
    {
        $certPath = storage_path('app/private/qz/digital-certificate.txt');

        if (!file_exists($certPath)) {
            // Fallback location check
            $certPath = storage_path('app/qz/digital-certificate.txt');
        }

        if (!file_exists($certPath)) {
            Log::error('[QZ Tray] Certificate file missing at: ' . storage_path('app/private/qz/digital-certificate.txt'));
            return response()->json([
                'error' => 'Digital certificate file not found. Please place digital-certificate.txt in storage/app/private/qz/digital-certificate.txt'
            ], 404);
        }

        $certificate = file_get_contents($certPath);

        return response($certificate, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Sign the QZ Tray authentication challenge string using SHA512 and private key.
     */
    public function sign(Request $request)
    {
        $request->validate([
            'request' => 'required|string',
        ]);

        $toSign = $request->input('request');

        $keyPath = storage_path('app/private/qz/private-key.pem');

        if (!file_exists($keyPath)) {
            // Fallback location check
            $keyPath = storage_path('app/qz/private-key.pem');
        }

        if (!file_exists($keyPath)) {
            Log::error('[QZ Tray] Private key file missing at: ' . storage_path('app/private/qz/private-key.pem'));
            return response()->json([
                'error' => 'Private key file not found. Please place private-key.pem in storage/app/private/qz/private-key.pem'
            ], 404);
        }

        $privateKeyContent = file_get_contents($keyPath);
        $pkey = openssl_pkey_get_private($privateKeyContent);

        if (!$pkey) {
            Log::error('[QZ Tray] Invalid OpenSSL private key contents in private-key.pem.');
            return response()->json([
                'error' => 'Invalid private key file format.'
            ], 500);
        }

        $signature = '';
        $success = openssl_sign($toSign, $signature, $pkey, OPENSSL_ALGO_SHA512);

        if (!$success) {
            Log::error('[QZ Tray] OpenSSL failed to compute signature for request string.');
            return response()->json([
                'error' => 'Failed to generate digital signature.'
            ], 500);
        }

        return response(base64_encode($signature), 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
