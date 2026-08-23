'use client';

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@mema/ui';

export default function ManagementDashboardPage() {
  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-2xl font-bold text-slate-900 font-heading">Executive Dashboard</h2>
        <p className="text-sm text-slate-500 mt-1">MOD-05-03 · Institutional analytics and executive BI</p>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>Management portal scaffold ready</CardTitle>
          <CardDescription>
            Auth shell is wired. Apache ECharts dashboards will land once the data warehouse projections are
            available in Phase 5.
          </CardDescription>
        </CardHeader>
        <CardContent className="text-sm text-slate-600">
          Chart package (<code>@mema/charts</code>) is scheduled for a later Phase 0 tranche.
        </CardContent>
      </Card>
    </div>
  );
}
