'use client';

import React from 'react';
import Link from 'next/link';
import { useQuery } from '@tanstack/react-query';
import { api } from '@mema/api-client';
import { Badge, Button, Card, CardContent, CardDescription, CardHeader, CardTitle, StatCard } from '@mema/ui';
import { ArrowRight, ClipboardCheck, Inbox, Users } from 'lucide-react';
import { staffModuleLinks } from '@/config/portal-nav';
import { ModuleHub } from '@/components/module-hub';

export default function StaffDashboardPage() {
  const user = useQuery({ queryKey: ['auth', 'me'], queryFn: () => api.getCurrentUser() });
  const queue = useQuery({
    queryKey: ['graduation', 'clearance-queue'],
    queryFn: () => api.getGraduationClearanceQueue(),
    enabled: user.data?.permissions.includes('graduation.clearance.clear') ?? false,
  });

  const pendingCount = queue.data?.length ?? 0;

  return (
    <div className="space-y-8">
      <div>
        <h2 className="text-2xl font-bold text-slate-900 font-heading">Staff Dashboard</h2>
        <p className="text-sm text-slate-500 mt-1">
          Welcome{user.data?.person?.given_name ? `, ${user.data.person.given_name}` : ''} · internal clearance & services
        </p>
      </div>

      <ModuleHub items={staffModuleLinks} />

      <div className="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <StatCard
          title="Pending clearances"
          value={String(pendingCount)}
          description="Awaiting departmental sign-off"
          icon={<Inbox className="h-5 w-5" />}
        />
        <StatCard
          title="Your role"
          value={user.data?.roles?.[0]?.role_name ?? 'Staff'}
          description="Scoped institutional access"
          icon={<Users className="h-5 w-5" />}
        />
        <StatCard
          title="Clearance module"
          value="Active"
          description="MOD-02-09 paperless clearance"
          icon={<ClipboardCheck className="h-5 w-5" />}
        />
      </div>

      <Card>
        <CardHeader className="flex flex-row items-center justify-between">
          <div>
            <CardTitle>Clearance queue preview</CardTitle>
            <CardDescription>Latest pending graduation checkpoints</CardDescription>
          </div>
          <Link href="/requests">
            <Button variant="ghost" size="sm" className="gap-1 text-mema-teal-800">
              Open queue <ArrowRight className="h-4 w-4" />
            </Button>
          </Link>
        </CardHeader>
        <CardContent className="space-y-3">
          {(queue.data ?? []).slice(0, 5).map((item) => {
            const student = item.application as { student?: { student_number?: string; person?: { given_name?: string; family_name?: string } } } | undefined;
            const person = student?.student?.person;
            const name = person ? `${person.given_name ?? ''} ${person.family_name ?? ''}`.trim() : '—';
            return (
              <div key={String(item.id)} className="flex items-center justify-between text-sm border-b border-slate-100 pb-2 last:border-0">
                <div>
                  <div className="font-medium">{name}</div>
                  <div className="text-xs text-slate-500">
                    {String(item.department_name ?? item.department_code)} · {student?.student?.student_number}
                  </div>
                </div>
                <Badge variant="warning">PENDING</Badge>
              </div>
            );
          })}
          {pendingCount === 0 && (
            <p className="text-sm text-slate-500">
              {queue.isLoading ? 'Loading queue…' : 'No pending clearance items.'}
            </p>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
