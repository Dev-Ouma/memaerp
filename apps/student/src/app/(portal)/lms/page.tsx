'use client';

import React, { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { api, ApiError } from '@mema/api-client';
import {
  Alert,
  AlertDescription,
  AlertTitle,
  Badge,
  Button,
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
  MemaLoaderInline,
} from '@mema/ui';
import { ExternalLink, GraduationCap, MonitorPlay } from 'lucide-react';

const messageFrom = (reason: unknown) =>
  reason instanceof ApiError ? reason.message : 'Unable to launch Moodle.';

export default function StudentLmsPage() {
  const [launching, setLaunching] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const courses = useQuery({ queryKey: ['enrollment', 'my-courses'], queryFn: () => api.getMyCourses() });

  async function launchMoodle(path = '/') {
    setLaunching(true);
    setError(null);
    try {
      const url = await api.getLmsLaunchUrl(path);
      window.open(url, '_blank', 'noopener,noreferrer');
    } catch (reason) {
      setError(messageFrom(reason));
    } finally {
      setLaunching(false);
    }
  }

  return (
    <div className="space-y-8">
      <div>
        <h2 className="text-2xl font-bold text-slate-900 font-heading">E-Learning (Moodle)</h2>
        <p className="text-sm text-slate-500 mt-1">
          Launch Moodle for course materials, assignments, and forums (MOD-02-01)
        </p>
      </div>

      {error && (
        <Alert variant="destructive">
          <AlertTitle>Launch failed</AlertTitle>
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      )}

      <Card className="bg-gradient-to-br from-mema-teal-900 to-slate-900 text-white">
        <CardHeader>
          <CardTitle className="flex items-center gap-2 text-white">
            <MonitorPlay className="h-5 w-5 text-mema-green-400" />
            Moodle SSO Launch
          </CardTitle>
          <CardDescription className="text-mema-teal-100">
            Single sign-on into Moodle with your student portal credentials
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Button
            onClick={() => launchMoodle('/')}
            disabled={launching}
            className="gap-2 bg-mema-green-600 hover:bg-mema-green-500"
          >
            {launching ? <MemaLoaderInline size={40} /> : <ExternalLink className="h-4 w-4" />}
            Open Moodle Dashboard
          </Button>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <GraduationCap className="h-5 w-5 text-mema-teal-700" />
            My enrolled courses
          </CardTitle>
          <CardDescription>Quick launch into Moodle course contexts when synced</CardDescription>
        </CardHeader>
        <CardContent className="space-y-3">
          {(courses.data ?? []).map((enrollment) => {
            const offering = enrollment.course_offering;
            const code = offering?.course?.code ?? 'Course';
            const title = offering?.course?.title ?? offering?.section_code ?? '—';
            return (
              <div
                key={enrollment.id}
                className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border border-slate-200 p-4"
              >
                <div>
                  <div className="font-semibold text-slate-900">{code}</div>
                  <div className="text-xs text-slate-500">{title} · {offering?.section_code}</div>
                </div>
                <div className="flex items-center gap-2">
                  <Badge variant="outline">{enrollment.status}</Badge>
                  <Button
                    size="sm"
                    variant="outline"
                    disabled={launching}
                    onClick={() => launchMoodle(`/course/view.php?id=${offering?.id ?? ''}`)}
                    className="gap-1"
                  >
                    <ExternalLink className="h-3 w-3" /> Open
                  </Button>
                </div>
              </div>
            );
          })}
          {(courses.data ?? []).length === 0 && (
            <p className="text-sm text-slate-500 py-6 text-center">
              {courses.isLoading ? 'Loading courses…' : 'Register for courses to see Moodle links.'}
            </p>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
