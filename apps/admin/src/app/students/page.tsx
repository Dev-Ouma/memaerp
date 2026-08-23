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
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
} from '@mema/ui';
import { Search, Filter, Eye, Download } from 'lucide-react';
import { mockCurrentStudent } from '@mema/api-client';

export default function AdminStudentsPage() {
  const [searchTerm, setSearchTerm] = useState('');

  const students = [
    mockCurrentStudent,
    {
      id: 'stud-02',
      institution_id: 'inst-01',
      person_id: 'pers-02',
      programme_id: 'prog-01',
      campus_id: 'camp-01',
      student_number: 'CT201/0043/23',
      admission_term_id: 'term-sem1-2023',
      current_year_level: 3,
      current_term_sequence: 1,
      status: 'ACTIVE' as const,
      cumulative_gpa: 3.45,
      cumulative_credits_earned: 72,
      created_at: '2023-09-01T00:00:00Z',
      updated_at: '2026-08-20T00:00:00Z',
      person: {
        id: 'pers-02',
        institution_id: 'inst-01',
        first_name: 'Grace',
        last_name: 'Mutiso',
        national_id_number: '38920183',
        gender: 'FEMALE' as const,
        nationality: 'Kenyan',
        created_at: '2023-09-01T00:00:00Z',
        updated_at: '2026-08-20T00:00:00Z',
      },
      programme: mockCurrentStudent.programme,
      campus: mockCurrentStudent.campus,
    },
    {
      id: 'stud-03',
      institution_id: 'inst-01',
      person_id: 'pers-03',
      programme_id: 'prog-02',
      campus_id: 'camp-01',
      student_number: 'SE201/0012/24',
      admission_term_id: 'term-sem1-2024',
      current_year_level: 2,
      current_term_sequence: 1,
      status: 'ACTIVE' as const,
      cumulative_gpa: 3.91,
      cumulative_credits_earned: 40,
      created_at: '2024-09-01T00:00:00Z',
      updated_at: '2026-08-20T00:00:00Z',
      person: {
        id: 'pers-03',
        institution_id: 'inst-01',
        first_name: 'Kevin',
        last_name: 'Kiprono',
        gender: 'MALE' as const,
        created_at: '2024-09-01T00:00:00Z',
        updated_at: '2026-08-20T00:00:00Z',
      },
      programme: {
        id: 'prog-02',
        code: 'BSC-SE',
        title: 'BSc in Software Engineering',
      } as any,
      campus: mockCurrentStudent.campus,
    },
  ];

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

      {/* Filter Bar */}
      <div className="flex flex-col sm:flex-row items-center gap-4">
        <div className="flex-1 w-full">
          <Input
            placeholder="Search by student number, national ID, or student name..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            leftIcon={<Search className="h-4 w-4" />}
          />
        </div>
        <Button variant="outline" className="gap-2 shrink-0">
          <Filter className="h-4 w-4" /> Filters
        </Button>
      </div>

      {/* Students Table */}
      <Card>
        <CardHeader>
          <CardTitle>Matriculated Student Registry</CardTitle>
          <CardDescription>
            Single canonical person spine across applicant, student, and alumnus lifecycles
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Student ID</TableHead>
                <TableHead>Full Name</TableHead>
                <TableHead>Programme</TableHead>
                <TableHead className="text-center">Year / Sem</TableHead>
                <TableHead className="text-center">CGPA</TableHead>
                <TableHead className="text-center">Credits</TableHead>
                <TableHead className="text-center">Status</TableHead>
                <TableHead className="text-right">Action</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {students.map((st) => (
                <TableRow key={st.id}>
                  <TableCell className="font-mono font-bold text-mema-teal-900">
                    {st.student_number}
                  </TableCell>
                  <TableCell className="font-semibold text-slate-900">
                    {st.person?.first_name} {st.person?.last_name}
                  </TableCell>
                  <TableCell className="text-xs font-medium text-slate-800">
                    {st.programme?.title}
                  </TableCell>
                  <TableCell className="text-center font-mono text-xs">
                    Yr {st.current_year_level} · Sem {st.current_term_sequence}
                  </TableCell>
                  <TableCell className="text-center font-mono font-bold text-slate-900">
                    {st.cumulative_gpa.toFixed(2)}
                  </TableCell>
                  <TableCell className="text-center font-mono text-xs font-semibold text-slate-700">
                    {st.cumulative_credits_earned}
                  </TableCell>
                  <TableCell className="text-center">
                    <Badge variant="success" dot>
                      {st.status}
                    </Badge>
                  </TableCell>
                  <TableCell className="text-right">
                    <Button size="sm" variant="ghost" className="h-8 text-xs gap-1 text-mema-teal-800">
                      <Eye className="h-3.5 w-3.5" /> View Profile
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
