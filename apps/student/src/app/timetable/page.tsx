'use client';

import React from 'react';
import {
  Card,
  CardHeader,
  CardTitle,
  CardContent,
  Button,
  Badge,
} from '@mema/ui';
import {
  Calendar as CalendarIcon,
  Clock,
  MapPin,
  User as UserIcon,
  Download,
} from 'lucide-react';
import { mockOfferings } from '@mema/api-client';

export default function StudentTimetablePage() {
  const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

  return (
    <div className="space-y-8">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 font-heading">
            Class & Examination Timetable
          </h2>
          <p className="text-sm text-slate-500 mt-1">
            Weekly teaching schedule, lecture halls and laboratory sessions
          </p>
        </div>
        <Button variant="outline" className="gap-2 self-start sm:self-auto">
          <Download className="h-4 w-4" /> Export iCal / Google Calendar
        </Button>
      </div>

      <div className="space-y-6">
        {days.map((day, idx) => {
          // Determine day's lectures
          const dayOfferings = mockOfferings.filter((_, i) => (i + idx) % 2 === 0);

          return (
            <Card key={day}>
              <CardHeader className="py-3 bg-slate-50 border-b border-slate-100 flex flex-row items-center justify-between">
                <CardTitle className="text-base font-bold text-slate-900 flex items-center gap-2">
                  <CalendarIcon className="h-4 w-4 text-mema-teal-700" />
                  {day}
                </CardTitle>
                <Badge variant="outline" className="text-xs">
                  {dayOfferings.length} Scheduled Sessions
                </Badge>
              </CardHeader>
              <CardContent className="p-4 space-y-3">
                {dayOfferings.length === 0 ? (
                  <p className="text-xs text-slate-400 py-2">No lectures scheduled for this day.</p>
                ) : (
                  dayOfferings.map((offering) => (
                    <div
                      key={offering.id}
                      className="p-4 rounded-xl border border-slate-200/80 bg-white hover:border-mema-teal-500 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs"
                    >
                      <div className="space-y-1">
                        <div className="flex items-center gap-2">
                          <span className="font-bold text-mema-teal-900">
                            {offering.course?.code}
                          </span>
                          <span className="text-slate-400">·</span>
                          <span className="text-sm font-semibold text-slate-800">
                            {offering.course?.title}
                          </span>
                        </div>
                        <div className="flex flex-wrap items-center gap-4 text-xs text-slate-500 pt-1">
                          <span className="flex items-center gap-1 font-medium text-slate-700">
                            <Clock className="h-3.5 w-3.5 text-mema-teal-700" />
                            {offering.schedule_slot}
                          </span>
                          <span className="flex items-center gap-1 font-medium text-emerald-700">
                            <MapPin className="h-3.5 w-3.5 text-mema-green-600" />
                            {offering.room}
                          </span>
                          <span className="flex items-center gap-1">
                            <UserIcon className="h-3.5 w-3.5 text-slate-400" />
                            Dr. P. Kamau (Lecturer)
                          </span>
                        </div>
                      </div>

                      <div className="flex items-center gap-2 self-start sm:self-auto">
                        <Badge variant="success">{offering.section_code}</Badge>
                      </div>
                    </div>
                  ))
                )}
              </CardContent>
            </Card>
          );
        })}
      </div>
    </div>
  );
}
