'use client';

import React, { useMemo, useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import {
  Badge,
  Button,
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
  Input,
  personDisplayName,
  programmeLabel,
} from '@mema/ui';
import { DataTable } from '@mema/tables';
import { api } from '@mema/api-client';
import { Download, Eye, Filter, Search } from 'lucide-react';
import type { Student } from '@mema/types';

export default function AdminStudentsPage() {
  const [searchTerm, setSearchTerm] = useState('');
  const { data, isLoading, isError, error } = useQuery({
    queryKey: ['enrollment', 'students'],
    queryFn: () => api.getStudents(),
  });

  const students = useMemo(() => {
    const rows = data ?? [];
    const query = searchTerm.trim().toLowerCase();
    if (!query) return rows;

    return rows.filter((student) => {
      const haystack = [
        student.student_number,
        personDisplayName(student.person),
        programmeLabel(student.programme ?? { code: 'unknown', title: 'Unknown programme' }),
      ]
        .join(' ')
        .toLowerCase();
      return haystack.includes(query);
    });
  }, [data, searchTerm]);

  return (
    <div className="space-y-8">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 font-heading">
            Student Information System (SIS)
          </h2>
          <p className="text-sm text-slate-500 mt-1">
            Matriculated students, canonical person records, and progression statuses (MOD-01-06)
          </p>
        </div>
        <Button variant="outline" className="gap-2 self-start sm:self-auto">
          <Download className="h-4 w-4" /> Export Student Master File
        </Button>
      </div>

      <div className="flex flex-col sm:flex-row items-center gap-4">
        <div className="flex-1 w-full">
          <Input
            placeholder="Search by student number, national ID, or student name..."
            value={searchTerm}
            onChange={(event) => setSearchTerm(event.target.value)}
            leftIcon={<Search className="h-4 w-4" />}
          />
        </div>
        <Button variant="outline" className="gap-2 shrink-0">
          <Filter className="h-4 w-4" /> Filters
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Matriculated Student Registry</CardTitle>
          <CardDescription>
            Loaded from <code className="text-xs">GET /api/v1/enrollment/students</code>
          </CardDescription>
        </CardHeader>
        <CardContent>
          <DataTable<Student>
            isLoading={isLoading}
            isError={isError}
            errorMessage={error instanceof Error ? error.message : undefined}
            data={students}
            getRowKey={(student) => student.id}
            emptyMessage="No students found for the current search."
            columns={[
              {
                key: 'student_number',
                header: 'Student ID',
                cell: (student) => (
                  <span className="font-mono font-bold text-mema-teal-900">
                    {student.student_number}
                  </span>
                ),
              },
              {
                key: 'name',
                header: 'Full Name',
                cell: (student) => (
                  <span className="font-semibold text-slate-900">
                    {personDisplayName(student.person)}
                  </span>
                ),
              },
              {
                key: 'programme',
                header: 'Programme',
                cell: (student) => (
                  <span className="text-xs font-medium text-slate-800">
                    {student.programme ? programmeLabel(student.programme) : '—'}
                  </span>
                ),
              },
              {
                key: 'year',
                header: 'Year / Sem',
                className: 'text-center',
                cell: (student) => (
                  <span className="font-mono text-xs">
                    Yr {student.current_year_level} · Sem {student.current_term_sequence}
                  </span>
                ),
              },
              {
                key: 'cgpa',
                header: 'CGPA',
                className: 'text-center',
                cell: (student) => (
                  <span className="font-mono font-bold text-slate-900">
                    {Number(student.cumulative_gpa).toFixed(2)}
                  </span>
                ),
              },
              {
                key: 'credits',
                header: 'Credits',
                className: 'text-center',
                cell: (student) => (
                  <span className="font-mono text-xs font-semibold text-slate-700">
                    {student.cumulative_credits_earned}
                  </span>
                ),
              },
              {
                key: 'status',
                header: 'Status',
                className: 'text-center',
                cell: (student) => (
                  <Badge variant="success" dot>
                    {student.status}
                  </Badge>
                ),
              },
              {
                key: 'actions',
                header: 'Action',
                className: 'text-right',
                cell: () => (
                  <Button size="sm" variant="ghost" className="h-8 text-xs gap-1 text-mema-teal-800">
                    <Eye className="h-3.5 w-3.5" /> View Profile
                  </Button>
                ),
              },
            ]}
          />
        </CardContent>
      </Card>
    </div>
  );
}
