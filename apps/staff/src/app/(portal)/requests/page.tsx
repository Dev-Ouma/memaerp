'use client';

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@mema/ui';

export default function StaffRequestsPage() {
  return (
    <Card>
      <CardHeader>
        <CardTitle>Requests & Clearance</CardTitle>
        <CardDescription>Paperless requests and departmental clearance workflows.</CardDescription>
      </CardHeader>
      <CardContent className="text-sm text-slate-600">
        Waiting on MOD-02-09 request and clearance APIs.
      </CardContent>
    </Card>
  );
}
