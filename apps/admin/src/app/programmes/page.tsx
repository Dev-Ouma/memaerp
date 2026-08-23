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
} from '@mema/ui';
import { Plus, Edit, Layers } from 'lucide-react';
import { mockProgrammes } from '@mema/api-client';

export default function AdminProgrammesPage() {
  return (
    <div className="space-y-8">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 font-heading">
            Degree Programmes & Curricula
          </h2>
          <p className="text-sm text-slate-500 mt-1">
            Academic programmes, curriculum versions and credit requirements (MOD-01-03)
          </p>
        </div>
        <Button className="bg-mema-teal-800 hover:bg-mema-teal-700 text-white gap-2 self-start sm:self-auto">
          <Plus className="h-4 w-4" /> Create New Programme
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Accredited Institutional Programmes</CardTitle>
          <CardDescription>
            All programme versions are immutable once assigned to enrolled cohorts.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Code</TableHead>
                <TableHead>Programme Title</TableHead>
                <TableHead>Department</TableHead>
                <TableHead>Award Level</TableHead>
                <TableHead className="text-center">Duration</TableHead>
                <TableHead className="text-center">Required Credits</TableHead>
                <TableHead className="text-center">Status</TableHead>
                <TableHead className="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {mockProgrammes.map((prog) => (
                <TableRow key={prog.id}>
                  <TableCell className="font-mono font-bold text-mema-teal-900">
                    {prog.code}
                  </TableCell>
                  <TableCell className="font-semibold text-slate-900">
                    {prog.title}
                  </TableCell>
                  <TableCell className="text-xs text-slate-600">
                    {prog.department?.name}
                  </TableCell>
                  <TableCell>
                    <Badge variant="outline">{prog.award_level}</Badge>
                  </TableCell>
                  <TableCell className="text-center font-mono text-xs">
                    {prog.duration_years} Years
                  </TableCell>
                  <TableCell className="text-center font-mono font-bold text-slate-800">
                    {prog.credit_units_required}
                  </TableCell>
                  <TableCell className="text-center">
                    <Badge variant="success" dot>
                      Active
                    </Badge>
                  </TableCell>
                  <TableCell className="text-right">
                    <div className="flex items-center justify-end gap-2">
                      <Button size="sm" variant="outline" className="h-8 text-xs gap-1">
                        <Layers className="h-3.5 w-3.5" /> Curricula
                      </Button>
                      <Button size="sm" variant="ghost" className="h-8 text-xs">
                        <Edit className="h-3.5 w-3.5" />
                      </Button>
                    </div>
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
