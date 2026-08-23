'use client';

import React from 'react';
import Link from 'next/link';
import {
  StatCard,
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
} from '@mema/ui';
import {
  Users,
  CircleDollarSign,
  ShieldCheck,
  ArrowRight,
  UserCheck,
  Activity,
  Server,
} from 'lucide-react';
import {
  mockProgrammes,
  mockOfferings,
} from '@mema/api-client';

export default function AdminDashboardPage() {
  const recentLogs = [
    {
      id: 'log-1',
      actor: 'Prof. J. Omondi (HOD)',
      module: 'Examination',
      action: 'APPROVED_MARKS',
      subject: 'CSC 201 - Section A (65 records)',
      time: '12 mins ago',
      status: 'VERIFIED',
    },
    {
      id: 'log-2',
      actor: 'Finance Officer (M-Pesa Gateway)',
      module: 'Finance',
      action: 'RECONCILED_PAYMENT',
      subject: 'KES 50,000 · Ref: RHK82910J1',
      time: '25 mins ago',
      status: 'SUCCESS',
    },
    {
      id: 'log-3',
      actor: 'Admissions Lead',
      module: 'Admissions',
      action: 'ISSUED_OFFER_LETTER',
      subject: 'App #MEMA-2026-0892',
      time: '1 hour ago',
      status: 'SUCCESS',
    },
    {
      id: 'log-4',
      actor: 'Registrar',
      module: 'IAM',
      action: 'ASSIGNED_ROLE',
      subject: 'Lecturer Role -> Dr. P. Kamau (Scoped to CS)',
      time: '3 hours ago',
      status: 'AUDITED',
    },
  ];

  return (
    <div className="space-y-8">
      {/* Top Welcome */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 font-heading">
            Institutional Master Console
          </h2>
          <p className="text-sm text-slate-500 mt-1">
            Mema University ERP · Multi-Campus Operations & Live Telemetry
          </p>
        </div>
        <div className="flex items-center gap-2 self-start sm:self-auto">
          <Badge variant="success" className="px-3 py-1 text-xs" dot>
            Core Monolith Online · DB Schema v1.0
          </Badge>
        </div>
      </div>

      {/* KPI Stats */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <StatCard
          title="Active Matriculated Students"
          value="5,420"
          description="Across 4 faculties & 2 campuses"
          icon={<Users className="h-5 w-5" />}
          trend={{
            value: '+14.2%',
            isPositive: true,
            label: 'vs previous academic year',
          }}
        />

        <StatCard
          title="2026/27 Applicants"
          value="1,248"
          description="980 Admitted · 18 Pending review"
          icon={<UserCheck className="h-5 w-5" />}
          trend={{
            value: '78.5%',
            isPositive: true,
            label: 'Admissions conversion',
          }}
        />

        <StatCard
          title="Tuition Collected (Term 1)"
          value={formatCurrency(184250000)}
          description="86.2% of total billed tuition"
          icon={<CircleDollarSign className="h-5 w-5" />}
          trend={{
            value: '+8.4%',
            isPositive: true,
            label: 'Auto-reconciled via M-Pesa',
          }}
        />

        <StatCard
          title="Security & RBAC Health"
          value="100% Passed"
          description="0 Permission drifts detected"
          icon={<ShieldCheck className="h-5 w-5 text-emerald-600" />}
          trend={{
            value: 'Audited',
            isPositive: true,
            label: 'Append-only DB triggers',
          }}
        />
      </div>

      {/* Main Grid: Academic Offerings & Live Activity Feed */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Left 2 Cols: Active Academic Units & Capacity Tracker */}
        <div className="lg:col-span-2 space-y-6">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between pb-4">
              <div>
                <CardTitle>Course Sections & Capacity Telemetry</CardTitle>
                <CardDescription>
                  Real-time section enrollment concurrency & lecturer assignments
                </CardDescription>
              </div>
              <Link href="/courses">
                <Button variant="ghost" size="sm" className="gap-1 text-mema-teal-800">
                  Manage Sections <ArrowRight className="h-4 w-4" />
                </Button>
              </Link>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Course Code & Title</TableHead>
                    <TableHead>Section / Room</TableHead>
                    <TableHead className="text-center">Enrolled</TableHead>
                    <TableHead className="text-center">Capacity</TableHead>
                    <TableHead className="text-right">Occupancy</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {mockOfferings.map((offering) => {
                    const percentage = Math.round(
                      (offering.enrolled_count / offering.capacity) * 100
                    );
                    return (
                      <TableRow key={offering.id}>
                        <TableCell>
                          <div className="font-bold text-slate-900">
                            {offering.course?.code}
                          </div>
                          <div className="text-xs text-slate-500">
                            {offering.course?.title}
                          </div>
                        </TableCell>
                        <TableCell>
                          <div className="font-medium text-xs text-slate-800">
                            {offering.section_code}
                          </div>
                          <div className="text-[11px] text-slate-400">
                            {offering.room}
                          </div>
                        </TableCell>
                        <TableCell className="text-center font-mono font-bold text-slate-800">
                          {offering.enrolled_count}
                        </TableCell>
                        <TableCell className="text-center font-mono text-slate-500">
                          {offering.capacity}
                        </TableCell>
                        <TableCell className="text-right">
                          <div className="flex items-center justify-end gap-2">
                            <span className="font-mono text-xs font-bold text-slate-700">
                              {percentage}%
                            </span>
                            <div className="w-16 h-2 bg-slate-100 rounded-full overflow-hidden">
                              <div
                                className={`h-full rounded-full ${
                                  percentage >= 90
                                    ? 'bg-rose-500'
                                    : percentage >= 75
                                    ? 'bg-amber-500'
                                    : 'bg-emerald-500'
                                }`}
                                style={{ width: `${percentage}%` }}
                              />
                            </div>
                          </div>
                        </TableCell>
                      </TableRow>
                    );
                  })}
                </TableBody>
              </Table>
            </CardContent>
          </Card>

          {/* Academic Programmes Summary */}
          <Card>
            <CardHeader className="flex flex-row items-center justify-between pb-4">
              <div>
                <CardTitle>Accredited Degree Programmes</CardTitle>
                <CardDescription>
                  CUE-approved curricula and department affiliations
                </CardDescription>
              </div>
              <Link href="/programmes">
                <Button variant="ghost" size="sm" className="gap-1 text-mema-teal-800">
                  All Programmes <ArrowRight className="h-4 w-4" />
                </Button>
              </Link>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {mockProgrammes.map((prog) => (
                  <div
                    key={prog.id}
                    className="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-100/60 transition-colors space-y-2"
                  >
                    <div className="flex items-center justify-between">
                      <Badge variant="default" className="font-mono font-bold">
                        {prog.code}
                      </Badge>
                      <Badge variant="outline" className="text-[11px]">
                        {prog.duration_years} Years · {prog.credit_units_required} Cr
                      </Badge>
                    </div>
                    <h4 className="font-bold text-slate-900 text-sm">{prog.title}</h4>
                    <p className="text-xs text-slate-500">{prog.department?.name}</p>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Right Column: Real-Time Audit Trail & Action Queues */}
        <div className="space-y-6">
          <Card>
            <CardHeader className="pb-3">
              <CardTitle className="text-base flex items-center gap-2">
                <Activity className="h-4 w-4 text-mema-teal-800" />
                Immutable Audit Trail
              </CardTitle>
              <CardDescription>Live state mutations recorded via PostgreSQL triggers</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-3 divide-y divide-slate-100">
                {recentLogs.map((log) => (
                  <div key={log.id} className="pt-3 first:pt-0 space-y-1">
                    <div className="flex items-center justify-between text-xs">
                      <span className="font-semibold text-slate-900">{log.actor}</span>
                      <span className="text-[11px] text-slate-400">{log.time}</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <Badge variant="default" className="text-[10px] py-0">
                        {log.module}
                      </Badge>
                      <span className="text-xs font-mono text-mema-teal-800 font-semibold">
                        {log.action}
                      </span>
                    </div>
                    <p className="text-xs text-slate-600">{log.subject}</p>
                  </div>
                ))}
              </div>

              <Link href="/security" className="block pt-2">
                <Button variant="outline" size="sm" className="w-full text-xs">
                  View Full Audit Log
                </Button>
              </Link>
            </CardContent>
          </Card>

          {/* Quick System Status Card */}
          <Card className="bg-gradient-to-br from-mema-teal-900 to-slate-900 text-white p-6 space-y-4 shadow-lg">
            <div className="flex items-center justify-between">
              <span className="text-xs font-bold uppercase tracking-wider text-mema-green-400 flex items-center gap-1.5">
                <Server className="h-4 w-4" /> Cluster Telemetry
              </span>
              <span className="h-2 w-2 rounded-full bg-emerald-400 animate-pulse" />
            </div>

            <div className="space-y-2 text-xs text-mema-teal-100">
              <div className="flex justify-between">
                <span>PostgreSQL 17 Primary:</span>
                <span className="text-emerald-300 font-mono font-semibold">Connected (14 Schemas)</span>
              </div>
              <div className="flex justify-between">
                <span>Redis 7 Cache:</span>
                <span className="text-emerald-300 font-mono font-semibold">0.4ms Latency</span>
              </div>
              <div className="flex justify-between">
                <span>Sanctum Cookie Mode:</span>
                <span className="text-emerald-300 font-mono font-semibold">Strict __Host-</span>
              </div>
            </div>
          </Card>
        </div>
      </div>
    </div>
  );
}
