'use client';

import React, { useState } from 'react';
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
import {
  BookOpen,
  Clock,
  MapPin,
  Plus,
  Trash2,
} from 'lucide-react';
import { mockOfferings } from '@mema/api-client';

export default function CourseRegistrationPage() {
  const [selectedOfferingIds, setSelectedOfferingIds] = useState<string[]>([
    'off-01',
    'off-02',
  ]);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [registeredSuccess, setRegisteredSuccess] = useState(false);

  const toggleOffering = (id: string) => {
    if (selectedOfferingIds.includes(id)) {
      setSelectedOfferingIds(selectedOfferingIds.filter((item) => item !== id));
    } else {
      setSelectedOfferingIds([...selectedOfferingIds, id]);
    }
  };

  const selectedOfferings = mockOfferings.filter((o) =>
    selectedOfferingIds.includes(o.id)
  );

  const totalCredits = selectedOfferings.reduce(
    (acc, curr) => acc + (curr.course?.credit_units || 0),
    0
  );

  const handleRegister = () => {
    setIsSubmitting(true);
    setTimeout(() => {
      setIsSubmitting(false);
      setRegisteredSuccess(true);
    }, 1200);
  };

  return (
    <div className="space-y-8">
      {/* Header */}
      <div>
        <h2 className="text-2xl font-bold text-slate-900 font-heading">
          Online Course Registration
        </h2>
        <p className="text-sm text-slate-500 mt-1">
          Academic Year 2026/2027 · Semester 1 · Regular Program
        </p>
      </div>

      {registeredSuccess && (
        <Alert variant="success" className="border-emerald-300 bg-emerald-50">
          <AlertTitle className="text-emerald-900 font-bold">
            Registration Confirmed & Enrolled!
          </AlertTitle>
          <AlertDescription className="text-emerald-800">
            You have successfully registered for {selectedOfferings.length} courses ({totalCredits} credit units). An official confirmation has been logged in your audit profile.
          </AlertDescription>
        </Alert>
      )}

      {/* Credit Load Summary Card */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-5">
        <Card className="p-5 bg-white border-l-4 border-l-mema-teal-800">
          <div className="flex justify-between items-center">
            <div>
              <p className="text-xs text-slate-500 font-medium">Selected Credit Load</p>
              <h3 className="text-2xl font-bold text-mema-teal-900 mt-1">
                {totalCredits} <span className="text-sm font-normal text-slate-500">/ 24 max</span>
              </h3>
            </div>
            <Badge variant={totalCredits >= 12 && totalCredits <= 24 ? 'success' : 'warning'}>
              {totalCredits >= 12 ? 'Valid Load' : 'Under Minimum'}
            </Badge>
          </div>
          <p className="text-xs text-slate-500 mt-2">
            Minimum required: 12 units · Maximum allowed: 24 units
          </p>
        </Card>

        <Card className="p-5 bg-white border-l-4 border-l-mema-green-600">
          <div className="flex justify-between items-center">
            <div>
              <p className="text-xs text-slate-500 font-medium">Fee Clearance Gate</p>
              <h3 className="text-2xl font-bold text-mema-green-700 mt-1">
                Cleared (60%+)
              </h3>
            </div>
            <Badge variant="success">Eligible to Register</Badge>
          </div>
          <p className="text-xs text-slate-500 mt-2">
            Tuition payment threshold satisfied for registration window
          </p>
        </Card>

        <Card className="p-5 bg-white border-l-4 border-l-amber-500">
          <div className="flex justify-between items-center">
            <div>
              <p className="text-xs text-slate-500 font-medium">Registration Deadline</p>
              <h3 className="text-2xl font-bold text-slate-900 mt-1">
                Sep 15, 2026
              </h3>
            </div>
            <Badge variant="warning">14 Days Left</Badge>
          </div>
          <p className="text-xs text-slate-500 mt-2">
            Late registration penalty applies after deadline
          </p>
        </Card>
      </div>

      {/* Available Offerings & Selection */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2 space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Available Departmental Offerings</CardTitle>
              <CardDescription>
                Core & Elective units available for Year 3 Semester 1
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              {mockOfferings.map((offering) => {
                const isSelected = selectedOfferingIds.includes(offering.id);
                const capacityPercent = Math.round(
                  (offering.enrolled_count / offering.capacity) * 100
                );
                return (
                  <div
                    key={offering.id}
                    className={`p-4 rounded-xl border transition-all ${
                      isSelected
                        ? 'border-mema-teal-700 bg-mema-teal-50/40 ring-1 ring-mema-teal-700'
                        : 'border-slate-200 bg-white hover:border-slate-300'
                    }`}
                  >
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                      <div className="space-y-1">
                        <div className="flex items-center gap-2">
                          <span className="font-bold text-mema-teal-900">
                            {offering.course?.code}
                          </span>
                          <Badge variant="default" className="text-[11px]">
                            {offering.course?.credit_units} Credits
                          </Badge>
                          <Badge variant="outline" className="text-[11px]">
                            {offering.section_code}
                          </Badge>
                          <Badge variant="success" className="text-[11px]">
                            Core Unit
                          </Badge>
                        </div>
                        <h4 className="font-semibold text-slate-900 text-sm">
                          {offering.course?.title}
                        </h4>
                        <p className="text-xs text-slate-500 line-clamp-1">
                          {offering.course?.description}
                        </p>
                        <div className="flex flex-wrap items-center gap-4 text-xs text-slate-500 pt-1">
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

                      <div className="flex sm:flex-col items-center sm:items-end justify-between gap-2">
                        <div className="text-right">
                          <span className="text-[11px] text-slate-500">
                            Capacity: {offering.enrolled_count}/{offering.capacity}
                          </span>
                          <div className="w-24 h-1.5 bg-slate-100 rounded-full overflow-hidden mt-0.5">
                            <div
                              className="h-full bg-mema-teal-600 rounded-full"
                              style={{ width: `${capacityPercent}%` }}
                            />
                          </div>
                        </div>

                        <Button
                          size="sm"
                          variant={isSelected ? 'destructive' : 'default'}
                          onClick={() => toggleOffering(offering.id)}
                          className="h-8 text-xs gap-1"
                        >
                          {isSelected ? (
                            <>
                              <Trash2 className="h-3.5 w-3.5" /> Remove
                            </>
                          ) : (
                            <>
                              <Plus className="h-3.5 w-3.5" /> Add Course
                            </>
                          )}
                        </Button>
                      </div>
                    </div>
                  </div>
                );
              })}
            </CardContent>
          </Card>
        </div>

        {/* Registration Cart & Confirmation */}
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
                        <span className="font-bold text-slate-900 block">
                          {item.course?.code}
                        </span>
                        <span className="text-slate-500">{item.section_code}</span>
                      </div>
                      <div className="text-right">
                        <span className="font-semibold text-slate-800">
                          {item.course?.credit_units} Cr
                        </span>
                      </div>
                    </div>
                  ))}
                </div>
              )}

              <div className="p-3 bg-slate-50 rounded-xl border border-slate-200/80 space-y-1 text-xs">
                <div className="flex justify-between font-semibold text-slate-800">
                  <span>Total Credits:</span>
                  <span>{totalCredits} Units</span>
                </div>
                <div className="flex justify-between text-slate-500 text-[11px]">
                  <span>Status:</span>
                  <span className="text-emerald-700 font-bold">
                    {totalCredits >= 12 ? 'Ready for submission' : 'Need more units'}
                  </span>
                </div>
              </div>

              <Button
                onClick={handleRegister}
                isLoading={isSubmitting}
                disabled={totalCredits < 12 || selectedOfferings.length === 0}
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
