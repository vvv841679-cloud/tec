<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MasterQRService
{
    private string $baseUrl;
    private string $tokenService;
    private string $tokenSecret;
    private string $clientCode;
    private ?string $callbackUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.masterqr.base_url');
        $this->tokenService = config('services.masterqr.token_service');
        $this->tokenSecret = config('services.masterqr.token_secret');
        $this->clientCode = config('services.masterqr.client_code');
        $this->callbackUrl = config('services.masterqr.callback_url');
    }

    /**
     * Obtiene el token de autenticación de MasterQR
     * El token se cachea por 3 horas (expira en 3h 11min según la API)
     */
    public function getAuthToken(): ?string
    {
        return Cache::remember('masterqr_auth_token', 60 * 180, function () {
            try {
                $response = Http::withHeaders([
                    'tcTokenService' => $this->tokenService,
                    'tcTokenSecret' => $this->tokenSecret,
                ])->post("{$this->baseUrl}/login");

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['values']['accessToken'] ?? $data['token'] ?? null;
                }

                Log::error('MasterQR Login failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return null;
            } catch (\Exception $e) {
                Log::error('MasterQR Login exception', ['error' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * Lista los servicios habilitados en MasterQR
     */
    public function listEnabledServices(): ?array
    {
        $token = $this->getAuthToken();

        if (!$token) {
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->post("{$this->baseUrl}/list-enabled-services");

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('MasterQR List Services exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Genera un código QR para pago
     *
     * @param array $paymentData Datos del pago
     * @return array|null
     */
    public function generateQR(array $paymentData): ?array
    {
        $token = $this->getAuthToken();

        if (!$token) {
            Log::error('Cannot generate QR: No auth token available');
            return null;
        }

        // Usar la callback URL configurada o la proporcionada en los datos de pago
        $callbackUrl = $paymentData['callback_url'] ?? $this->callbackUrl;

        // Preparar datos para la API de MasterQR
        $requestData = [
            'paymentMethod' => 4, // QR
            'clientName' => $paymentData['client_name'],
            'documentType' => $paymentData['document_type'] ?? 1, // 1 = CI
            'documentId' => $paymentData['document_id'],
            'phoneNumber' => $paymentData['phone_number'],
            'email' => $paymentData['email'],
            'paymentNumber' => $paymentData['payment_number'], // Único por transacción
            'amount' => $paymentData['amount'],
            'currency' => 2, // 2 = BOB (Bolivianos)
            'clientCode' => $this->clientCode,
            'callbackUrl' => $callbackUrl,
            'orderDetail' => $paymentData['order_detail'],
        ];

        try {
            $response = Http::withToken($token)
                ->post("{$this->baseUrl}/generate-qr", $requestData);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('QR Generated successfully', [
                    'payment_number' => $paymentData['payment_number'],
                    'amount' => $paymentData['amount']
                ]);

                return $data;
            }

            Log::error('MasterQR Generate QR failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'request_data' => $requestData
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('MasterQR Generate QR exception', [
                'error' => $e->getMessage(),
                'payment_number' => $paymentData['payment_number']
            ]);
            return null;
        }
    }

    /**
     * Consulta el estado de una transacción
     *
     * @param string $transactionId ID de la transacción en MasterQR
     * @return array|null
     */
    public function queryTransaction(string $transactionId): ?array
    {
        $token = $this->getAuthToken();

        if (!$token) {
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->get("{$this->baseUrl}/query-transaction", [
                    'transactionId' => $transactionId
                ]);

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('MasterQR Query Transaction exception', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId
            ]);
            return null;
        }
    }

    /**
     * Valida la firma del webhook (callback) de MasterQR
     * Implementar según la documentación de MasterQR para validar webhooks
     */
    public function validateWebhookSignature(array $payload, string $signature): bool
    {
        // TODO: Implementar validación de firma según documentación de MasterQR
        // Por ahora retorna true, pero DEBE implementarse para seguridad
        return true;
    }
}
