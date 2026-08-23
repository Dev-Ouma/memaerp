'use client';

import React from 'react';
import { useQuery } from '@tanstack/react-query';
import {
  Alert,
  AlertDescription,
  AlertTitle,
  Badge,
  Button,
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@mema/ui';
import { api } from '@mema/api-client';
import { Plus, Edit, Layers } from 'lucide-react';

function programmeTitle(programme: {
  title?: string;
  name?: string;
  code: string;
}) {
  return programme.title ?? programme.name ?? programme.code;
}

function programmeCredits(programme: {
  credit_units_required?: number;
  total_credits_required?: number;
}) {
  return programme.credit_units_required ?? programme.total_credits_required ?? 0;
}

export default function AdminProgrammesPage() {
  const { data, isLoading, isError, error, refetch, isFetching } = useQuery({
    queryKey: ['curriculum', 'programmes'],
    queryFn: () => api.getProgrammes(),
  });

  const programmes = data ?? [];

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

      {isError && (
        <Alert variant="destructive">
          <AlertTitle>Unable to load programmes</AlertTitle>
          <AlertDescription>
            {error instanceof Error ? error.message : 'An unexpected error occurred.'}
          </AlertDescription>
        </Alert>
      )}

      <Card>
        <CardHeader className="flex flex-row items-center justify-between gap-4">
          <div>
            <CardTitle>Accredited Institutional Programmes</CardTitle>
            <CardDescription>
              Loaded from <code className="text-xs">GET /api/v1/curriculum/programmes</code>
              {isFetching ? ' · refreshing…' : ''}
            </CardDescription>
          </div>
          <Button variant="outline" size="sm" onClick={() => refetch()} disabled={isLoading}>
            Refresh
          </Button>
        </CardHeader>
        <CardContent>
          {isLoading ? (
            <div className="py-12 text-center text-sm text-slate-500">Loading programmes...</div>
          ) : programmes.length === 0 ? (
            <div className="py-12 text-center text-sm text-slate-500">
              No programmes found for your institution.
            </div>
          ) : (
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
                {programmes.map((prog) => (
                  <TableRow key={prog.id}>
                    <TableCell className="font-mono font-bold text-mema-teal-900">
                      {prog.code}
                    </TableCell>
                    <TableCell className="font-semibold text-slate-900">
                      {programmeTitle(prog)}
                    </TableCell>
                    <TableCell className="text-xs text-slate-600">
                      {prog.department?.name ?? '—'}
                    </TableCell>
                    <TableCell>
                      <Badge variant="outline">{prog.award_level}</Badge>
                    </TableCell>
                    <TableCell className="text-center font-mono text-xs">
                      {prog.duration_years} Years
                    </TableCell>
                    <TableCell className="text-center font-mono font-bold text-slate-800">
                      {programmeCredits(prog)}
                    </TableCell>
                    <TableCell className="text-center">
                      <Badge variant="success" dot>
                        {prog.is_active ? 'Active' : 'Inactive'}
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
          )}
        </CardContent>
      </Card>
    </div>
  );
}
