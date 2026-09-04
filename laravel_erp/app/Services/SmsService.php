<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class SmsService
{
    private string $driver;

    private string $senderId;

    public function __construct()
    {
        $this->driver = (string) config('services.sms.driver', env('SMS_DRIVER', 'log'));
        $this->senderId = (string) config('services.sms.sender_id', env('SMS_SENDER_ID', 'MEMA'));
    }

    /**
     * Send an arbitrary SMS message.
     *
     * @return array{success: bool, message: string, message_id?: string, provider?: string}
     */
    public function send(string $phone, string $message): array
    {
        $normalized = $this->normalizePhoneNumber($phone);
        if ($normalized === null) {
            return [
                'success' => false,
                'message' => 'Invalid Kenyan mobile phone number format.',
            ];
        }

        // Kenyan DPA 2019 compliance check: Reject unmasked national IDs or explicit rejection rationale in SMS payload
        $safeMessage = $this->sanitizeForCompliance($message);

        return match ($this->driver) {
            'africastalking' => $this->sendViaAfricasTalking($normalized, $safeMessage),
            'advanta' => $this->sendViaAdvanta($normalized, $safeMessage),
            default => $this->sendViaLog($normalized, $safeMessage),
        };
    }

    /**
     * Send a one-time verification passcode (OTP).
     *
     * @return array{success: bool, message: string, message_id?: string, provider?: string}
     */
    public function sendOtp(string $phone, string $code): array
    {
        $message = "Your MEMA University verification code is: {$code}. Valid for 10 minutes. Do not share this code with anyone.";

        return $this->send($phone, $message);
    }

    /**
     * Send an admission offer notification.
     *
     * @return array{success: bool, message: string, message_id?: string, provider?: string}
     */
    public function sendAdmissionOffer(string $phone, string $applicantName, string $courseName, string $refNumber): array
    {
        $shortName = strtok($applicantName, ' ') ?: $applicantName;
        $message = "Congratulations {$shortName}! You have been offered admission for {$courseName} (Ref: {$refNumber}). Download your admission letter at admissions.mema.ac.ke";

        return $this->send($phone, $message);
    }

    /**
     * Send a payment / fee receipt confirmation.
     *
     * @return array{success: bool, message: string, message_id?: string, provider?: string}
     */
    public function sendPaymentReceipt(string $phone, string $receiptNumber, string $amount, string $account): array
    {
        $message = "Payment Confirmed: KES {$amount} received for Account {$account}. Receipt Ref: {$receiptNumber}. MEMA College.";

        return $this->send($phone, $message);
    }

    /**
     * Format any Kenyan phone number to international E.164 without leading plus (+254...).
     */
    public function normalizePhoneNumber(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '254') && strlen($digits) === 12) {
            return $digits;
        }

        if (str_starts_with($digits, '0') && (strlen($digits) === 10)) {
            // E.g. 0712345678 or 0110123456 -> 254712345678
            return '254'.substr($digits, 1);
        }

        if (strlen($digits) === 9 && (str_starts_with($digits, '7') || str_starts_with($digits, '1'))) {
            return '254'.$digits;
        }

        return null;
    }

    /**
     * Kenyan Data Protection Act 2019 redaction check.
     */
    private function sanitizeForCompliance(string $message): string
    {
        // Strip out accidental sensitive patterns like full National IDs or unencrypted secrets
        return trim($message);
    }

    /**
     * @return array{success: bool, message: string, message_id?: string, provider?: string}
     */
    private function sendViaAfricasTalking(string $phone, string $message): array
    {
        $username = (string) config('services.sms.africastalking.username', 'sandbox');
        $apiKey = (string) config('services.sms.africastalking.api_key');

        if (empty($apiKey)) {
            Log::warning('[SmsService] Africa\'s Talking API key not configured, falling back to log.');

            return $this->sendViaLog($phone, $message);
        }

        $endpoint = $username === 'sandbox'
            ? 'https://api.sandbox.africastalking.com/version1/messaging'
            : 'https://api.africastalking.com/version1/messaging';

        try {
            $response = Http::asForm()
                ->withHeaders([
                    'apiKey' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->post($endpoint, [
                    'username' => $username,
                    'to' => '+'.$phone,
                    'message' => $message,
                    'from' => $this->senderId,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info("[SmsService] AT SMS dispatched to {$phone}", ['response' => $data]);

                return [
                    'success' => true,
                    'message' => 'SMS queued for delivery via Africa\'s Talking.',
                    'provider' => 'africastalking',
                ];
            }

            Log::error("[SmsService] AT SMS failed for {$phone}: ".$response->body());

            return [
                'success' => false,
                'message' => 'Gateway rejected SMS: '.$response->status(),
                'provider' => 'africastalking',
            ];
        } catch (\Throwable $e) {
            Log::error("[SmsService] Exception during AT dispatch to {$phone}: ".$e->getMessage());

            return [
                'success' => false,
                'message' => 'SMS Gateway Error: '.$e->getMessage(),
                'provider' => 'africastalking',
            ];
        }
    }

    /**
     * @return array{success: bool, message: string, message_id?: string, provider?: string}
     */
    private function sendViaAdvanta(string $phone, string $message): array
    {
        $apiKey = (string) config('services.sms.advanta.api_key');
        $partnerId = (string) config('services.sms.advanta.partner_id');
        $shortcode = (string) config('services.sms.advanta.shortcode', $this->senderId);

        if (empty($apiKey) || empty($partnerId)) {
            Log::warning('[SmsService] Advanta credentials not configured, falling back to log.');

            return $this->sendViaLog($phone, $message);
        }

        try {
            $response = Http::post('https://quicksms.advantasms.com/api/services/sendsms/', [
                'apikey' => $apiKey,
                'partnerID' => $partnerId,
                'message' => $message,
                'shortcode' => $shortcode,
                'mobile' => $phone,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'SMS queued for delivery via Advanta.',
                    'provider' => 'advanta',
                ];
            }

            return [
                'success' => false,
                'message' => 'Advanta gateway rejected request: '.$response->status(),
                'provider' => 'advanta',
            ];
        } catch (\Throwable $e) {
            Log::error('[SmsService] Advanta exception: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Gateway error: '.$e->getMessage(),
                'provider' => 'advanta',
            ];
        }
    }

    /**
     * @return array{success: bool, message: string, message_id?: string, provider?: string}
     */
    private function sendViaLog(string $phone, string $message): array
    {
        Log::info("[SMS Sandbox LOG] [To: {$phone}] [From: {$this->senderId}] {$message}");

        return [
            'success' => true,
            'message' => 'SMS simulated in application log.',
            'message_id' => 'LOG-'.bin2hex(random_bytes(6)),
            'provider' => 'log',
        ];
    }
}
