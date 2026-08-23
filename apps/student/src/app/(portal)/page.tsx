'use client';

import React from 'react';
import Link from 'next/link';
import {
  StatCard,
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  Button,
  Badge,
  Alert,
  AlertTitle,
  AlertDescription,
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
  formatCurrency,
} from '@mema/ui';
import {
  BookOpen,
  Award,
  CreditCard,
  ArrowRight,
  Clock,
  MapPin,
  CheckCircle2,
  Sparkles,
} from 'lucide-react';
import {
  mockCurrentStudent,
  mockInvoices,
  mockOfferings,
  mockRecentMarks,
} from '@mema/api-client';

export default function StudentDashboardPage() {
  const currentInvoice = mockInvoices[0];

  return (
    <div className="space-y-8">
      {/* Top Banner / Attention Alert */}
      <div className="space-y-3">
        <Alert variant="warning" className="border-amber-300/80 bg-amber-50">
          <AlertTitle className="text-amber-900 font-bold flex items-center justify-between">
            <span>Semester 1 Registration Window is Active</span>
            <Badge variant="warning">Closes Sep 15, 2026</Badge>
          </AlertTitle>
          <AlertDescription className="text-amber-800 mt-1 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <span>
              You have not registered for all core Year 3 Semester 1 units. Please complete your registration and fee clearance.
            </span>
            <Link href="/registration">
              <Button size="sm" variant="default" className="bg-amber-800 hover:bg-amber-900 text-white">
                Register Units Now
              </Button>
            </Link>
          </AlertDescription>
        </Alert>
      </div>

      {/* Profile & KPI Stats Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <StatCard
          title="Cumulative GPA"
          value="3.82"
          description="Out of 4.00 max scale"
          icon={<Award className="h-5 w-5" />}
          trend={{
            value: '+0.12',
            isPositive: true,
            label: 'vs last semester',
          }}
        />

        <StatCard
          title="Credits Earned"
          value="76 / 144"
          description="52.8% degree completion"
          icon={<BookOpen className="h-5 w-5" />}
          trend={{
            value: 'On Track',
            isPositive: true,
            label: 'Normal progression',
          }}
        />

        <StatCard
          title="Fee Balance"
          value={formatCurrency(currentInvoice?.balance_amount || 0)}
          description="Due by Sep 30, 2026"
          icon={<CreditCard className="h-5 w-5" />}
          trend={{
            value: 'Partially Paid',
            isNeutral: true,
            label: 'KES 50,000 paid',
          }}
        />

        <StatCard
          title="Academic Standing"
          value="Good Standing"
          description="Dean's Commendation List"
          icon={<Sparkles className="h-5 w-5" />}
          trend={{
            value: 'Top 5%',
            isPositive: true,
            label: 'Cohort Rank #4',
          }}
        />
      </div>

      {/* Quick Actions & Timetable Highlights */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Enrolled Courses / Schedule */}
        <div className="lg:col-span-2 space-y-6">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between pb-4">
              <div>
                <CardTitle>Current Semester Schedule</CardTitle>
                <CardDescription>
                  Semester 1 (Sep - Dec 2026) · Computing Sciences
                </CardDescription>
              </div>
              <Link href="/timetable">
                <Button variant="ghost" size="sm" className="gap-1 text-mema-teal-800">
                  Full Timetable <ArrowRight className="h-4 w-4" />
                </Button>
              </Link>
            </CardHeader>
            <CardContent className="space-y-4">
              {mockOfferings.map((offering) => (
                <div
                  key={offering.id}
                  className="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-slate-100/70 transition-colors gap-3"
                >
                  <div className="space-y-1">
                    <div className="flex items-center gap-2">
                      <span className="font-bold text-mema-teal-900 text-sm">
                        {offering.course?.code}
                      </span>
                      <Badge variant="outline" className="text-[11px]">
                        {offering.course?.credit_units} Credits
                      </Badge>
                      <Badge variant="success" className="text-[11px]">
                        {offering.section_code}
                      </Badge>
                    </div>
                    <p className="text-sm font-medium text-slate-800">
                      {offering.course?.title}
                    </p>
                    <div className="flex items-center gap-4 text-xs text-slate-500 pt-1">
                      <span className="flex items-center gap-1">
                        <Clock className="h-3.5 w-3.5 text-mema-teal-700" />
                        {offering.schedule_slot}
                      </span>
                      <span className="flex items-center gap-1">
                        <MapPin className="h-3.5 w-3.5 text-mema-green-600" />
                        {offering.room}
                      </span>
                    </div>
                  </div>

                  <div className="flex sm:flex-col items-center sm:items-end justify-between gap-1">
                    <span className="text-xs text-slate-500">Attendance</span>
                    <span className="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                      94% Present
                    </span>
                  </div>
                </div>
              ))}
            </CardContent>
          </Card>

          {/* Recent Examination Marks */}
          <Card>
            <CardHeader className="flex flex-row items-center justify-between pb-4">
              <div>
                <CardTitle>Recent Verified Grades</CardTitle>
                <CardDescription>
                  Previous Semester Results (Year 2 Sem 2)
                </CardDescription>
              </div>
              <Link href="/results">
                <Button variant="ghost" size="sm" className="gap-1 text-mema-teal-800">
                  View Transcripts <ArrowRight className="h-4 w-4" />
                </Button>
              </Link>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Course</TableHead>
                    <TableHead className="text-center">CAT (30)</TableHead>
                    <TableHead className="text-center">Exam (70)</TableHead>
                    <TableHead className="text-center">Total (100)</TableHead>
                    <TableHead className="text-center">Grade</TableHead>
                    <TableHead className="text-right">Points</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {mockRecentMarks.map((mark) => (
                    <TableRow key={mark.id}>
                      <TableCell className="font-medium">
                        <div className="font-semibold text-slate-900">
                          {mark.course_enrollment?.course_offering?.course?.code}
                        </div>
                        <div className="text-xs text-slate-500">
                          {mark.course_enrollment?.course_offering?.course?.title}
                        </div>
                      </TableCell>
                      <TableCell className="text-center font-mono">{mark.cat_score}</TableCell>
                      <TableCell className="text-center font-mono">{mark.exam_score}</TableCell>
                      <TableCell className="text-center font-mono font-bold text-slate-900">
                        {mark.total_score}%
                      </TableCell>
                      <TableCell className="text-center">
                        <Badge variant="success" className="font-bold">
                          {mark.grade_letter}
                        </Badge>
                      </TableCell>
                      <TableCell className="text-right font-mono font-bold text-mema-teal-800">
                        {mark.grade_points?.toFixed(2)}
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </div>

        {/* Right Sidebar Widget: Student ID & Fee Quick Pay */}
        <div className="space-y-6">
          {/* Virtual Student ID Card */}
          <Card className="overflow-hidden border-mema-teal-800/30 bg-gradient-to-br from-mema-teal-900 via-mema-teal-800 to-slate-900 text-white shadow-xl">
            <div className="p-6 space-y-4">
              <div className="flex items-center justify-between">
                <div>
                  <h4 className="font-heading font-bold text-sm tracking-wider uppercase text-mema-green-300">
                    Mema University
                  </h4>
                  <p className="text-[10px] text-mema-teal-200 uppercase tracking-widest">
                    Student Identification Card
                  </p>
                </div>
                <div className="h-8 w-8 rounded-full bg-white/10 flex items-center justify-center backdrop-blur-md">
                  <CheckCircle2 className="h-5 w-5 text-mema-green-400" />
                </div>
              </div>

              <div className="flex items-center gap-4 pt-2">
                <div className="h-16 w-16 rounded-xl bg-white/20 p-1 border border-white/30 overflow-hidden shadow-inner flex items-center justify-center font-bold text-2xl text-white">
                  IW
                </div>
                <div className="space-y-0.5">
                  <p className="font-bold text-base text-white">
                    {mockCurrentStudent.person?.first_name} {mockCurrentStudent.person?.last_name}
                  </p>
                  <p className="text-xs font-mono text-mema-teal-200">
                    {mockCurrentStudent.student_number}
                  </p>
                  <p className="text-[11px] text-slate-300">
                    {mockCurrentStudent.programme?.title}
                  </p>
                </div>
              </div>

              <div className="pt-3 border-t border-white/10 flex items-center justify-between text-xs text-mema-teal-200">
                <span>Valid Thru: 08/2027</span>
                <span className="text-emerald-300 font-bold flex items-center gap-1">
                  <span className="h-2 w-2 rounded-full bg-emerald-400" /> Active Student
                </span>
              </div>
            </div>
          </Card>

          {/* Quick Fee Settlement Widget */}
          <Card>
            <CardHeader className="pb-3">
              <CardTitle className="text-base">Tuition Fee Overview</CardTitle>
              <CardDescription>Invoice #{currentInvoice?.invoice_number}</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2">
                <div className="flex justify-between text-xs text-slate-500">
                  <span>Total Invoiced</span>
                  <span className="font-semibold text-slate-800">
                    {formatCurrency(currentInvoice?.total_amount || 0)}
                  </span>
                </div>
                <div className="flex justify-between text-xs text-slate-500">
                  <span>Paid so far</span>
                  <span className="font-semibold text-emerald-600">
                    - {formatCurrency(currentInvoice?.paid_amount || 0)}
                  </span>
                </div>
                <div className="h-px bg-slate-200 my-1" />
                <div className="flex justify-between text-sm font-bold text-slate-900">
                  <span>Pending Balance</span>
                  <span className="text-rose-600">
                    {formatCurrency(currentInvoice?.balance_amount || 0)}
                  </span>
                </div>
              </div>

              <Link href="/finance" className="block">
                <Button className="w-full bg-mema-green-600 hover:bg-mema-green-700 text-white gap-2 font-semibold">
                  <CreditCard className="h-4 w-4" /> Pay via M-Pesa / Card
                </Button>
              </Link>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
