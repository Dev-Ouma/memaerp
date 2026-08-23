'use client';

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@mema/ui';

export default function LecturerMarksPage() {
  return (
    <Card>
      <CardHeader>
        <CardTitle>Marks Entry</CardTitle>
        <CardDescription>Continuous assessment and examination marks capture.</CardDescription>
      </CardHeader>
      <CardContent className="text-sm text-slate-600">
        Waiting on marks entry mutation APIs and moderation workflow contracts.
      </CardContent>
    </Card>
  );
}
