'use client';

import React, { useMemo } from 'react';
import { useQuery } from '@tanstack/react-query';
import {
  Card,
  CardHeader,
  CardTitle,
  CardContent,
  Button,
  Badge,
  Alert,
  AlertTitle,
  AlertDescription,
} from '@mema/ui';
import {
  Calendar as CalendarIcon,
  Clock,
  MapPin,
  User as UserIcon,
  Download,
} from 'lucide-react';
import { api } from '@mema/api-client';

const DAY_NAMES = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

function formatTime(value: unknown): string {
  if (!value) return '—';
  const date = new Date(String(value));
  if (Number.isNaN(date.getTime())) return String(value);
  return date.toLocaleTimeString('en-KE', { hour: '2-digit', minute: '2-digit' });
}

export default function StudentTimetablePage() {
  const dashboard = useQuery({ queryKey: ['portal', 'dashboard'], queryFn: () => api.getPortalDashboard() });
  const termId = (dashboard.data?.registration as { term?: { id?: string } } | undefined)?.term?.id;

  const schedule = useQuery({
    queryKey: ['timetable', 'my-schedule', termId],
    queryFn: () => api.getTimetableSchedule(termId),
    enabled: Boolean(termId),
  });

  const slotsByDay = useMemo(() => {
    const grouped = new Map<string, Record<string, unknown>[]>();
    for (const day of DAY_NAMES.slice(1, 6)) grouped.set(day, []);

    for (const slot of schedule.data ?? []) {
      const startsAt = slot.starts_at ? new Date(String(slot.starts_at)) : null;
      const day = (startsAt && DAY_NAMES[startsAt.getDay()]) || 'Monday';
      if (!grouped.has(day)) grouped.set(day, []);
      grouped.get(day)!.push(slot);
    }

    return grouped;
  }, [schedule.data]);

  return (
    <div className="space-y-8">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 font-heading">
            Class & Examination Timetable
          </h2>
          <p className="text-sm text-slate-500 mt-1">
            Live teaching slots from <code className="text-xs">GET /api/v1/timetable/my-schedule</code>
          </p>
        </div>
        <Button asChild variant="outline" className="gap-2 self-start sm:self-auto">
          <a href={api.getTimetableExportUrl(termId)} download="my-timetable.ics">
            <Download className="h-4 w-4" /> Export iCal
          </a>
        </Button>
      </div>

      {schedule.isError && (
        <Alert variant="destructive">
          <AlertTitle>Unable to load timetable</AlertTitle>
          <AlertDescription>
            {schedule.error instanceof Error ? schedule.error.message : 'Unexpected error'}
          </AlertDescription>
        </Alert>
      )}

      <div className="space-y-6">
        {['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'].map((day) => {
          const daySlots = slotsByDay.get(day) ?? [];

          return (
            <Card key={day}>
              <CardHeader className="py-3 bg-slate-50 border-b border-slate-100 flex flex-row items-center justify-between">
                <CardTitle className="text-base font-bold text-slate-900 flex items-center gap-2">
                  <CalendarIcon className="h-4 w-4 text-mema-teal-700" />
                  {day}
                </CardTitle>
                <Badge variant="outline" className="text-xs">
                  {daySlots.length} Scheduled Sessions
                </Badge>
              </CardHeader>
              <CardContent className="p-4 space-y-3">
                {schedule.isLoading ? (
                  <p className="text-xs text-slate-400 py-2">Loading schedule…</p>
                ) : daySlots.length === 0 ? (
                  <p className="text-xs text-slate-400 py-2">No lectures scheduled for this day.</p>
                ) : (
                  daySlots.map((slot) => {
                    const offering = slot.course_offering as Record<string, unknown> | undefined;
                    const course = offering?.course as Record<string, unknown> | undefined;
                    const room = slot.room as Record<string, unknown> | undefined;
                    const lecturer = slot.lecturer as Record<string, unknown> | undefined;

                    return (
                      <div
                        key={String(slot.id)}
                        className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl border border-slate-200 bg-white"
                      >
                        <div>
                          <div className="flex items-center gap-2">
                            <span className="font-bold text-mema-teal-900">{String(course?.code ?? '—')}</span>
                            <Badge variant="outline" className="text-[11px]">{String(offering?.section_code ?? '')}</Badge>
                          </div>
                          <p className="text-sm font-semibold text-slate-900 mt-1">{String(course?.title ?? 'Course')}</p>
                          <div className="flex flex-wrap items-center gap-4 text-xs text-slate-500 pt-2">
                            <span className="flex items-center gap-1">
                              <Clock className="h-3.5 w-3.5 text-mema-teal-700" />
                              {formatTime(slot.starts_at)} – {formatTime(slot.ends_at)}
                            </span>
                            <span className="flex items-center gap-1">
                              <MapPin className="h-3.5 w-3.5 text-mema-green-600" />
                              {String(room?.name ?? room?.code ?? 'TBA')}
                            </span>
                            <span className="flex items-center gap-1">
                              <UserIcon className="h-3.5 w-3.5" />
                              {String(lecturer?.email ?? 'Lecturer TBA')}
                            </span>
                          </div>
                        </div>
                      </div>
                    );
                  })
                )}
              </CardContent>
            </Card>
          );
        })}
      </div>
    </div>
  );
}
