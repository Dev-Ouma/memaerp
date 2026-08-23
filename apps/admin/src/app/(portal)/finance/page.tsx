'use client';

import React from 'react';
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  Button,
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
import { Plus, RefreshCw } from 'lucide-react';
import { mockPayments } from '@mema/api-client';

export default function AdminFinancePage() {
  return (
    <div className="space-y-8">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 font-heading">
            Student Finance & Fee Operations
          </h2>
          <p className="text-sm text-slate-500 mt-1">
            Fee structures, automated M-Pesa Daraja reconciliation, and general ledger feeds (MOD-01-09)
          </p>
        </div>
        <div className="flex items-center gap-2 self-start sm:self-auto">
          <Button variant="outline" className="gap-2">
            <RefreshCw className="h-4 w-4" /> Trigger Auto-Reconciliation
          </Button>
          <Button className="bg-mema-green-600 hover:bg-mema-green-700 text-white gap-2">
            <Plus className="h-4 w-4" /> New Fee Structure
          </Button>
        </div>
      </div>

      {/* Reconciliation Table */}
      <Card>
        <CardHeader>
          <CardTitle>M-Pesa & Bank Transactions Feed</CardTitle>
          <CardDescription>
            Idempotent webhook logs matched against student invoice balances
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Reference #</TableHead>
                <TableHead>Channel</TableHead>
                <TableHead>Student ID & Name</TableHead>
                <TableHead>Date & Time</TableHead>
                <TableHead className="text-right">Amount</TableHead>
                <TableHead className="text-center">Match Status</TableHead>
                <TableHead className="text-right">Action</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {mockPayments.map((pay) => (
                <TableRow key={pay.id}>
                  <TableCell className="font-mono font-bold text-slate-900">
                    {pay.reference_number}
                  </TableCell>
                  <TableCell>
                    <Badge variant="outline" className="font-mono text-[11px]">
                      {pay.payment_method}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <div className="font-semibold text-slate-900">
                      CT201/0042/23
                    </div>
                    <div className="text-xs text-slate-500">Ian Wabwire</div>
                  </TableCell>
                  <TableCell className="text-xs text-slate-600">
                    {formatDate(pay.transaction_date)}
                  </TableCell>
                  <TableCell className="text-right font-mono font-bold text-emerald-700">
                    {formatCurrency(pay.amount)}
                  </TableCell>
                  <TableCell className="text-center">
                    <Badge variant="success" dot>
                      AUTO_RECONCILED
                    </Badge>
                  </TableCell>
                  <TableCell className="text-right">
                    <Button size="sm" variant="ghost" className="h-8 text-xs text-mema-teal-800">
                      View Ledger Entry
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  );
}
