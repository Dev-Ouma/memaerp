<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\SmsService;
use Tests\TestCase;

final class SmsServiceTest extends TestCase
{
    private SmsService $sms;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sms = new SmsService;
    }

    public function test_normalizes_various_kenyan_phone_formats(): void
    {
        // 07... 10 digits
        $this->assertSame('254712345678', $this->sms->normalizePhoneNumber('0712345678'));

        // 01... 10 digits (Safaricom / Airtel newer series)
        $this->assertSame('254110123456', $this->sms->normalizePhoneNumber('0110123456'));

        // +254... international with plus and spaces
        $this->assertSame('254722000111', $this->sms->normalizePhoneNumber('+254 722 000 111'));

        // 254... 12 digits raw
        $this->assertSame('254799887766', $this->sms->normalizePhoneNumber('254799887766'));

        // 9 digits without leading 0
        $this->assertSame('254712345678', $this->sms->normalizePhoneNumber('712345678'));

        // Invalid formats
        $this->assertNull($this->sms->normalizePhoneNumber('12345'));
        $this->assertNull($this->sms->normalizePhoneNumber('abcd'));
    }

    public function test_dispatches_otp_sms_via_log_driver(): void
    {
        $result = $this->sms->sendOtp('0712345678', '948201');

        $this->assertTrue($result['success']);
        $this->assertSame('log', $result['provider']);
        $this->assertStringStartsWith('LOG-', $result['message_id'] ?? '');
    }

    public function test_dispatches_admission_offer_sms(): void
    {
        $result = $this->sms->sendAdmissionOffer(
            '0722112233',
            'Jane Wanjiku Mwangi',
            'Diploma in Information Communication Technology',
            'MEMA/2026/ADM/042'
        );

        $this->assertTrue($result['success']);
        $this->assertSame('log', $result['provider']);
    }

    public function test_dispatches_payment_receipt_sms(): void
    {
        $result = $this->sms->sendPaymentReceipt(
            '0700112233',
            'RCT-2026-9901',
            '25,000.00',
            'ACC-EQUITY-001'
        );

        $this->assertTrue($result['success']);
        $this->assertSame('log', $result['provider']);
    }
}
