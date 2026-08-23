'use client';

import React, { useEffect, useMemo, useState } from 'react';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import {
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
} from '@mema/ui';
import { BookOpen, Clock, MapPin, Plus, Trash2 } from 'lucide-react';
import { ApiError, api } from '@mema/api-client';
import type { CourseOffering } from '@mema/types';

export default function CourseRegistrationPage() {
  const queryClient = useQueryClient();
  const dashboard = useQuery({ queryKey: ['portal', 'dashboard'], queryFn: () => api.getPortalDashboard() });

  const registration = (dashboard.data?.registration ?? {}) as {
    term?: { id?: string; name?: string; code?: string };
    registered?: boolean;
    course_count?: number;
  };
  const finance = (dashboard.data?.finance ?? {}) as { registration_cleared?: boolean; payment_percentage?: number };
  const termId = registration.term?.id;

  const available = useQuery({
    queryKey: ['enrollment', 'available', termId],
    queryFn: () => api.getAvailableCourses(termId!),
    enabled: Boolean(termId),
  });

  const enrolled = useQuery({
    queryKey: ['enrollment', 'my-courses', termId],
    queryFn: () => api.getMyCourses(termId),
    enabled: Boolean(termId),
  });

  const [selectedOfferingIds, setSelectedOfferingIds] = useState<string[]>([]);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [registeredSuccess, setRegisteredSuccess] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (enrolled.data?.length) {
      setSelectedOfferingIds(enrolled.data.map((row) => row.course_offering_id));
    }
  }, [enrolled.data]);

  const offerings = available.data ?? [];
  const selectedOfferings = offerings.filter((offering) => selectedOfferingIds.includes(offering.id));

  const totalCredits = useMemo(
    () => selectedOfferings.reduce((acc, curr) => acc + (curr.course?.credit_units ?? 0), 0),
    [selectedOfferings],
  );

  const toggleOffering = (id: string) => {
    setSelectedOfferingIds((current) =>
      current.includes(id) ? current.filter((item) => item !== id) : [...current, id],
    );
  };

  const handleRegister = async () => {
    if (!termId) return;
    setIsSubmitting(true);
    setError(null);
    setRegisteredSuccess(false);
    try {
      await api.registerCourses({ term_id: termId, offering_ids: selectedOfferingIds });
      setRegisteredSuccess(true);
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: ['enrollment'] }),
        queryClient.invalidateQueries({ queryKey: ['portal', 'dashboard'] }),
      ]);
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Registration failed.');
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="space-y-8">
      <div>
        <h2 className="text-2xl font-bold text-slate-900 font-heading">Online Course Registration</h2>
        <p className="text-sm text-slate-500 mt-1">
          {registration.term?.name ?? 'Current term'} · Live offerings from{' '}
          <code className="text-xs">GET /api/v1/enrollment/available-courses</code>
        </p>
      </div>

      {error && (
        <Alert variant="destructive">
          <AlertTitle>Registration failed</AlertTitle>
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      )}

      {registeredSuccess && (
        <Alert variant="success" className="border-emerald-300 bg-emerald-50">
          <AlertTitle className="text-emerald-900 font-bold">Registration Confirmed & Enrolled!</AlertTitle>
          <AlertDescription className="text-emerald-800">
            You registered for {selectedOfferings.length} courses ({totalCredits} credit units).
          </AlertDescription>
        </Alert>
      )}

      <div className="grid grid-cols-1 md:grid-cols-3 gap-5">
        <Card className="p-5 bg-white border-l-4 border-l-mema-teal-800">
          <p className="text-xs text-slate-500 font-medium">Selected Credit Load</p>
          <h3 className="text-2xl font-bold text-mema-teal-900 mt-1">
            {totalCredits} <span className="text-sm font-normal text-slate-500">/ 24 max</span>
          </h3>
          <Badge variant={totalCredits >= 12 && totalCredits <= 24 ? 'success' : 'warning'} className="mt-2">
            {totalCredits >= 12 ? 'Valid Load' : 'Under Minimum'}
          </Badge>
        </Card>

        <Card className="p-5 bg-white border-l-4 border-l-mema-green-600">
          <p className="text-xs text-slate-500 font-medium">Fee Clearance Gate</p>
          <h3 className="text-2xl font-bold text-mema-green-700 mt-1">
            {finance.registration_cleared ? 'Cleared' : 'Pending'}
          </h3>
          <p className="text-xs text-slate-500 mt-2">
            {Number(finance.payment_percentage ?? 0).toFixed(0)}% of fees paid
          </p>
        </Card>

        <Card className="p-5 bg-white border-l-4 border-l-amber-500">
          <p className="text-xs text-slate-500 font-medium">Current Enrolment</p>
          <h3 className="text-2xl font-bold text-slate-900 mt-1">{registration.course_count ?? 0} courses</h3>
          <Badge variant={registration.registered ? 'success' : 'outline'} className="mt-2">
            {registration.registered ? 'Term registered' : 'Not registered'}
          </Badge>
        </Card>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2 space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Available Departmental Offerings</CardTitle>
              <CardDescription>Select units for {registration.term?.code ?? 'this term'}</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              {available.isLoading ? (
                <p className="text-sm text-slate-500 py-8 text-center">Loading offerings…</p>
              ) : offerings.length === 0 ? (
                <p className="text-sm text-slate-500 py-8 text-center">No offerings available for registration.</p>
              ) : (
                offerings.map((offering) => (
                  <OfferingRow
                    key={offering.id}
                    offering={offering}
                    isSelected={selectedOfferingIds.includes(offering.id)}
                    onToggle={() => toggleOffering(offering.id)}
                  />
                ))
              )}
            </CardContent>
          </Card>
        </div>

        <div>
          <Card className="sticky top-24">
            <CardHeader className="pb-3">
              <CardTitle className="text-base flex items-center justify-between">
                <span>Selected Summary</span>
                <Badge variant="outline">{selectedOfferings.length} Courses</Badge>
              </CardTitle>
              <CardDescription>Review before finalizing registration</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              {selectedOfferings.length === 0 ? (
                <div className="p-6 text-center text-slate-400 border border-dashed rounded-xl">
                  <BookOpen className="h-8 w-8 mx-auto mb-2 opacity-50" />
                  <p className="text-xs">No courses selected yet.</p>
                </div>
              ) : (
                <div className="space-y-2 divide-y divide-slate-100">
                  {selectedOfferings.map((item) => (
                    <div key={item.id} className="pt-2 flex justify-between items-center text-xs">
                      <div>
                        <span className="font-bold text-slate-900 block">{item.course?.code}</span>
                        <span className="text-slate-500">{item.section_code}</span>
                      </div>
                      <span className="font-semibold text-slate-800">{item.course?.credit_units} Cr</span>
                    </div>
                  ))}
                </div>
              )}

              <Button
                onClick={handleRegister}
                isLoading={isSubmitting}
                disabled={!finance.registration_cleared || totalCredits < 12 || selectedOfferings.length === 0}
                className="w-full bg-mema-teal-800 hover:bg-mema-teal-700 text-white font-semibold py-2.5"
              >
                Submit Registration
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}

