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
  Input,
} from '@mema/ui';
import {
  User,
  GraduationCap,
  FileCheck,
  CheckCircle2,
  ArrowRight,
  ArrowLeft,
  Upload,
} from 'lucide-react';
import { mockProgrammes, mockCampuses } from '@mema/api-client';

export default function ApplicantPortalPage() {
  const [step, setStep] = useState(1);
  const [formData, setFormData] = useState({
    firstName: '',
    lastName: '',
    email: '',
    phone: '',
    nationalId: '',
    programmeId: 'prog-01',
    campusId: 'camp-01',
    highSchool: '',
    kcseMeanGrade: 'A-',
    kcseIndexNumber: '',
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submittedAppNumber, setSubmittedAppNumber] = useState<string | null>(null);

  const handleNext = () => setStep((prev) => Math.min(prev + 1, 4));
  const handleBack = () => setStep((prev) => Math.max(prev - 1, 1));

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    setTimeout(() => {
      setIsSubmitting(false);
      setSubmittedAppNumber('MEMA-2026-0941');
      setStep(4);
    }, 1500);
  };

  return (
    <div className="space-y-8">
      {/* Wizard Step Progress */}
      <div className="flex items-center justify-between max-w-2xl mx-auto px-4">
        {[
          { num: 1, label: 'Personal Info', icon: <User className="h-4 w-4" /> },
          { num: 2, label: 'Programme & Campus', icon: <GraduationCap className="h-4 w-4" /> },
          { num: 3, label: 'Academic Credentials', icon: <FileCheck className="h-4 w-4" /> },
          { num: 4, label: 'Confirmation', icon: <CheckCircle2 className="h-4 w-4" /> },
        ].map((s) => (
          <div key={s.num} className="flex flex-col items-center gap-1.5">
            <div
              className={`h-10 w-10 rounded-full flex items-center justify-center font-bold text-sm transition-all ${
                step >= s.num
                  ? 'bg-mema-teal-800 text-white shadow-md'
                  : 'bg-slate-200 text-slate-500'
              }`}
            >
              {step > s.num ? <CheckCircle2 className="h-5 w-5 text-emerald-300" /> : s.icon}
            </div>
            <span
              className={`text-xs font-semibold hidden sm:block ${
                step >= s.num ? 'text-mema-teal-900' : 'text-slate-400'
              }`}
            >
              {s.label}
            </span>
          </div>
        ))}
      </div>

      {/* Step Content */}
      <Card className="shadow-lg border-slate-200">
        {step === 1 && (
          <div>
            <CardHeader>
              <CardTitle>Step 1: Personal & Contact Information</CardTitle>
              <CardDescription>
                Provide your official names as they appear on your National ID or Passport
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="text-xs font-semibold text-slate-700 block mb-1">
                    First Name *
                  </label>
                  <Input
                    placeholder="e.g. Grace"
                    value={formData.firstName}
                    onChange={(e) => setFormData({ ...formData, firstName: e.target.value })}
                    required
                  />
                </div>
                <div>
                  <label className="text-xs font-semibold text-slate-700 block mb-1">
                    Last Name *
                  </label>
                  <Input
                    placeholder="e.g. Mutiso"
                    value={formData.lastName}
                    onChange={(e) => setFormData({ ...formData, lastName: e.target.value })}
                    required
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="text-xs font-semibold text-slate-700 block mb-1">
                    Email Address *
                  </label>
                  <Input
                    type="email"
                    placeholder="applicant@gmail.com"
                    value={formData.email}
                    onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                    required
                  />
                </div>
                <div>
                  <label className="text-xs font-semibold text-slate-700 block mb-1">
                    Phone Number (SMS alerts) *
                  </label>
                  <Input
                    placeholder="+254 7XX XXX XXX"
                    value={formData.phone}
                    onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                    required
                  />
                </div>
              </div>

              <div>
                <label className="text-xs font-semibold text-slate-700 block mb-1">
                  National ID / Passport / Birth Cert Number *
                </label>
                <Input
                  placeholder="38920194"
                  value={formData.nationalId}
                  onChange={(e) => setFormData({ ...formData, nationalId: e.target.value })}
                  required
                />
              </div>

              <div className="flex justify-end pt-4">
                <Button onClick={handleNext} className="bg-mema-teal-800 hover:bg-mema-teal-700 text-white gap-2">
                  Next: Programme Selection <ArrowRight className="h-4 w-4" />
                </Button>
              </div>
            </CardContent>
          </div>
        )}

        {step === 2 && (
          <div>
            <CardHeader>
              <CardTitle>Step 2: Choose Programme & Campus</CardTitle>
              <CardDescription>
                Select your intended degree programme and preferred study location
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-5">
              <div>
                <label className="text-xs font-semibold text-slate-700 block mb-2">
                  Select Programme of Study *
                </label>
                <div className="space-y-3">
                  {mockProgrammes.map((prog) => (
                    <div
                      key={prog.id}
                      onClick={() => setFormData({ ...formData, programmeId: prog.id })}
                      className={`p-4 rounded-xl border cursor-pointer transition-all ${
                        formData.programmeId === prog.id
                          ? 'border-mema-teal-700 bg-mema-teal-50/50 ring-2 ring-mema-teal-700'
                          : 'border-slate-200 hover:border-slate-300'
                      }`}
                    >
                      <div className="flex items-center justify-between">
                        <span className="font-bold text-slate-900">{prog.title}</span>
                        <Badge variant="default">{prog.code}</Badge>
                      </div>
                      <p className="text-xs text-slate-500 mt-1">
                        {prog.duration_years} Years · {prog.award_level} · {prog.department?.name}
                      </p>
                    </div>
                  ))}
                </div>
              </div>

              <div>
                <label className="text-xs font-semibold text-slate-700 block mb-2">
                  Preferred Campus *
                </label>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  {mockCampuses.map((c) => (
                    <div
                      key={c.id}
                      onClick={() => setFormData({ ...formData, campusId: c.id })}
                      className={`p-3 rounded-xl border cursor-pointer transition-all ${
                        formData.campusId === c.id
                          ? 'border-mema-green-600 bg-mema-green-50/50 ring-2 ring-mema-green-600'
                          : 'border-slate-200'
                      }`}
                    >
                      <span className="font-bold text-sm text-slate-900 block">{c.name}</span>
                      <span className="text-xs text-slate-500">{c.location}</span>
                    </div>
                  ))}
                </div>
              </div>

              <div className="flex justify-between pt-4">
                <Button variant="outline" onClick={handleBack} className="gap-2">
                  <ArrowLeft className="h-4 w-4" /> Back
                </Button>
                <Button onClick={handleNext} className="bg-mema-teal-800 hover:bg-mema-teal-700 text-white gap-2">
                  Next: Academic History <ArrowRight className="h-4 w-4" />
                </Button>
              </div>
            </CardContent>
          </div>
        )}

        {step === 3 && (
          <div>
            <CardHeader>
              <CardTitle>Step 3: Academic Credentials & Certificate Upload</CardTitle>
              <CardDescription>
                Enter your KCSE / A-Level results and upload clear PDF/Image copies
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="text-xs font-semibold text-slate-700 block mb-1">
                    Secondary School Name *
                  </label>
                  <Input
                    placeholder="e.g. Alliance High School"
                    value={formData.highSchool}
                    onChange={(e) => setFormData({ ...formData, highSchool: e.target.value })}
                    required
                  />
                </div>
                <div>
                  <label className="text-xs font-semibold text-slate-700 block mb-1">
                    KCSE Index Number *
                  </label>
                  <Input
                    placeholder="12345678001/2025"
                    value={formData.kcseIndexNumber}
                    onChange={(e) => setFormData({ ...formData, kcseIndexNumber: e.target.value })}
                    required
                  />
                </div>
              </div>

              <div>
                <label className="text-xs font-semibold text-slate-700 block mb-1">
                  KCSE Mean Grade *
                </label>
                <Input
                  placeholder="e.g. A- (78 points)"
                  value={formData.kcseMeanGrade}
                  onChange={(e) => setFormData({ ...formData, kcseMeanGrade: e.target.value })}
                  required
                />
              </div>

              <div className="p-6 border-2 border-dashed border-slate-300 rounded-xl text-center bg-slate-50/60 hover:bg-slate-50 transition-colors">
                <Upload className="h-8 w-8 text-mema-teal-700 mx-auto mb-2" />
                <p className="text-sm font-semibold text-slate-800">
                  Upload Result Slip / Certificate (PDF or JPEG)
                </p>
                <p className="text-xs text-slate-400 mt-1">Maximum file size: 5MB</p>
              </div>

              <div className="flex justify-between pt-4">
                <Button variant="outline" onClick={handleBack} className="gap-2">
                  <ArrowLeft className="h-4 w-4" /> Back
                </Button>
                <Button
                  onClick={handleSubmit}
                  isLoading={isSubmitting}
                  className="bg-mema-green-600 hover:bg-mema-green-700 text-white font-bold gap-2 px-6"
                >
                  Submit Application
                </Button>
              </div>
            </CardContent>
          </div>
        )}

        {step === 4 && (
          <div className="p-8 text-center space-y-6">
            <div className="h-16 w-16 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center mx-auto shadow-inner">
              <CheckCircle2 className="h-10 w-10" />
            </div>

            <div className="space-y-2">
              <h3 className="text-2xl font-bold text-slate-900 font-heading">
                Application Successfully Submitted!
              </h3>
              <p className="text-sm text-slate-600 max-w-md mx-auto">
                Your application reference is{' '}
                <strong className="font-mono text-mema-teal-900 font-bold text-base">
                  {submittedAppNumber}
                </strong>
                . We have sent an email and SMS confirmation with your tracking details.
              </p>
            </div>

            <div className="p-4 bg-slate-50 border border-slate-200 rounded-xl max-w-md mx-auto text-left text-xs space-y-1.5">
              <div className="flex justify-between">
                <span className="text-slate-500">Applicant:</span>
                <span className="font-semibold text-slate-800">
                  {formData.firstName || 'Grace'} {formData.lastName || 'Mutiso'}
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-slate-500">Target Programme:</span>
                <span className="font-semibold text-slate-800">BSc in Computer Science</span>
              </div>
              <div className="flex justify-between">
                <span className="text-slate-500">Intake:</span>
                <span className="font-semibold text-slate-800">September 2026 Intake</span>
              </div>
              <div className="flex justify-between">
                <span className="text-slate-500">Status:</span>
                <span className="text-emerald-700 font-bold">Under Review</span>
              </div>
            </div>

            <Button
              onClick={() => setStep(1)}
              variant="outline"
              className="mt-4"
            >
              Submit Another Application
            </Button>
          </div>
        )}
      </Card>
    </div>
  );
}
