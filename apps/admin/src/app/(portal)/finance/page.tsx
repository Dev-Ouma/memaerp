'use client';

import React from 'react';
import { useQuery } from '@tanstack/react-query';
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  Badge,
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
  formatCurrency,
  formatDate,
} from '@mema/ui';
import { api } from '@mema/api-client';
import { RefreshCw } from 'lucide-react';

export default function AdminFinancePage() {
  const statement = useQuery({ queryKey: ['finance', 'statement'], queryFn: () => api.getFinanceStatement() });

  return (
    <div className="space-y-8">
      <div>
        <h2 className="text-2xl font-bold text-slate-900 font-heading">Student Finance & Fee Operations</h2>
        <p className="text-sm text-slate-500 mt-1">
          Live invoices, payments, and clearance from <code className="text-xs">GET /api/v1/finance/statement</code>
        </p>
      </div>

      <div className="grid gap-4 sm:grid-cols-3">
        <Card>
          <CardHeader className="pb-2">
            <CardDescription>Total due</CardDescription>
            <CardTitle>{formatCurrency(Number(statement.data?.clearance.total_due ?? 0))}</CardTitle>
          </CardHeader>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardDescription>Total paid</CardDescription>
            <CardTitle>{formatCurrency(Number(statement.data?.clearance.total_paid ?? 0))}</CardTitle>
          </CardHeader>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardDescription>Registration clearance</CardDescription>
            <CardTitle>
              {statement.data?.clearance.registration_cleared ? 'Cleared' : 'Blocked'}
            </CardTitle>
          </CardHeader>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <RefreshCw className="h-4 w-4" /> Recent payments
          </CardTitle>
          <CardDescription>Institution-wide payment ledger (MOD-01-09)</CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Receipt</TableHead>
                <TableHead>Method</TableHead>
                <TableHead>Reference</TableHead>
                <TableHead>Date</TableHead>
                <TableHead className="text-right">Amount</TableHead>
                <TableHead className="text-center">Status</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {(statement.data?.payments ?? []).map((pay) => (
                <TableRow key={pay.id}>
                  <TableCell className="font-mono text-xs">{pay.reference_number ?? pay.id.slice(0, 8)}</TableCell>
                  <TableCell>
                    <Badge variant="outline">{pay.payment_method}</Badge>
                  </TableCell>
                  <TableCell className="font-mono text-xs">{pay.reference_number ?? '—'}</TableCell>
                  <TableCell>{pay.transaction_date ? formatDate(pay.transaction_date) : '—'}</TableCell>
                  <TableCell className="text-right font-mono">{formatCurrency(Number(pay.amount))}</TableCell>
                  <TableCell className="text-center">{pay.status}</TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  );
}