function OfferingRow({
  offering,
  isSelected,
  onToggle,
}: {
  offering: CourseOffering;
  isSelected: boolean;
  onToggle: () => void;
}) {
  const capacityPercent = offering.capacity
    ? Math.round(((offering.enrolled_count ?? 0) / offering.capacity) * 100)
    : 0;

  return (
    <div
      className={`p-4 rounded-xl border transition-all ${
        isSelected
          ? 'border-mema-teal-700 bg-mema-teal-50/40 ring-1 ring-mema-teal-700'
          : 'border-slate-200 bg-white hover:border-slate-300'
      }`}
    >
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div className="space-y-1">
          <div className="flex items-center gap-2 flex-wrap">
            <span className="font-bold text-mema-teal-900">{offering.course?.code}</span>
            <Badge variant="default" className="text-[11px]">{offering.course?.credit_units} Credits</Badge>
            <Badge variant="outline" className="text-[11px]">{offering.section_code}</Badge>
          </div>
          <h4 className="font-semibold text-slate-900 text-sm">{offering.course?.title}</h4>
          <div className="flex flex-wrap items-center gap-4 text-xs text-slate-500 pt-1">
            <span className="flex items-center gap-1">
              <Clock className="h-3.5 w-3.5 text-mema-teal-700" />
              {offering.schedule_slot ?? 'Schedule TBA'}
            </span>
            <span className="flex items-center gap-1">
              <MapPin className="h-3.5 w-3.5 text-mema-green-600" />
              {offering.room ?? offering.campus?.name ?? 'TBA'}
            </span>
          </div>
        </div>

        <div className="flex sm:flex-col items-center sm:items-end justify-between gap-2">
          <div className="text-right">
            <span className="text-[11px] text-slate-500">
              Capacity: {offering.enrolled_count ?? 0}/{offering.capacity ?? '—'}
            </span>
            <div className="w-24 h-1.5 bg-slate-100 rounded-full overflow-hidden mt-0.5">
              <div className="h-full bg-mema-teal-600 rounded-full" style={{ width: `${capacityPercent}%` }} />
            </div>
          </div>
          <Button size="sm" variant={isSelected ? 'destructive' : 'default'} onClick={onToggle} className="h-8 text-xs gap-1">
            {isSelected ? (<><Trash2 className="h-3.5 w-3.5" /> Remove</>) : (<><Plus className="h-3.5 w-3.5" /> Add Course</>)}
          </Button>
        </div>
      </div>
    </div>
  );
}
