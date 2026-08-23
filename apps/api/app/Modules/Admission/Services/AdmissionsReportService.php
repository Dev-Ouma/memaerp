<?php

declare(strict_types=1);

namespace App\Modules\Admission\Services;

use App\Modules\Admission\Models\Application;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

final class AdmissionsReportService
{
    /** @param Collection<int, Application> $applications */
    public function intakeRoll(Collection $applications, string $format): Response
    {
        if ($format === 'csv') {
            return response()->streamDownload(function () use ($applications): void {
                $output = fopen('php://output', 'wb');
                if ($output === false) {
                    return;
                }
                fputcsv($output, [
                    'application_number', 'applicant', 'email', 'programme', 'campus', 'intake',
                    'mean_grade', 'score', 'fee_paid', 'status', 'offer_ref',
                ]);
                foreach ($applications as $application) {
                    fputcsv($output, [
                        $application->application_number,
                        $application->person?->full_name,
                        $application->person?->primary_email,
                        $application->programme?->code,
                        $application->campus?->code,
                        $application->intake?->code,
                        $application->mean_grade,
                        $application->qualification_score,
                        $application->is_fee_paid ? 'yes' : 'no',
                        $application->status,
                        $application->offer_letter_ref,
                    ]);
                }
                fclose($output);
            }, 'admissions-intake-roll.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return Pdf::loadView('reports.admissions-intake-roll', ['applications' => $applications])
            ->setPaper('a4', 'landscape')
            ->download('admissions-intake-roll.pdf');
    }

    public function offerLetter(Application $application): Response
    {
        $safe = str_replace(['/', '\\', ' '], '-', (string) $application->application_number);

        return Pdf::loadView('reports.admission-offer-letter', ['application' => $application])
            ->setPaper('a4')
            ->download("admission-offer-{$safe}.pdf");
    }

    /** @param Collection<int, \App\Modules\Admission\Models\ApplicationPayment> $payments */
    public function feeRevenue(Collection $payments, string $format): Response
    {
        if ($format === 'csv') {
            return response()->streamDownload(function () use ($payments): void {
                $output = fopen('php://output', 'wb');
                if ($output === false) {
                    return;
                }
                fputcsv($output, ['receipt_number', 'application_number', 'channel', 'amount', 'currency', 'paid_at', 'transaction_reference']);
                foreach ($payments as $payment) {
                    fputcsv($output, [
                        $payment->receipt_number,
                        $payment->application?->application_number,
                        $payment->channel,
                        $payment->amount,
                        $payment->currency,
                        optional($payment->paid_at)?->toIso8601String(),
                        $payment->transaction_reference,
                    ]);
                }
                fclose($output);
            }, 'application-fee-revenue.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return Pdf::loadView('reports.application-fee-revenue', ['payments' => $payments])
            ->setPaper('a4')
            ->download('application-fee-revenue.pdf');
    }
}
