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
  formatDate,
} from '@mema/ui';
import {
  FileText,
  Download,
} from 'lucide-react';

export default function AdminAdmissionsPage() {
  const applicants = [
    {
      id: 'app-01',
      appNumber: 'APP-2026-0892',
      name: 'Grace Mutiso',
      email: 'grace.mutiso@gmail.com',
      programme: 'BSc in Computer Science',
      meanGrade: 'A- (78 pts)',
      status: 'UNDER_REVIEW',
      submissionDate: '2026-08-20',
    },
    {
      id: 'app-02',
      appNumber: 'APP-2026-0893',
      name: 'Kevin Kiprono',
      email: 'kevin.kip@outlook.com',
      programme: 'BSc in Software Engineering',
      meanGrade: 'A (82 pts)',
      status: 'ADMITTED',
      submissionDate: '2026-08-19',
    },
    {
      id: 'app-03',
      appNumber: 'APP-2026-0894',
      name: 'Faith Achieng',
      email: 'achieng.f@gmail.com',
      programme: 'BSc in Computer Science',
      meanGrade: 'B+ (71 pts)',
      status: 'MATRICULATED',
      submissionDate: '2026-08-18',
    },
  ];

  return (
    <div className="space-y-8">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 font-heading">
            Admissions & Applicant Review
          </h2>
          <p className="text-sm text-slate-500 mt-1">
            KUCCPS & Direct online applications, document verification, scoring & matriculation (MOD-01-05)
          </p>
        </div>
        <div className="flex items-center gap-2 self-start sm:self-auto">
          <Button variant="outline" className="gap-2">
            <Download className="h-4 w-4" /> Export KUCCPS List
          </Button>
        </div>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Intake 2026/2027 Applicant Queue</CardTitle>
          <CardDescription>
            Verify academic transcripts and issue automated offer letters
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Application #</TableHead>
                <TableHead>Applicant Name & Email</TableHead>
                <TableHead>Target Programme</TableHead>
                <TableHead className="text-center">KCSE / Entry Score</TableHead>
                <TableHead>Submitted</TableHead>
                <TableHead className="text-center">Status</TableHead>
                <TableHead className="text-right">Action</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {applicants.map((app) => (
                <TableRow key={app.id}>
                  <TableCell className="font-mono font-bold text-mema-teal-900">
                    {app.appNumber}
                  </TableCell>
                  <TableCell>
                    <div className="font-semibold text-slate-900">{app.name}</div>
                    <div className="text-xs text-slate-500">{app.email}</div>
                  </TableCell>
                  <TableCell className="text-xs font-medium text-slate-800">
                    {app.programme}
                  </TableCell>
                  <TableCell className="text-center font-mono font-bold text-slate-900">
                    {app.meanGrade}
                  </TableCell>
                  <TableCell className="text-xs text-slate-600">
                    {formatDate(app.submissionDate)}
                  </TableCell>
                  <TableCell className="text-center">
                    <Badge
                      variant={
                        app.status === 'MATRICULATED'
                          ? 'success'
                          : app.status === 'ADMITTED'
                          ? 'info'
                          : 'warning'
                      }
                    >
                      {app.status.replace('_', ' ')}
                    </Badge>
                  </TableCell>
                  <TableCell className="text-right">
                    <div className="flex items-center justify-end gap-2">
                      <Button size="sm" variant="outline" className="h-8 text-xs gap-1">
                        <FileText className="h-3.5 w-3.5" /> Review
                      </Button>
                      <Button size="sm" className="h-8 text-xs bg-mema-green-600 hover:bg-mema-green-700 text-white">
                        Admit
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
