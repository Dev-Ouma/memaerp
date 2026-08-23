'use client';

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@mema/ui';

export default function LecturerOfferingsPage() {
  return (
    <Card>
      <CardHeader>
        <CardTitle>My Offerings</CardTitle>
        <CardDescription>Assigned course offerings for the active term.</CardDescription>
      </CardHeader>
      <CardContent className="text-sm text-slate-600">
        Waiting on lecturer-scoped offerings API from Codex.
      </CardContent>
    </Card>
  );
}
