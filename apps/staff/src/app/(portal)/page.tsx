'use client';

import { Card, CardDescription, CardHeader, CardTitle } from '@mema/ui';

export default function StaffDashboardPage() {
  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-2xl font-bold text-slate-900 font-heading">Staff Dashboard</h2>
        <p className="text-sm text-slate-500 mt-1">MOD-02-11 · Internal staff services portal</p>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>Portal scaffold ready</CardTitle>
          <CardDescription>
            Authentication and navigation are in place. Department workflows will be added module by module.
          </CardDescription>
        </CardHeader>
      </Card>
    </div>
  );
}
