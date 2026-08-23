'use client';

import React from 'react';
import { useQuery } from '@tanstack/react-query';
import {
  Badge,
  Button,
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
  courseCredits,
} from '@mema/ui';
import { DataTable } from '@mema/tables';
import { api } from '@mema/api-client';
import { Plus } from 'lucide-react';
import type { Course, CourseOffering } from '@mema/types';

export default function AdminCoursesPage() {
  const coursesQuery = useQuery({
    queryKey: ['courses', 'catalogue'],
    queryFn: () => api.getCourses(),
  });

  const offeringsQuery = useQuery({
    queryKey: ['courses', 'offerings', 'active'],
    queryFn: () => api.getOfferings(),
  });

  return (
    <div className="space-y-8">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 font-heading">
            Courses & Semester Offerings
          </h2>
          <p className="text-sm text-slate-500 mt-1">
            Master course inventory, section allocation, and capacity locks (MOD-01-04)
          </p>
        </div>
        <Button className="bg-mema-teal-800 hover:bg-mema-teal-700 text-white gap-2 self-start sm:self-auto">
          <Plus className="h-4 w-4" /> Add New Course
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Current Semester Offerings</CardTitle>
          <CardDescription>
            Loaded from <code className="text-xs">GET /api/v1/courses/offerings/active</code>
          </CardDescription>
        </CardHeader>
        <CardContent>
          <DataTable<CourseOffering>
            isLoading={offeringsQuery.isLoading}
            isError={offeringsQuery.isError}
            errorMessage={
              offeringsQuery.error instanceof Error ? offeringsQuery.error.message : undefined
            }
            data={offeringsQuery.data ?? []}
            getRowKey={(offering) => offering.id}
            emptyMessage="No active offerings found."
            columns={[
              {
                key: 'course',
                header: 'Course Code & Title',
                cell: (offering) => (
                  <div>
                    <div className="font-bold text-mema-teal-900">{offering.course?.code}</div>
                    <div className="text-xs text-slate-500">{offering.course?.title}</div>
                  </div>
                ),
              },
              {
                key: 'section',
                header: 'Section',
                cell: (offering) => (
                  <Badge variant="outline" className="font-mono">
                    {offering.section_code}
                  </Badge>
                ),
              },
              {
                key: 'campus',
                header: 'Campus / Venue',
                cell: (offering) => (
                  <div>
                    <div className="text-xs font-medium text-slate-800">
                      {offering.campus?.name ?? '—'}
                    </div>
                    <div className="text-[11px] text-slate-400">{offering.room ?? 'TBA'}</div>
                  </div>
                ),
              },
              {
                key: 'schedule',
                header: 'Schedule',
                cell: (offering) => (
                  <span className="text-xs text-slate-600">
                    {offering.schedule_slot ?? 'Not scheduled'}
                  </span>
                ),
              },
              {
                key: 'capacity',
                header: 'Enrolled / Cap',
                className: 'text-center',
                cell: (offering) => (
                  <span className="font-mono font-bold text-slate-900">
                    {offering.enrolled_count} / {offering.capacity}
                  </span>
                ),
              },
              {
                key: 'load',
                header: 'Load Status',
                className: 'text-center',
                cell: (offering) => {
                  const percentage =
                    offering.capacity > 0
                      ? Math.round((offering.enrolled_count / offering.capacity) * 100)
                      : 0;
                  return (
                    <Badge
                      variant={
                        percentage >= 90 ? 'destructive' : percentage >= 75 ? 'warning' : 'success'
                      }
                    >
                      {percentage}% Full
                    </Badge>
                  );
                },
              },
            ]}
          />
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Institutional Course Catalogue</CardTitle>
          <CardDescription>
            Loaded from <code className="text-xs">GET /api/v1/courses/</code>
          </CardDescription>
        </CardHeader>
        <CardContent>
          <DataTable<Course>
            isLoading={coursesQuery.isLoading}
            isError={coursesQuery.isError}
            errorMessage={
              coursesQuery.error instanceof Error ? coursesQuery.error.message : undefined
            }
            data={coursesQuery.data ?? []}
            getRowKey={(course) => course.id}
            emptyMessage="No courses found."
            columns={[
              {
                key: 'code',
                header: 'Code',
                cell: (course) => (
                  <span className="font-mono font-bold text-slate-900">{course.code}</span>
                ),
              },
              {
                key: 'title',
                header: 'Course Title',
                cell: (course) => (
                  <span className="font-medium text-slate-800">{course.title}</span>
                ),
              },
              {
                key: 'credits',
                header: 'Credits',
                className: 'text-center',
                cell: (course) => (
                  <span className="font-mono font-bold">{courseCredits(course)}</span>
                ),
              },
              {
                key: 'lecture',
                header: 'Lecture Hrs',
                className: 'text-center',
                cell: (course) => (
                  <span className="font-mono text-xs">{course.lecture_hours ?? 0} hrs</span>
                ),
              },
              {
                key: 'lab',
                header: 'Practical Hrs',
                className: 'text-center',
                cell: (course) => (
                  <span className="font-mono text-xs">{course.lab_hours ?? course.practical_hours ?? 0} hrs</span>
                ),
              },
              {
                key: 'status',
                header: 'Status',
                className: 'text-center',
                cell: (course) => (
                  <Badge variant="success" dot>
                    {course.is_active ? 'Active' : 'Inactive'}
                  </Badge>
                ),
              },
            ]}
          />
        </CardContent>
      </Card>
    </div>
  );
}
