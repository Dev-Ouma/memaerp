'use client';

import React, { useState } from 'react';
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
  CheckCircle2,
  Download,
  Receipt,
  Building,
  ShieldCheck,
  Smartphone,
} from 'lucide-react';
import { mockInvoices, mockPayments } from '@mema/api-client';

export default function StudentFinancePage() {
  const [phoneNumber, setPhoneNumber] = useState('0712345678');
  const [payAmount, setPayAmount] = useState('35000');
  const [isProcessing, setIsProcessing] = useState(false);
  const [paymentSuccess, setPaymentSuccess] = useState(false);

  const activeInvoice = mockInvoices[0];

  const handleMpesaPay = (e: React.FormEvent) => {
    e.preventDefault();
    setIsProcessing(true);
    setTimeout(() => {
      setIsProcessing(false);
      setPaymentSuccess(true);
    }, 2000);
  };

  return (
    <div className="space-y-8">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 font-heading">
            Student Finance & Fee Statement
          </h2>
          <p className="text-sm text-slate-500 mt-1">
            Real-time ledger, invoice breakdowns and verified payments
          </p>
        </div>
        <Button variant="outline" className="gap-2 self-start sm:self-auto">
          <Download className="h-4 w-4" /> Download Official Statement
        </Button>
      </div>

      {paymentSuccess && (
        <Alert variant="success" className="border-emerald-300 bg-emerald-50">
          <AlertTitle className="text-emerald-900 font-bold flex items-center gap-2">
            <CheckCircle2 className="h-5 w-5 text-emerald-600" />
            Payment Successful & Reconciled!
          </AlertTitle>
          <AlertDescription className="text-emerald-800 mt-1">
            STK Push completed. Transaction Reference: <strong>RHK93182K2</strong>. Your student ledger has been updated and fee clearance status adjusted.
          </AlertDescription>
        </Alert>
      )}

      {/* Balance Summary Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <Card className="p-6 bg-white border-l-4 border-l-slate-800">
          <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">
            Total Billed (To Date)
          </p>
          <h3 className="text-2xl sm:text-3xl font-bold text-slate-900 mt-2 font-heading">
            {formatCurrency(170000)}
          </h3>
          <p className="text-xs text-slate-400 mt-1">2 Invoiced Terms</p>
        </Card>

        <Card className="p-6 bg-white border-l-4 border-l-mema-green-600">
          <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">
            Total Paid
          </p>
          <h3 className="text-2xl sm:text-3xl font-bold text-emerald-600 mt-2 font-heading">
            {formatCurrency(135000)}
          </h3>
          <p className="text-xs text-emerald-600 font-medium mt-1">79.4% Settled</p>
        </Card>

        <Card className="p-6 bg-white border-l-4 border-l-rose-600">
          <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">
            Outstanding Balance
          </p>
          <h3 className="text-2xl sm:text-3xl font-bold text-rose-600 mt-2 font-heading">
            {formatCurrency(activeInvoice?.balance_amount || 0)}
          </h3>
          <p className="text-xs text-rose-500 font-medium mt-1">Due by Sep 30, 2026</p>
        </Card>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Left Column: Invoices & Receipts */}
        <div className="lg:col-span-2 space-y-6">
          {/* Active Invoices Table */}
          <Card>
            <CardHeader>
              <CardTitle>Invoices & Term Charges</CardTitle>
              <CardDescription>
                Detailed itemization of fees, tuition, and university levies
              </CardDescription>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Invoice #</TableHead>
                    <TableHead>Term / Description</TableHead>
                    <TableHead className="text-right">Billed</TableHead>
                    <TableHead className="text-right">Paid</TableHead>
                    <TableHead className="text-right">Balance</TableHead>
                    <TableHead className="text-center">Status</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {mockInvoices.map((inv) => (
                    <TableRow key={inv.id}>
                      <TableCell className="font-mono font-semibold text-mema-teal-900">
                        {inv.invoice_number}
                      </TableCell>
                      <TableCell>
                        <div className="font-medium text-slate-800">
                          {inv.term?.name || 'Tuition & Activity Fees'}
                        </div>
                        <div className="text-xs text-slate-400">
                          Due: {formatDate(inv.due_date)}
                        </div>
                      </TableCell>
                      <TableCell className="text-right font-mono">
                        {formatCurrency(inv.total_amount)}
                      </TableCell>
                      <TableCell className="text-right font-mono text-emerald-600 font-medium">
                        {formatCurrency(inv.paid_amount)}
                      </TableCell>
                      <TableCell className="text-right font-mono font-bold text-slate-900">
                        {formatCurrency(inv.balance_amount)}
                      </TableCell>
                      <TableCell className="text-center">
                        <Badge
                          variant={
                            inv.status === 'PAID'
                              ? 'success'
                              : inv.status === 'PARTIALLY_PAID'
                              ? 'warning'
                              : 'destructive'
                          }
                        >
                          {inv.status.replace('_', ' ')}
                        </Badge>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>

          {/* Payment Receipts History */}
          <Card>
            <CardHeader>
              <CardTitle>Payment & Transaction History</CardTitle>
              <CardDescription>
                Automated bank and M-Pesa Daraja reconciliation records
              </CardDescription>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Ref Number</TableHead>
                    <TableHead>Method</TableHead>
                    <TableHead>Date</TableHead>
                    <TableHead className="text-right">Amount</TableHead>
                    <TableHead className="text-center">Status</TableHead>
                    <TableHead className="text-right">Receipt</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {mockPayments.map((pay) => (
                    <TableRow key={pay.id}>
                      <TableCell className="font-mono font-bold text-slate-900">
                        {pay.reference_number}
                      </TableCell>
                      <TableCell>
                        <div className="flex items-center gap-1.5 font-medium text-xs">
                          <Smartphone className="h-4 w-4 text-emerald-600" />
                          <span>M-Pesa Daraja</span>
                        </div>
                      </TableCell>
                      <TableCell className="text-xs text-slate-600">
                        {formatDate(pay.transaction_date)}
                      </TableCell>
                      <TableCell className="text-right font-mono font-bold text-emerald-700">
                        {formatCurrency(pay.amount)}
                      </TableCell>
                      <TableCell className="text-center">
                        <Badge variant="success" dot>
                          {pay.status}
                        </Badge>
                      </TableCell>
                      <TableCell className="text-right">
                        <Button size="sm" variant="ghost" className="h-8 text-xs gap-1 text-mema-teal-800">
                          <Receipt className="h-3.5 w-3.5" /> PDF
                        </Button>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </div>

        {/* Right Column: Instant M-Pesa Checkout */}
        <div className="space-y-6">
          <Card className="border-mema-green-600/30 shadow-lg bg-gradient-to-b from-white to-mema-green-50/20">
            <CardHeader className="bg-mema-green-800 text-white rounded-t-xl">
              <div className="flex items-center justify-between">
                <CardTitle className="text-white text-lg flex items-center gap-2">
                  <Smartphone className="h-5 w-5 text-emerald-300" />
                  M-Pesa Express (STK Push)
                </CardTitle>
              </div>
              <CardDescription className="text-emerald-100 text-xs">
                Instant clearance on student account
              </CardDescription>
            </CardHeader>
            <CardContent className="p-6 space-y-4">
              <form onSubmit={handleMpesaPay} className="space-y-4">
                <div>
                  <label className="text-xs font-semibold text-slate-700 mb-1 block">
                    M-Pesa Mobile Number
                  </label>
                  <Input
                    type="tel"
                    value={phoneNumber}
                    onChange={(e) => setPhoneNumber(e.target.value)}
                    placeholder="07XXXXXXXX"
                    leftIcon={<Phone className="h-4 w-4" />}
                    required
                  />
                  <p className="text-[11px] text-slate-500 mt-1">
                    An STK prompt will appear on your phone to enter your PIN.
                  </p>
                </div>

                <div>
                  <label className="text-xs font-semibold text-slate-700 mb-1 block">
                    Amount (KES)
                  </label>
                  <Input
                    type="number"
                    value={payAmount}
                    onChange={(e) => setPayAmount(e.target.value)}
                    placeholder="Amount to pay"
                    leftIcon={<CreditCard className="h-4 w-4" />}
                    required
                  />
                </div>

                <div className="p-3 rounded-lg bg-slate-100/80 border border-slate-200 text-xs text-slate-600 space-y-1">
                  <div className="flex justify-between">
                    <span>Paybill Number:</span>
                    <span className="font-mono font-bold text-slate-900">400222</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Account Number:</span>
                    <span className="font-mono font-bold text-slate-900">CT201/0042/23</span>
                  </div>
                </div>

                <Button
                  type="submit"
                  isLoading={isProcessing}
                  className="w-full bg-mema-green-600 hover:bg-mema-green-700 text-white font-bold py-3 shadow-md"
                >
                  Pay {formatCurrency(Number(payAmount) || 0)} Now
                </Button>
              </form>

              <div className="flex items-center justify-center gap-2 pt-2 text-[11px] text-slate-500">
                <ShieldCheck className="h-4 w-4 text-mema-green-600" />
                <span>256-Bit Encrypted & Auto-Reconciled</span>
              </div>
            </CardContent>
          </Card>

          {/* Direct Bank Deposit Accounts */}
          <Card>
            <CardHeader className="pb-3">
              <CardTitle className="text-base flex items-center gap-2">
                <Building className="h-4 w-4 text-mema-teal-800" />
                Direct Bank Accounts
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-3 text-xs text-slate-600">
              <div className="p-3 rounded-lg border border-slate-200 bg-slate-50">
                <p className="font-bold text-slate-900">Kenya Commercial Bank (KCB)</p>
                <p className="font-mono text-slate-600">Account: 1102938491</p>
                <p className="text-[11px] text-slate-500">Branch: University Way</p>
              </div>
              <div className="p-3 rounded-lg border border-slate-200 bg-slate-50">
                <p className="font-bold text-slate-900">Co-operative Bank of Kenya</p>
                <p className="font-mono text-slate-600">Account: 01129384910200</p>
                <p className="text-[11px] text-slate-500">Branch: City Centre</p>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
