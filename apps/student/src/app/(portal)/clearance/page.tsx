'use client';

import React from 'react';
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  Badge,
} from '@mema/ui';
import {
  Building,
  BookOpen,
  DollarSign,
  ShieldAlert,
} from 'lucide-react';

export default function StudentClearancePage() {
  const clearanceSteps = [
    {
      department: 'Department of Computer Science (HOD)',
      status: 'APPROVED',
      date: '2026-08-10',
      officer: 'Prof. J. Omondi (HOD)',
      icon: <BookOpen className="h-5 w-5 text-mema-teal-700" />,
      notes: 'Completed all prerequisite practicals & project work.',
    },
    {
      department: 'University Library',
      status: 'APPROVED',
      date: '2026-08-12',
      officer: 'M. Wanjiku (Chief Librarian)',
      icon: <Building className="h-5 w-5 text-mema-teal-700" />,
      notes: 'No outstanding book loans or overdue fines.',
    },
    {
      department: 'Finance & Student Accounts',
      status: 'PENDING',
      date: '-',
      officer: 'Finance Bursar',
      icon: <DollarSign className="h-5 w-5 text-amber-600" />,
      notes: 'Requires clearance of current term balance (KES 35,000).',
    },
    {
      department: 'Hostels & Accommodation',
      status: 'APPROVED',
      date: '2026-08-14',
      officer: 'Hostel Custodian',
      icon: <Building className="h-5 w-5 text-mema-teal-700" />,
      notes: 'Room 402 handed over in good order.',
    },
    {
      department: 'Dean of Students & Security',
      status: 'APPROVED',
      date: '2026-08-15',
      officer: 'Dean of Student Affairs',
      icon: <ShieldAlert className="h-5 w-5 text-mema-teal-700" />,
      notes: 'No disciplinary matters pending.',
    },
  ];

  return (
    <div className="space-y-8">
      <div>
        <h2 className="text-2xl font-bold text-slate-900 font-heading">
          Automated Digital Clearance Hub
        </h2>
        <p className="text-sm text-slate-500 mt-1">
          Paperless multi-departmental clearance for exams, completion & graduation
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center justify-between">
            <span>Clearance Progress</span>
            <Badge variant="warning">4 of 5 Completed</Badge>
          </CardTitle>
          <CardDescription>
            Once all 5 departments sign off, your official clearance certificate and graduation docket will unlock automatically.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-4">
            {clearanceSteps.map((step, idx) => {
              const isApproved = step.status === 'APPROVED';
              return (
                <div
                  key={idx}
                  className={`p-4 rounded-xl border flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-colors ${
                    isApproved
                      ? 'border-emerald-200 bg-emerald-50/40'
                      : 'border-amber-200 bg-amber-50/40'
                  }`}
                >
                  <div className="flex items-start gap-3.5">
                    <div
                      className={`h-10 w-10 rounded-xl flex items-center justify-center shrink-0 ${
                        isApproved
                          ? 'bg-emerald-100 text-emerald-800'
                          : 'bg-amber-100 text-amber-800'
                      }`}
                    >
                      {step.icon}
                    </div>
                    <div className="space-y-1">
                      <h4 className="font-semibold text-slate-900 text-sm">
                        {step.department}
                      </h4>
                      <p className="text-xs text-slate-600">{step.notes}</p>
                      <p className="text-[11px] text-slate-400">
                        Signed off by: <span className="font-medium text-slate-700">{step.officer}</span>
                        {step.date !== '-' && ` · on ${step.date}`}
                      </p>
                    </div>
                  </div>

                  <div className="flex items-center gap-2 self-start sm:self-auto">
                    <Badge variant={isApproved ? 'success' : 'warning'} dot>
                      {step.status}
                    </Badge>
                  </div>
                </div>
              );
            })}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
