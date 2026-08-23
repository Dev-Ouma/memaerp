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
import {
  Download,
  CheckCircle2,
} from 'lucide-react';
import { mockRecentMarks, mockCurrentStudent } from '@mema/api-client';
import type { StudentMark } from '@mema/types';

type ResultMark = Partial<StudentMark> & {
  id: string;
  code?: string;
  title?: string;
  credits?: number;
  cat?: number;
  exam?: number;
  total?: number;
  grade?: string;
  points?: number;
};

export default function StudentResultsPage() {
  const semestersData = [
    {
      termName: 'Year 2 · Semester 2 (Jan - Apr 2026)',
      termGpa: 3.82,
      cumulativeGpa: 3.82,
      standing: 'Good Standing',
      marks: mockRecentMarks,
    },
    {
      termName: 'Year 2 · Semester 1 (Sep - Dec 2025)',
      termGpa: 3.70,
      cumulativeGpa: 3.74,
      standing: 'Good Standing',
      marks: [
        {
          id: 'mark-past-1',
          code: 'CSC 202',
          title: 'Object-Oriented Analysis & Design',
          credits: 4,
          cat: 26,
          exam: 58,
          total: 84,
          grade: 'A',
          points: 4.0,
        },
        {
          id: 'mark-past-2',
          code: 'MAT 201',
          title: 'Linear Algebra for Computing',
          credits: 3,
          cat: 22,
          exam: 50,
          total: 72,
          grade: 'B',
          points: 3.0,
        },
      ],
    },
  ];

  return (
    <div className="space-y-8">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 font-heading">
            Academic Performance & Transcripts
          </h2>
          <p className="text-sm text-slate-500 mt-1">
            Senate-approved examination marks, GPA progression, and degree audit
          </p>
        </div>
        <Button className="gap-2 self-start sm:self-auto bg-mema-teal-800 hover:bg-mema-teal-700 text-white">
          <Download className="h-4 w-4" /> Download Official PDF Transcript
        </Button>
      </div>

      {/* Degree Progression Metrics */}
      <div className="grid grid-cols-1 sm:grid-cols-4 gap-5">
        <Card className="p-5 bg-white border-l-4 border-l-mema-teal-800">
          <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">
            Cumulative GPA (CGPA)
          </p>
          <h3 className="text-3xl font-bold text-mema-teal-900 mt-2 font-heading">
            {mockCurrentStudent.cumulative_gpa}
          </h3>
          <p className="text-xs text-emerald-600 font-semibold mt-1">
            First Class Honours Classification
          </p>
        </Card>

        <Card className="p-5 bg-white border-l-4 border-l-mema-green-600">
          <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">
            Credits Earned
          </p>
          <h3 className="text-3xl font-bold text-mema-green-700 mt-2 font-heading">
            {mockCurrentStudent.cumulative_credits_earned} / 144
          </h3>
          <p className="text-xs text-slate-500 mt-1">68 units remaining for graduation</p>
        </Card>

        <Card className="p-5 bg-white border-l-4 border-l-blue-600">
          <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">
            Academic Standing
          </p>
          <h3 className="text-2xl font-bold text-blue-900 mt-2 font-heading">
            Good Standing
          </h3>
          <p className="text-xs text-slate-500 mt-1">Satisfied all core requirements</p>
        </Card>

        <Card className="p-5 bg-white border-l-4 border-l-amber-500">
          <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">
            Verification Hash
          </p>
          <p className="font-mono text-xs text-slate-700 mt-3 truncate">
            0x8F92A108C23B...
          </p>
          <p className="text-[11px] text-emerald-600 font-semibold mt-1 flex items-center gap-1">
            <CheckCircle2 className="h-3.5 w-3.5" /> Senate Immutable Hash
          </p>
        </Card>
      </div>

      {/* Historical Breakdown per Semester */}
      <div className="space-y-6">
        {semestersData.map((sem, idx) => (
          <Card key={idx}>
            <CardHeader className="flex flex-row items-center justify-between pb-4 bg-slate-50/70 rounded-t-xl">
              <div>
                <CardTitle className="text-base text-slate-900">{sem.termName}</CardTitle>
                <CardDescription>
                  Semester GPA: <strong>{sem.termGpa.toFixed(2)}</strong> · Cumulative GPA: <strong>{sem.cumulativeGpa.toFixed(2)}</strong>
                </CardDescription>
              </div>
              <Badge variant="success">{sem.standing}</Badge>
            </CardHeader>
            <CardContent className="pt-4">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Course Code & Title</TableHead>
                    <TableHead className="text-center">Credits</TableHead>
                    <TableHead className="text-center">CAT (30)</TableHead>
                    <TableHead className="text-center">Exam (70)</TableHead>
                    <TableHead className="text-center">Total (100)</TableHead>
                    <TableHead className="text-center">Grade</TableHead>
                    <TableHead className="text-right">Grade Point</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {sem.marks.map((m: ResultMark) => {
                    const code = m.code || m.course_enrollment?.course_offering?.course?.code;
                    const title = m.title || m.course_enrollment?.course_offering?.course?.title;
                    const credits = m.credits || m.course_enrollment?.course_offering?.course?.credit_units || 4;
                    const cat = m.cat ?? m.cat_score;
                    const exam = m.exam ?? m.exam_score;
                    const total = m.total ?? m.total_score;
                    const grade = m.grade || m.grade_letter;
                    const points = m.points ?? m.grade_points;

                    return (
                      <TableRow key={m.id}>
                        <TableCell>
                          <div className="font-semibold text-slate-900">{code}</div>
                          <div className="text-xs text-slate-500">{title}</div>
                        </TableCell>
                        <TableCell className="text-center font-mono">{credits}</TableCell>
                        <TableCell className="text-center font-mono">{cat}</TableCell>
                        <TableCell className="text-center font-mono">{exam}</TableCell>
                        <TableCell className="text-center font-mono font-bold text-slate-900">
                          {total}%
                        </TableCell>
                        <TableCell className="text-center">
                          <Badge variant={grade === 'A' || grade === 'B+' ? 'success' : 'default'}>
                            {grade}
                          </Badge>
                        </TableCell>
                        <TableCell className="text-right font-mono font-bold text-mema-teal-900">
                          {Number(points).toFixed(2)}
                        </TableCell>
                      </TableRow>
                    );
                  })}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
}
