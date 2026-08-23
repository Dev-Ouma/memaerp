'use client';

import React from 'react';
import { useQuery } from '@tanstack/react-query';
import { api } from '@mema/api-client';
import { Badge, Card, CardContent, CardDescription, CardHeader, CardTitle, StatCard } from '@mema/ui';
import { GraduationCap, UserCheck, Users, BookOpen } from 'lucide-react';

export default function ManagementDashboardPage() {
  const students = useQuery({ queryKey: ['students', 'dashboard'], queryFn: () => api.getStudentsDashboard() });
  const admissions = useQuery({ queryKey: ['admissions', 'dashboard'], queryFn: () => api.getAdmissionsDashboard() });
  const courses = useQuery({ queryKey: ['courses', 'dashboard'], queryFn: () => api.getCourseDashboard() });

  return (
    <div className="space-y-8">
      <div>
        <h2 className="text-2xl font-bold text-slate-900 font-heading">Executive Dashboard</h2>
        <p className="text-sm text-slate-500 mt-1">
          Live institutional KPIs from the ERP monolith (Phase 5 BI charts pending)
        </p>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <StatCard
          title="Active students"
          value={String(students.data?.active ?? '—')}
          description={`${students.data?.graduated ?? 0} graduated · ${students.data?.on_leave ?? 0} on leave`}
          icon={<Users className="h-5 w-5" />}
        />
        <StatCard
          title="Applications"
          value={String(admissions.data?.total ?? '—')}
          description={`${admissions.data?.admitted ?? 0} admitted · ${admissions.data?.under_review ?? 0} in review`}
          icon={<UserCheck className="h-5 w-5" />}
        />
        <StatCard
          title="Open sections"
          value={String(courses.data?.open_sections ?? '—')}
          description={`${courses.data?.saturated_sections ?? 0} at capacity`}
          icon={<BookOpen className="h-5 w-5" />}
        />
        <StatCard
          title="Capacity use"
          value={`${courses.data?.capacity_saturation_percent ?? 0}%`}
          description="Average section occupancy"
          icon={<GraduationCap className="h-5 w-5" />}
        />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Student lifecycle</CardTitle>
            <CardDescription>Registry snapshot</CardDescription>
          </CardHeader>
          <CardContent className="space-y-2 text-sm">
            <div className="flex justify-between">
              <span className="text-slate-600">Total enrolled</span>
              <span className="font-semibold">{students.data?.total ?? '—'}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-slate-600">Matriculation queue</span>
              <Badge variant="warning">{students.data?.matriculation_queue ?? 0}</Badge>
            </div>
            <div className="flex justify-between">
              <span className="text-slate-600">Matriculated this month</span>
              <span className="font-semibold">{students.data?.matriculated_this_month ?? 0}</span>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>Admissions pipeline</CardTitle>
            <CardDescription>Current intake cycle</CardDescription>
          </CardHeader>
          <CardContent className="space-y-2 text-sm">
            <div className="flex justify-between">
              <span className="text-slate-600">Submitted</span>
              <span className="font-semibold">{admissions.data?.submitted ?? 0}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-slate-600">Fee paid</span>
              <span className="font-semibold">{admissions.data?.fee_paid ?? 0}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-slate-600">Prospects</span>
              <span className="font-semibold">{admissions.data?.prospects ?? 0}</span>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
