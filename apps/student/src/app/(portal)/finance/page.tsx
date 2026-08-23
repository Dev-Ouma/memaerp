'use client';

import React, { useState } from 'react';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  Button,
  Badge,
  Input,
  Alert,
  AlertTitle,
  AlertDescription,
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
  formatCurrency,
  formatDate,
} from '@mema/ui';
import {
  CreditCard,
  Phone,
  Receipt,
  ShieldCheck,
  Smartphone,
} from 'lucide-react';
import { ApiError, api } from '@mema/api-client';

export default function StudentFinancePage() {
  const queryClient = useQueryClient();
  const statement = useQuery({
    queryKey: ['finance', 'statement'],
    queryFn: () => api.getFinanceStatement(),
  });

  const clearance = (statement.data?.clearance ?? {}) as Record<string, number | boolean>;
  const invoices = statement.data?.invoices ?? [];
  const payments = statement.data?.payments ?? [];
  const activeInvoice = invoices.find((inv) => inv.status !== 'PAID') ?? invoices[0];

  const [phoneNumber, setPhoneNumber] = useState('0712345678');
  const [payAmount, setPayAmount] = useState('');
  const [isProcessing, setIsProcessing] = useState(false);
  const [paymentSuccess, setPaymentSuccess] = useState(false);
  const [error, setError] = useState<string | null>(null);

  React.useEffect(() => {
    if (activeInvoice) {
      const bal = activeInvoice.balance_amount ?? (Number(activeInvoice.total_amount) - Number(activeInvoice.paid_amount || 0));
      setPayAmount(String(Math.min(Number(bal), 50000)));
    }
  }, [activeInvoice?.id, activeInvoice?.balance_amount, activeInvoice?.total_amount, activeInvoice?.paid_amount]);

  const handleMpesaPay = async (event: React.FormEvent) => {
    event.preventDefault();
    if (!activeInvoice) return;
    setIsProcessing(true);
    setError(null);
    setPaymentSuccess(false);
    try {
      await api.initiateMpesaPayment({
        invoice_id: activeInvoice.id,
        phone_number: phoneNumber,
        amount: Number(payAmount),
      });
      setPaymentSuccess(true);
      await queryClient.invalidateQueries({ queryKey: ['finance', 'statement'] });
    } catch (err) {
      const message =
        err instanceof ApiError ? err.message : 'Unable to complete payment right now.';
      setError(message);
    } finally {
      setIsProcessing(false);
    }
  };

  return (
    <div className="space-y-8">
      <div>
        <h2 className="text-2xl font-bold text-slate-900 font-heading">Finance & Fee Statement</h2>
        <p className="text-sm text-slate-500 mt-1">
          Real-time ledger · invoices, receipts, and M-Pesa instant reconciliation
        </p>
      </div>

      {statement.isError && (
        <Alert variant="destructive">
          <AlertTitle>Unable to load statement</AlertTitle>
          <AlertDescription>
            {statement.error instanceof Error ? statement.error.message : 'Unexpected error'}
          </AlertDescription>
        </Alert>
      )}

      {error && (
        <Alert variant="destructive">
          <AlertTitle>Payment failed</AlertTitle>
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      )}

      {paymentSuccess && (
        <Alert variant="success">
          <AlertTitle>M-Pesa STK push sent</AlertTitle>
          <AlertDescription>Check your phone to complete the payment.</AlertDescription>
        </Alert>
      )}

      <div className="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
          <div className="flex items-center justify-between text-slate-500 text-sm">
            <span>Outstanding Balance</span>
            <CreditCard className="h-5 w-5 text-rose-500" />
          </div>
          <p className="mt-3 text-2xl font-bold text-slate-900">
            {formatCurrency(Number(clearance.balance ?? 0))}
          </p>
          <p className="text-xs text-slate-500 mt-1">Due across all open terms</p>
        </div>

        <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
          <div className="flex items-center justify-between text-slate-500 text-sm">
            <span>Total Invoiced</span>
            <Receipt className="h-5 w-5 text-mema-teal-700" />
          </div>
          <p className="mt-3 text-2xl font-bold text-slate-900">
            {formatCurrency(Number(clearance.total_invoiced ?? 0))}
          </p>
          <p className="text-xs text-slate-500 mt-1">Cumulative programme charges</p>
        </div>

        <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
          <div className="flex items-center justify-between text-slate-500 text-sm">
            <span>Exam Clearance</span>
            <ShieldCheck className="h-5 w-5 text-emerald-600" />
          </div>
          <div className="mt-3">
            <Badge variant={clearance.is_cleared ? 'success' : 'warning'}>
              {clearance.is_cleared ? 'Cleared for exams' : 'Balance outstanding'}
            </Badge>
          </div>
          <p className="text-xs text-slate-500 mt-2">
            Threshold: {Number(clearance.clearance_threshold_percent ?? 100)}% paid
          </p>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2 space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Invoices</CardTitle>
              <CardDescription>Term billing schedule and payment allocation</CardDescription>
            </CardHeader>
            <CardContent>
              {statement.isLoading ? (
                <p className="text-sm text-slate-500 py-8 text-center">Loading invoices…</p>
              ) : invoices.length === 0 ? (
                <p className="text-sm text-slate-500 py-4 text-center">No invoices issued.</p>
              ) : (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Invoice #</TableHead>
                      <TableHead>Due</TableHead>
                      <TableHead>Amount</TableHead>
                      <TableHead>Balance</TableHead>
                      <TableHead>Status</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {invoices.map((inv) => {
                      const bal = inv.balance_amount ?? (Number(inv.total_amount) - Number(inv.paid_amount || 0));
                      return (
                        <TableRow key={inv.id}>
                          <TableCell className="font-mono text-xs">{inv.invoice_number ?? inv.id.slice(0, 8)}</TableCell>
                          <TableCell>{inv.due_date ? formatDate(inv.due_date) : '—'}</TableCell>
                          <TableCell>{formatCurrency(Number(inv.total_amount ?? 0))}</TableCell>
                          <TableCell>{formatCurrency(Number(bal ?? 0))}</TableCell>
                          <TableCell><Badge variant={inv.status === 'PAID' ? 'success' : 'warning'}>{inv.status}</Badge></TableCell>
                        </TableRow>
                      );
                    })}
                  </TableBody>
                </Table>
              )}
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Payment History</CardTitle>
              <CardDescription>Recorded M-Pesa and bank receipts</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              {payments.length === 0 ? (
                <p className="text-sm text-slate-500 py-4 text-center">No payments recorded yet.</p>
              ) : (
                payments.map((pay) => (
                  <div key={pay.id} className="flex items-center justify-between rounded-xl border border-slate-200 p-4">
                    <div>
                      <p className="font-semibold text-slate-900">{formatCurrency(Number(pay.amount ?? 0))}</p>
                      <p className="text-xs text-slate-500">{pay.reference_number ?? pay.id.slice(0, 8)}</p>
                    </div>
                    <Badge variant="success">{pay.status ?? 'COMPLETED'}</Badge>
                  </div>
                ))
              )}
            </CardContent>
          </Card>
        </div>

        <Card className="sticky top-24 h-fit">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Smartphone className="h-5 w-5 text-emerald-600" /> M-Pesa Payment
            </CardTitle>
            <CardDescription>Pay fees via Safaricom STK push</CardDescription>
          </CardHeader>
          <CardContent>
            {!activeInvoice ? (
              <p className="text-sm text-slate-500">No payable invoice available.</p>
            ) : (
              <form onSubmit={handleMpesaPay} className="space-y-4">
                <div>
                  <label className="text-xs font-bold text-slate-700 uppercase tracking-wider">Phone number</label>
                  <Input
                    value={phoneNumber}
                    onChange={(e) => setPhoneNumber(e.target.value)}
                    leftIcon={<Phone className="h-4 w-4" />}
                    placeholder="07XXXXXXXX"
                    required
                  />
                </div>
                <div>
                  <label className="text-xs font-bold text-slate-700 uppercase tracking-wider">Amount (KES)</label>
                  <Input
                    type="number"
                    min={1}
                    value={payAmount}
                    onChange={(e) => setPayAmount(e.target.value)}
                    leftIcon={<CreditCard className="h-4 w-4" />}
                    required
                  />
                </div>
                <Button type="submit" className="w-full gap-2" isLoading={isProcessing}>
                  <Receipt className="h-4 w-4" /> Send STK Push
                </Button>
                <p className="text-3xs text-slate-400 flex items-center gap-1">
                  <ShieldCheck className="h-3.5 w-3.5" /> Invoice {activeInvoice.invoice_number ?? activeInvoice.id.slice(0, 8)}
                </p>
              </form>
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
