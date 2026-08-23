'use client';

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@mema/ui';

export default function LecturerDashboardPage() {
  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-2xl font-bold text-slate-900 font-heading">Teaching Dashboard</h2>
        <p className="text-sm text-slate-500 mt-1">MOD-02-11 · Lecturer and staff teaching workflows</p>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>Portal scaffold ready</CardTitle>
          <CardDescription>
            Auth, navigation, and session handling are wired. Marks entry and offering views will connect
            once examination API contracts are published.
          </CardDescription>
        </CardHeader>
        <CardContent className="text-sm text-slate-600">
          Next integration target: <code>GET /api/v1/exams/marks-sheet/{'{offeringId}'}</code>
        </CardContent>
      </Card>
    </div>
  );
}
