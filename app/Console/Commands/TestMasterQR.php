<?php

namespace App\Console\Commands;

use App\Services\MasterQRService;
use Illuminate\Console\Command;

class TestMasterQR extends Command
{
    protected $signature = 'test:masterqr';
    protected $description = 'Test MasterQR API integration';

    public function handle(MasterQRService $masterQRService)
    {
        $this->info('🔐 Testing MasterQR Integration...');
        $this->newLine();

        // 1. Test Login
        $this->info('1️⃣  Testing Login...');
        $token = $masterQRService->getAuthToken();

        if ($token) {
            $this->info("✅ Login successful!");
            $this->line("   Token: " . substr($token, 0, 50) . "...");
        } else {
            $this->error("❌ Login failed!");
            return 1;
        }

        $this->newLine();

        // 2. Test List Services
        $this->info('2️⃣  Testing List Enabled Services...');
        $services = $masterQRService->listEnabledServices();

        if ($services) {
            $this->info("✅ Services retrieved successfully!");
            $this->line(json_encode($services, JSON_PRETTY_PRINT));
        } else {
            $this->warn("⚠️  Could not retrieve services");
        }

        $this->newLine();

        // 3. Test Generate QR
        $this->info('3️⃣  Testing Generate QR...');

        $testPaymentData = [
            'client_name' => 'Jhon Doe',
            'document_type' => 1, // CI
            'document_id' => '123456',
            'phone_number' => '75540850',
            'email' => 'test@example.com',
            'payment_number' => 'TEST-' . time(),
            'amount' => 0.1,
            'callback_url' => 'https://webhook.site/unique-callback-url', // Use a public URL for testing
            'order_detail' => [
                [
                    'serial' => 1,
                    'product' => 'Test Product',
                    'quantity' => 1,
                    'price' => 0.1,
                    'discount' => 0,
                    'total' => 0.1,
                ]
            ],
        ];

        $this->line("Payment Data:");
        $this->line(json_encode($testPaymentData, JSON_PRETTY_PRINT));
        $this->newLine();

        $qrResponse = $masterQRService->generateQR($testPaymentData);

        if ($qrResponse) {
            $this->info("✅ QR Generated successfully!");
            $this->newLine();
            $this->line("Response:");
            $this->line(json_encode($qrResponse, JSON_PRETTY_PRINT));

            if (isset($qrResponse['qrImage'])) {
                $this->newLine();
                $this->info("📱 QR Image URL: " . $qrResponse['qrImage']);
            }

            if (isset($qrResponse['transactionId'])) {
                $this->info("🔑 Transaction ID: " . $qrResponse['transactionId']);
            }
        } else {
            $this->error("❌ QR Generation failed!");
            $this->warn("Check the logs for more details");
            return 1;
        }

        $this->newLine();
        $this->info('✨ All tests completed!');

        return 0;
    }
}
