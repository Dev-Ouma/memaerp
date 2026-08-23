'use client';

import React from 'react';
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  Button,
  Badge,
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
} from '@mema/ui';
import { Plus } from 'lucide-react';
import { mockCourses, mockOfferings } from '@mema/api-client';

export default function AdminCoursesPage() {
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
        <div className="flex items-center gap-2 self-start sm:self-auto">
          <Button className="bg-mema-teal-800 hover:bg-mema-teal-700 text-white gap-2">
            <Plus className="h-4 w-4" /> Add New Course
          </Button>
        </div>
      </div>

      {/* Active Semester Offerings */}
      <Card>
        <CardHeader>
          <CardTitle>Current Semester Offerings & Concurrency Locks</CardTitle>
          <CardDescription>
            Section capacity with Redis distributed locks preventing oversubscription.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Course Code & Title</TableHead>
                <TableHead>Section</TableHead>
                <TableHead>Campus / Venue</TableHead>
                <TableHead>Schedule</TableHead>
                <TableHead className="text-center">Enrolled / Cap</TableHead>
                <TableHead className="text-center">Load Status</TableHead>
                <TableHead className="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {mockOfferings.map((offering) => {
                const percentage = Math.round(
                  (offering.enrolled_count / offering.capacity) * 100
                );
                return (
                  <TableRow key={offering.id}>
                    <TableCell>
                      <div className="font-bold text-mema-teal-900">
                        {offering.course?.code}
                      </div>
                      <div className="text-xs text-slate-500">
                        {offering.course?.title}
                      </div>
                    </TableCell>
                    <TableCell>
                      <Badge variant="outline" className="font-mono">
                        {offering.section_code}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <div className="text-xs font-medium text-slate-800">
                        {offering.campus?.name}
                      </div>
                      <div className="text-[11px] text-slate-400">
                        {offering.room}
                      </div>
                    </TableCell>
                    <TableCell className="text-xs text-slate-600">
                      {offering.schedule_slot}
                    </TableCell>
                    <TableCell className="text-center font-mono font-bold text-slate-900">
                      {offering.enrolled_count} / {offering.capacity}
                    </TableCell>
                    <TableCell className="text-center">
                      <Badge
                        variant={
                          percentage >= 90
                            ? 'destructive'
                            : percentage >= 75
                            ? 'warning'
                            : 'success'
                        }
                      >
                        {percentage}% Full
                      </Badge>
                    </TableCell>
                    <TableCell className="text-right">
                      <Button size="sm" variant="outline" className="h-8 text-xs">
                        Edit Section
                      </Button>
                    </TableCell>
                  </TableRow>
                );
              })}
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      {/* Master Course Inventory */}
      <Card>
        <CardHeader>
          <CardTitle>Institutional Course Catalogue</CardTitle>
          <CardDescription>Master syllabus & credit unit configurations</CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Code</TableHead>
                <TableHead>Course Title</TableHead>
                <TableHead className="text-center">Credits</TableHead>
                <TableHead className="text-center">Lecture Hrs</TableHead>
                <TableHead className="text-center">Practical Hrs</TableHead>
                <TableHead className="text-center">Status</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {mockCourses.map((c) => (
                <TableRow key={c.id}>
                  <TableCell className="font-mono font-bold text-slate-900">
                    {c.code}
                  </TableCell>
                  <TableCell className="font-medium text-slate-800">
                    {c.title}
                  </TableCell>
                  <TableCell className="text-center font-mono font-bold">
                    {c.credit_units}
                  </TableCell>
                  <TableCell className="text-center font-mono text-xs">
                    {c.lecture_hours} hrs
                  </TableCell>
                  <TableCell className="text-center font-mono text-xs">
                    {c.practical_hours} hrs
                  </TableCell>
                  <TableCell className="text-center">
                    <Badge variant="success" dot>
                      Active
                    </Badge>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  );
}
