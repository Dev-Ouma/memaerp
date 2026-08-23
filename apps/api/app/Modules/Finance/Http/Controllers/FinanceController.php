<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\Payment;
use App\Modules\Finance\Services\ClearanceService;
use App\Modules\Finance\Services\FinanceService;
use App\Modules\Iam\Models\User;
use App\Modules\Student\Models\Student;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class FinanceController extends Controller
{
    public function __construct(
        private readonly FinanceService $finance,
        private readonly ClearanceService $clearance,
    ) {}

    public function statement(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'finance.invoice.view');
        $personId = $this->resolvePersonId($user, $request->query('person_id'));

        $data = $this->finance->statement((string) $user->institution_id, $personId);

        return response()->json(['data' => $data]);
    }

    public function clearanceStatus(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'finance.invoice.view');
        $personId = $this->resolvePersonId($user, $request->query('person_id'));
        $termId = $request->query('term_id');

        return response()->json([
            'data' => $this->clearance->forPerson(
                (string) $user->institution_id,
                $personId,
                is_string($termId) ? $termId : null,
            ),
        ]);
    }

    public function recordPayment(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'finance.payment.record');

        $validated = $request->validate([
            'invoice_id' => ['required', 'uuid'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'string', 'in:MPESA,BANK_TRANSFER,CHEQUE,CARD'],
            'transaction_reference' => ['sometimes', 'nullable', 'string', 'max:100'],
        ]);

        $invoice = Invoice::query()->where('institution_id', $user->institution_id)->findOrFail($validated['invoice_id']);
        $payment = $this->finance->recordPayment(
            $invoice,
            (float) $validated['amount'],
            $validated['payment_method'],
            $validated['transaction_reference'] ?? null,
        );

        return response()->json(['data' => $payment->load('invoice')], 201);
    }

    public function mpesaStkPush(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requireAnyPermission($user, ['finance.payment.record', 'finance.payment.view']);

        $validated = $request->validate([
            'invoice_id' => ['required', 'uuid'],
            'phone_number' => ['required', 'string', 'max:32'],
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $invoice = Invoice::query()->where('institution_id', $user->institution_id)->findOrFail($validated['invoice_id']);
        if ($this->isSelfScopedStudent($user)) {
            $student = Student::query()->where('person_id', $user->person_id)->first();
            abort_unless($student !== null && $invoice->person_id === $student->person_id, 404);
        }

        return response()->json([
            'data' => $this->finance->initiateMpesaStk($invoice, $validated['phone_number'], (float) $validated['amount']),
        ]);
    }

    public function mpesaCallback(Request $request): JsonResponse
    {
        $payment = $this->finance->ingestMpesaCallback($request->all());

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted', 'payment_id' => $payment->id]);
    }

    public function receipt(Request $request, string $paymentId): Response
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'finance.payment.view');

        $payment = Payment::query()->where('institution_id', $user->institution_id)->findOrFail($paymentId);

        return $this->finance->receiptPdf($payment);
    }

    public function issueInvoice(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $this->requirePermission($user, 'finance.invoice.issue');

        $validated = $request->validate([
            'student_id' => ['required', 'uuid'],
            'term_id' => ['required', 'uuid'],
        ]);

        $student = Student::query()->where('institution_id', $user->institution_id)->findOrFail($validated['student_id']);
        $term = \App\Modules\Institution\Models\Term::query()->where('institution_id', $user->institution_id)->findOrFail($validated['term_id']);
        $invoice = $this->finance->issueTermInvoice($student, $term);

        return response()->json(['data' => $invoice->load(['term', 'feeStructure'])], 201);
    }

    private function actor(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return $user;
    }

    private function requirePermission(User $user, string $permission): void
    {
        if ($user->scopesFor($permission) === []) {
            throw new AuthorizationException;
        }
    }

    /** @param list<string> $permissions */
    private function requireAnyPermission(User $user, array $permissions): void
    {
        foreach ($permissions as $permission) {
            if ($user->scopesFor($permission) !== []) {
                return;
            }
        }

        throw new AuthorizationException;
    }

    private function resolvePersonId(User $user, mixed $requestedPersonId): string
    {
        if ($this->isSelfScopedStudent($user)) {
            abort_unless(is_string($user->person_id), 404);

            return $user->person_id;
        }

        if (is_string($requestedPersonId) && $requestedPersonId !== '') {
            return $requestedPersonId;
        }

        abort_unless(is_string($user->person_id), 422, 'person_id is required for staff callers.');

        return $user->person_id;
    }

    private function isSelfScopedStudent(User $user): bool
    {
        $scopes = $user->scopesFor('finance.invoice.view');
        if ($scopes === []) {
            return false;
        }

        if ($user->scopesFor('finance.payment.record') !== [] || $user->scopesFor('finance.invoice.issue') !== []) {
            return false;
        }

        return collect($scopes)->every(fn ($scope) => $scope->isSelf());
    }
}
