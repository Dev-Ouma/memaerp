'use client';

import React, { useMemo } from 'react';
import Link from 'next/link';
import { useQuery } from '@tanstack/react-query';
import { api } from '@mema/api-client';
import type { CourseOffering } from '@mema/types';
import {
  Badge,
  Button,
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@mema/ui';
import { ArrowRight, BookOpen, ClipboardList, QrCode, Users } from 'lucide-react';

function occupancyVariant(enrolled: number = 0, capacity: number = 0) {
  const pct = capacity > 0 ? (enrolled / capacity) * 100 : 0;
  if (pct >= 90) return 'destructive' as const;
  if (pct >= 75) return 'warning' as const;
  return 'success' as const;
}

export default function LecturerOfferingsPage() {
  const user = useQuery({ queryKey: ['auth', 'me'], queryFn: () => api.getCurrentUser() });
  const offerings = useQuery({ queryKey: ['courses', 'offerings'], queryFn: () => api.getOfferings() });

  const myOfferings = useMemo(() => {
    const uid = user.data?.id;
    const all = offerings.data ?? [];
    if (!uid) return all;
    return all.filter((o) => o.lecturer_id === uid);
  }, [offerings.data, user.data?.id]);

  return (
    <div className="space-y-8">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 font-heading">My Offerings</h2>
          <p className="text-sm text-slate-500 mt-1">
            Course sections assigned to you · scoped via lecturer identity
          </p>
        </div>
        <Badge variant="outline">{myOfferings.length} section(s)</Badge>
      </div>

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <BookOpen className="h-5 w-5 text-mema-teal-700" />
            Assigned Sections
          </CardTitle>
          <CardDescription>Live data from GET /api/v1/courses/offerings</CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Course</TableHead>
                <TableHead>Section</TableHead>
                <TableHead>Campus</TableHead>
                <TableHead className="text-center">Enrolled</TableHead>
                <TableHead>Status</TableHead>
                <TableHead className="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {myOfferings.map((offering: CourseOffering) => (
                <TableRow key={offering.id}>
                  <TableCell>
                    <div className="font-bold text-slate-900">{offering.course?.code ?? '—'}</div>
                    <div className="text-xs text-slate-500">{offering.course?.title}</div>
                  </TableCell>
                  <TableCell className="font-mono text-sm">{offering.section_code}</TableCell>
                  <TableCell className="text-sm">{offering.campus?.name ?? '—'}</TableCell>
                  <TableCell className="text-center">
                    <Badge variant={occupancyVariant(offering.enrolled_count, offering.max_capacity)}>
                      <Users className="h-3 w-3 mr-1 inline" />
                      {offering.enrolled_count}/{offering.max_capacity}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <Badge variant="outline">{offering.status}</Badge>
                  </TableCell>
                  <TableCell className="text-right space-x-2">
                    <Link href={`/marks?offering=${offering.id}`}>
                      <Button size="sm" variant="outline" className="gap-1">
                        <ClipboardList className="h-3 w-3" /> Marks
                      </Button>
                    </Link>
                    <Link href="/attendance">
                      <Button size="sm" variant="secondary" className="gap-1">
                        <QrCode className="h-3 w-3" /> Attendance
                      </Button>
                    </Link>
                  </TableCell>
                </TableRow>
              ))}
              {myOfferings.length === 0 && (
                <TableRow>
                  <TableCell colSpan={6} className="text-center text-slate-500 py-8">
                    {offerings.isLoading ? 'Loading offerings…' : 'No sections assigned to your account yet.'}
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      {myOfferings.length > 0 && (
        <div className="flex justify-end">
          <Link href="/">
            <Button variant="ghost" className="gap-2">
              Back to dashboard <ArrowRight className="h-4 w-4" />
            </Button>
          </Link>
        </div>
      )}
    </div>
  );
}
