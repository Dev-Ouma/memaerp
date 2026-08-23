'use client';

import React, { useState } from 'react';
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  Button,
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
  Phone,
  Mail,
  MapPin,
  HelpCircle,
  FileText,
  CreditCard,
  Check,
  Clock,
  Printer,
  QrCode,
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
    gender: 'Female',
    nationality: 'Kenyan',
    programmeId: 'prog-01',
    campusId: 'camp-01',
    highSchool: '',
    kcseMeanGrade: 'A-',
    kcseIndexNumber: '',
    mpesaPhone: '',
    mpesaTxnId: '',
  });

  const [uploadedFile, setUploadedFile] = useState<{ name: string; size: string } | null>(null);
  const [isUploading, setIsUploading] = useState(false);
  const [paymentSent, setPaymentSent] = useState(false);
  const [paymentSuccess, setPaymentSuccess] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submittedAppNumber, setSubmittedAppNumber] = useState<string | null>(null);

  const selectedProg = mockProgrammes.find(p => p.id === formData.programmeId) || mockProgrammes[0] || { title: 'Direct Entry Course', code: 'Direct', duration_years: 4, award_level: 'Degree' };
  const selectedCamp = mockCampuses.find(c => c.id === formData.campusId) || mockCampuses[0] || { name: 'Main Campus', location: 'Nairobi' };

  const handleNext = () => setStep((prev) => Math.min(prev + 1, 5));
  const handleBack = () => setStep((prev) => Math.max(prev - 1, 1));

  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      setIsUploading(true);
      const file = e.target.files[0];
      setTimeout(() => {
        setUploadedFile({
          name: file.name,
          size: (file.size / (1024 * 1024)).toFixed(2) + ' MB',
        });
        setIsUploading(false);
      }, 1000);
    }
  };

  const handleSendPayment = () => {
    setPaymentSent(true);
    setTimeout(() => {
      setPaymentSuccess(true);
    }, 2500);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    setTimeout(() => {
      setIsSubmitting(false);
      const randomId = 'MEMA-2026-' + Math.floor(1000 + Math.random() * 9000);
      setSubmittedAppNumber(randomId);
      setStep(5);
    }, 1500);
  };

  // Checklist completion calculation
  const getCompletionPercentage = () => {
    let score = 0;
    if (formData.firstName && formData.lastName) score += 20;
    if (formData.email && formData.phone) score += 20;
    if (formData.programmeId) score += 20;
    if (formData.highSchool && formData.kcseIndexNumber) score += 20;
    if (uploadedFile) score += 10;
    if (paymentSuccess) score += 10;
    return score;
  };

  return (
    <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start max-w-7xl mx-auto">
      
      {/* ── LEFT: FORM WIZARD (2 Cols) ── */}
      <div className="lg:col-span-2 space-y-6">
        
        {/* Step Indicators */}
        <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex items-center justify-between overflow-x-auto gap-4">
          {[
            { num: 1, label: 'Profile', icon: <User className="h-4 w-4" /> },
            { num: 2, label: 'Course', icon: <GraduationCap className="h-4 w-4" /> },
            { num: 3, label: 'Academic', icon: <FileCheck className="h-4 w-4" /> },
            { num: 4, label: 'Fee Payment', icon: <CreditCard className="h-4 w-4" /> },
            { num: 5, label: 'Receipt', icon: <CheckCircle2 className="h-4 w-4" /> },
          ].map((s) => (
            <div key={s.num} className="flex items-center gap-2 flex-shrink-0">
              <div
                className={`h-9 w-9 rounded-full flex items-center justify-center font-bold text-sm transition-all ${
                  step >= s.num
                    ? 'bg-brand-primary text-white shadow-sm'
                    : 'bg-slate-100 text-slate-400'
                }`}
              >
                {step > s.num ? <Check className="h-4 w-4 text-emerald-300" /> : s.icon}
              </div>
              <span
                className={`text-xs font-semibold hidden sm:inline ${
                  step >= s.num ? 'text-brand-primary font-bold' : 'text-slate-400'
                }`}
              >
                {s.label}
              </span>
              {s.num < 5 && <span className="text-slate-300 hidden sm:inline ml-2">/</span>}
            </div>
          ))}
        </div>

        {/* Wizard Panel */}
        <Card className="shadow-md border-slate-200 bg-white rounded-2xl">
          
          {/* STEP 1: PERSONAL INFORMATION */}
          {step === 1 && (
            <div>
              <CardHeader className="border-b border-slate-100 pb-5">
                <CardTitle className="text-xl font-extrabold text-brand-primary font-heading flex items-center gap-2">
                  <span className="p-1.5 bg-brand-primary/10 rounded-lg text-brand-primary"><User className="h-5 w-5" /></span>
                  Personal &amp; Contact Profile
                </CardTitle>
                <CardDescription>
                  Please provide your legal names and contact details as they appear on your National ID or Passport.
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-5 pt-6">
                
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div className="space-y-1.5">
                    <label className="text-xs font-bold text-slate-700 block">First Name <span className="text-red-500">*</span></label>
                    <Input
                      placeholder="e.g. Grace"
                      value={formData.firstName}
                      onChange={(e) => setFormData({ ...formData, firstName: e.target.value })}
                      required
                    />
                  </div>
                  <div className="space-y-1.5">
                    <label className="text-xs font-bold text-slate-700 block">Last/Family Name <span className="text-red-500">*</span></label>
                    <Input
                      placeholder="e.g. Mutiso"
                      value={formData.lastName}
                      onChange={(e) => setFormData({ ...formData, lastName: e.target.value })}
                      required
                    />
                  </div>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div className="space-y-1.5">
                    <label className="text-xs font-bold text-slate-700 block">Email Address <span className="text-red-500">*</span></label>
                    <Input
                      type="email"
                      placeholder="e.g. grace.mutiso@example.com"
                      value={formData.email}
                      onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                      required
                    />
                  </div>
                  <div className="space-y-1.5">
                    <label className="text-xs font-bold text-slate-700 block">Phone Number (For SMS updates) <span className="text-red-500">*</span></label>
                    <Input
                      placeholder="e.g. +254 712 345678"
                      value={formData.phone}
                      onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                      required
                    />
                  </div>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                  <div className="space-y-1.5">
                    <label className="text-xs font-bold text-slate-700 block">National ID / Passport No. <span className="text-red-500">*</span></label>
                    <Input
                      placeholder="e.g. 38402910"
                      value={formData.nationalId}
                      onChange={(e) => setFormData({ ...formData, nationalId: e.target.value })}
                      required
                    />
                  </div>
                  <div className="space-y-1.5">
                    <label className="text-xs font-bold text-slate-700 block">Gender</label>
                    <select 
                      className="w-full h-10 px-3 rounded-lg border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-brand-primary"
                      value={formData.gender}
                      onChange={(e) => setFormData({ ...formData, gender: e.target.value })}
                    >
                      <option>Female</option>
                      <option>Male</option>
                      <option>Other</option>
                    </select>
                  </div>
                  <div className="space-y-1.5">
                    <label className="text-xs font-bold text-slate-700 block">Nationality</label>
                    <Input
                      value={formData.nationality}
                      onChange={(e) => setFormData({ ...formData, nationality: e.target.value })}
                    />
                  </div>
                </div>

                <div className="flex justify-end pt-4 border-t border-slate-100">
                  <Button 
                    onClick={handleNext} 
                    className="bg-brand-primary hover:bg-brand-primary-dark text-white gap-2 font-bold px-6 py-2.5 rounded-lg shadow-md transition-all"
                    disabled={!formData.firstName || !formData.lastName || !formData.email || !formData.phone}
                  >
                    Next: Choose Programme <ArrowRight className="h-4 w-4" />
                  </Button>
                </div>
              </CardContent>
            </div>
          )}

          {/* STEP 2: COURSE & CAMPUS SELECTION */}
          {step === 2 && (
            <div>
              <CardHeader className="border-b border-slate-100 pb-5">
                <CardTitle className="text-xl font-extrabold text-brand-primary font-heading flex items-center gap-2">
                  <span className="p-1.5 bg-brand-primary/10 rounded-lg text-brand-primary"><GraduationCap className="h-5 w-5" /></span>
                  Academic Programme &amp; Campus Choice
                </CardTitle>
                <CardDescription>
                  Select your intended course of study and preferred university campus.
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-6 pt-6">
                
                <div className="space-y-3">
                  <label className="text-xs font-bold text-slate-800 block uppercase tracking-wider">Available Catalog Options</label>
                  <div className="grid grid-cols-1 gap-3">
                    {mockProgrammes.map((prog) => (
                      <div
                        key={prog.id}
                        onClick={() => setFormData({ ...formData, programmeId: prog.id })}
                        className={`p-4 rounded-xl border cursor-pointer transition-all flex items-start gap-4 ${
                          formData.programmeId === prog.id
                            ? 'border-brand-primary bg-brand-primary/5 ring-2 ring-brand-primary shadow-sm'
                            : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50/50'
                        }`}
                      >
                        <div className={`w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 ${
                          formData.programmeId === prog.id ? 'bg-brand-primary text-white' : 'bg-slate-100 text-slate-600'
                        }`}>
                          <GraduationCap className="w-5 h-5" />
                        </div>
                        <div className="flex-1">
                          <div className="flex items-center justify-between flex-wrap gap-1">
                            <span className="font-bold text-slate-900 text-sm sm:text-base">{prog.title}</span>
                            <span className="px-2 py-0.5 text-2xs font-extrabold rounded bg-brand-accent/15 text-brand-accent uppercase tracking-wider">{prog.code}</span>
                          </div>
                          <p className="text-xs text-slate-500 mt-1">
                            Duration: {prog.duration_years} Years · Level: {prog.award_level} · Faculty: {prog.department?.name || 'Computing & IT'}
                          </p>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>

                <div className="space-y-3">
                  <label className="text-xs font-bold text-slate-800 block uppercase tracking-wider">Preferred Campus</label>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {mockCampuses.map((c) => (
                      <div
                        key={c.id}
                        onClick={() => setFormData({ ...formData, campusId: c.id })}
                        className={`p-4 rounded-xl border cursor-pointer transition-all flex items-center gap-3 ${
                          formData.campusId === c.id
                            ? 'border-brand-secondary bg-brand-secondary/5 ring-2 ring-brand-secondary shadow-sm'
                            : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50/50'
                        }`}
                      >
                        <div className={`w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 ${
                          formData.campusId === c.id ? 'bg-brand-secondary text-white' : 'bg-slate-100 text-slate-500'
                        }`}>
                          <MapPin className="w-4 h-4" />
                        </div>
                        <div>
                          <span className="font-bold text-sm text-slate-900 block">{c.name}</span>
                          <span className="text-xs text-slate-500">{c.location}</span>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>

                <div className="flex justify-between pt-4 border-t border-slate-100">
                  <Button variant="outline" onClick={handleBack} className="gap-2 font-bold">
                    <ArrowLeft className="h-4 w-4" /> Back
                  </Button>
                  <Button onClick={handleNext} className="bg-brand-primary hover:bg-brand-primary-dark text-white gap-2 font-bold px-6">
                    Next: Academic History <ArrowRight className="h-4 w-4" />
                  </Button>
                </div>
              </CardContent>
            </div>
          )}

          {/* STEP 3: ACADEMIC CREDENTIALS */}
          {step === 3 && (
            <div>
              <CardHeader className="border-b border-slate-100 pb-5">
                <CardTitle className="text-xl font-extrabold text-brand-primary font-heading flex items-center gap-2">
                  <span className="p-1.5 bg-brand-primary/10 rounded-lg text-brand-primary"><FileCheck className="h-5 w-5" /></span>
                  Academic Credentials &amp; Slip Upload
                </CardTitle>
                <CardDescription>
                  Enter your high school qualifications and upload a scanned copy of your certificate or results slip.
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-5 pt-6">
                
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div className="space-y-1.5">
                    <label className="text-xs font-bold text-slate-700 block">High School Name <span className="text-red-500">*</span></label>
                    <Input
                      placeholder="e.g. Alliance High School"
                      value={formData.highSchool}
                      onChange={(e) => setFormData({ ...formData, highSchool: e.target.value })}
                      required
                    />
                  </div>
                  <div className="space-y-1.5">
                    <label className="text-xs font-bold text-slate-700 block">KCSE Index Number <span className="text-red-500">*</span></label>
                    <Input
                      placeholder="e.g. 12345678001/2025"
                      value={formData.kcseIndexNumber}
                      onChange={(e) => setFormData({ ...formData, kcseIndexNumber: e.target.value })}
                      required
                    />
                  </div>
                </div>

                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-slate-700 block">KCSE Mean Grade <span className="text-red-500">*</span></label>
                  <Input
                    placeholder="e.g. A- (76 points)"
                    value={formData.kcseMeanGrade}
                    onChange={(e) => setFormData({ ...formData, kcseMeanGrade: e.target.value })}
                    required
                  />
                </div>

                {/* Upload Area */}
                <div className="space-y-2">
                  <label className="text-xs font-bold text-slate-800 block uppercase tracking-wider">Results Slip (PDF / Image) *</label>
                  
                  {!uploadedFile ? (
                    <div className="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center bg-slate-50/50 hover:bg-slate-50 transition-colors relative cursor-pointer">
                      <input
                        type="file"
                        accept="application/pdf,image/*"
                        onChange={handleFileUpload}
                        className="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                      />
                      <Upload className="h-8 w-8 text-brand-primary mx-auto mb-2" />
                      <p className="text-sm font-bold text-slate-800">
                        {isUploading ? 'Uploading file...' : 'Drag and drop or click to upload'}
                      </p>
                      <p className="text-xs text-slate-400 mt-1">Accepts PDF, JPEG, PNG (Max 5MB)</p>
                    </div>
                  ) : (
                    <div className="flex items-center justify-between p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                      <div className="flex items-center gap-3">
                        <div className="p-2 bg-emerald-100 rounded-lg text-emerald-700">
                          <FileText className="w-5 h-5" />
                        </div>
                        <div className="text-left">
                          <p className="text-sm font-bold text-slate-900 truncate max-w-[240px] sm:max-w-xs">{uploadedFile.name}</p>
                          <p className="text-xs text-slate-500">{uploadedFile.size}</p>
                        </div>
                      </div>
                      <Button
                        variant="outline"
                        size="sm"
                        className="text-red-600 hover:text-red-700 hover:bg-red-50 border-red-200 font-bold"
                        onClick={() => setUploadedFile(null)}
                      >
                        Remove
                      </Button>
                    </div>
                  )}
                </div>

                <div className="flex justify-between pt-4 border-t border-slate-100">
                  <Button variant="outline" onClick={handleBack} className="gap-2 font-bold">
                    <ArrowLeft className="h-4 w-4" /> Back
                  </Button>
                  <Button 
                    onClick={handleNext} 
                    className="bg-brand-primary hover:bg-brand-primary-dark text-white gap-2 font-bold px-6"
                    disabled={!formData.highSchool || !formData.kcseIndexNumber || !uploadedFile}
                  >
                    Next: Fee Payment <ArrowRight className="h-4 w-4" />
                  </Button>
                </div>
              </CardContent>
            </div>
          )}

          {/* STEP 4: FEE PAYMENT (Simulated M-PESA API) */}
          {step === 4 && (
            <div>
              <CardHeader className="border-b border-slate-100 pb-5">
                <CardTitle className="text-xl font-extrabold text-brand-primary font-heading flex items-center gap-2">
                  <span className="p-1.5 bg-brand-primary/10 rounded-lg text-brand-primary"><CreditCard className="h-5 w-5" /></span>
                  Application Fee Settlement
                </CardTitle>
                <CardDescription>
                  Confirm the processing fee of KES 1,500 using M-PESA instant STK push.
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-6 pt-6">
                
                <div className="p-5 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                  <div className="flex justify-between items-center text-sm">
                    <span className="text-slate-600">Application Category:</span>
                    <span className="font-bold text-slate-800">Direct Entry / Undergrad</span>
                  </div>
                  <div className="flex justify-between items-center text-sm">
                    <span className="text-slate-600">Administrative Fee:</span>
                    <span className="font-bold text-slate-800">KES 1,500.00</span>
                  </div>
                  <div className="border-t border-slate-200 pt-3 flex justify-between items-center text-base font-extrabold">
                    <span className="text-brand-primary">Total Payable:</span>
                    <span className="text-brand-primary">KES 1,500.00</span>
                  </div>
                </div>

                {!paymentSuccess ? (
                  <div className="space-y-4">
                    <div className="space-y-1.5">
                      <label className="text-xs font-bold text-slate-700 block">M-PESA Phone Number *</label>
                      <Input
                        placeholder="e.g. 0712345678"
                        value={formData.mpesaPhone}
                        onChange={(e) => setFormData({ ...formData, mpesaPhone: e.target.value })}
                        required
                      />
                    </div>

                    {!paymentSent ? (
                      <Button
                        onClick={handleSendPayment}
                        className="w-full bg-[#1E8449] hover:bg-[#16703d] text-white font-bold h-11 shadow-sm gap-2"
                        disabled={!formData.mpesaPhone}
                      >
                        Send M-PESA STK Push
                      </Button>
                    ) : (
                      <div className="p-4 bg-yellow-50 border border-yellow-200 rounded-xl flex items-start gap-3">
                        <Clock className="w-5 h-5 text-yellow-600 animate-spin flex-shrink-0 mt-0.5" />
                        <div>
                          <p className="text-sm font-bold text-yellow-800">STK Push dispatched...</p>
                          <p className="text-xs text-yellow-600 mt-1">Please check your mobile phone and enter your M-PESA PIN to authorize the transaction.</p>
                        </div>
                      </div>
                    )}
                  </div>
                ) : (
                  <div className="p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-start gap-3">
                    <CheckCircle2 className="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" />
                    <div>
                      <p className="text-sm font-bold text-emerald-800">Payment Confirmed!</p>
                      <p className="text-xs text-emerald-600 mt-1">Transaction Ref: <strong className="font-mono">TFK7291A04</strong>. Your application account has been funded.</p>
                    </div>
                  </div>
                )}

                <div className="flex justify-between pt-4 border-t border-slate-100">
                  <Button variant="outline" onClick={handleBack} className="gap-2 font-bold" disabled={paymentSent && !paymentSuccess}>
                    <ArrowLeft className="h-4 w-4" /> Back
                  </Button>
                  <Button 
                    onClick={handleSubmit} 
                    isLoading={isSubmitting}
                    className="bg-brand-primary hover:bg-brand-primary-dark text-white gap-2 font-bold px-6"
                    disabled={!paymentSuccess}
                  >
                    Submit Official Application
                  </Button>
                </div>
              </CardContent>
            </div>
          )}

          {/* STEP 5: RECEIPT & REGISTRATION SLIP */}
          {step === 5 && (
            <div className="p-8 text-center space-y-6">
              
              <div className="h-16 w-16 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center mx-auto shadow-inner">
                <CheckCircle2 className="h-10 w-10" />
              </div>

              <div className="space-y-2">
                <h3 className="text-2xl font-black text-brand-primary font-heading">
                  Admission File Created!
                </h3>
                <p className="text-sm text-slate-600 max-w-md mx-auto">
                  Your official application tracking reference is:
                  <br />
                  <span className="font-mono text-brand-accent font-black text-lg block mt-1 tracking-wider">
                    {submittedAppNumber}
                  </span>
                </p>
              </div>

              {/* Admission Tracking Slip */}
              <div className="border border-slate-300 rounded-2xl p-6 bg-white max-w-lg mx-auto text-left relative overflow-hidden shadow-sm">
                
                {/* Barcode line */}
                <div className="flex justify-between items-start border-b border-slate-100 pb-4 mb-4">
                  <div>
                    <span className="font-extrabold text-sm text-brand-primary block">MEMA UNIVERSITY</span>
                    <span className="text-[10px] text-slate-400 block tracking-widest uppercase">Office of Admissions</span>
                  </div>
                  <QrCode className="w-12 h-12 text-brand-primary" />
                </div>

                <div className="space-y-2.5 text-xs text-slate-800">
                  <div className="flex justify-between">
                    <span className="text-slate-500">Applicant Full Name:</span>
                    <span className="font-bold text-slate-800">{formData.firstName} {formData.lastName}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-slate-500">Primary Contact:</span>
                    <span className="font-semibold text-slate-700">{formData.email} · {formData.phone}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-slate-500">Academic Intake:</span>
                    <span className="font-semibold text-slate-700">September 2026 Academic Term</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-slate-500">Choice of Programme:</span>
                    <span className="font-bold text-slate-900">{selectedProg.title}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-slate-500">Campus Location:</span>
                    <span className="font-semibold text-slate-700">{selectedCamp.name}</span>
                  </div>
                  <div className="flex justify-between border-t border-slate-100 pt-2.5">
                    <span className="text-slate-500">Application Status:</span>
                    <span className="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-yellow-100 text-yellow-800">Under Evaluation</span>
                  </div>
                </div>

                {/* Faux Barcode */}
                <div className="mt-5 pt-4 border-t border-slate-100 flex flex-col items-center">
                  <div className="h-8 bg-slate-800 w-full flex items-center justify-between px-2 gap-1 rounded opacity-75">
                    {Array(48).fill(0).map((_,i) => (
                      <div key={i} className="bg-white h-full" style={{ width: (i%3===0?'4px':i%5===0?'1px':'2px') }} />
                    ))}
                  </div>
                  <span className="font-mono text-[9px] text-slate-400 mt-1">{submittedAppNumber}</span>
                </div>
              </div>

              <div className="flex items-center justify-center gap-3 pt-2">
                <Button
                  onClick={() => window.print()}
                  variant="outline"
                  className="gap-2 font-bold"
                >
                  <Printer className="w-4 h-4" /> Print Tracking Slip
                </Button>
                <Button
                  onClick={() => {
                    setStep(1);
                    setFormData({
                      ...formData,
                      firstName: '',
                      lastName: '',
                      email: '',
                      phone: '',
                      nationalId: '',
                      mpesaPhone: '',
                      mpesaTxnId: '',
                    });
                    setUploadedFile(null);
                    setPaymentSent(false);
                    setPaymentSuccess(false);
                  }}
                  className="bg-brand-primary hover:bg-brand-primary-dark text-white font-bold"
                >
                  New Application
                </Button>
              </div>

            </div>
          )}

        </Card>
      </div>

      {/* ── RIGHT: SUMMARY SIDEBAR (1 Col) ── */}
      <div className="space-y-6 lg:sticky lg:top-24">
        
        {/* Progress Card */}
        <Card className="p-5 border-slate-200 bg-white rounded-2xl shadow-sm">
          <h3 className="font-extrabold text-sm text-brand-primary mb-3 uppercase tracking-wider">File Progress</h3>
          <div className="space-y-2">
            <div className="flex justify-between text-xs font-bold text-slate-700">
              <span>Wizard Completeness</span>
              <span>{getCompletionPercentage()}%</span>
            </div>
            <div className="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
              <div 
                className="h-full bg-brand-secondary transition-all duration-300"
                style={{ width: `${getCompletionPercentage()}%` }}
              />
            </div>
          </div>
        </Card>

        {/* Selected Course Summary */}
        <Card className="p-5 border-slate-200 bg-white rounded-2xl shadow-sm space-y-4">
          <h3 className="font-extrabold text-sm text-brand-primary uppercase tracking-wider">Application Summary</h3>
          
          <div className="space-y-3 text-xs">
            <div className="border-b border-slate-100 pb-2">
              <span className="text-slate-400 block">Applicant Name:</span>
              <span className="font-bold text-slate-800 text-sm">
                {formData.firstName || formData.lastName ? `${formData.firstName} ${formData.lastName}` : 'Guest Profile'}
              </span>
            </div>
            <div className="border-b border-slate-100 pb-2">
              <span className="text-slate-400 block">Intended Course:</span>
              <span className="font-bold text-slate-800 text-sm">{selectedProg.title}</span>
            </div>
            <div className="border-b border-slate-100 pb-2">
              <span className="text-slate-400 block">Selected Campus:</span>
              <span className="font-semibold text-slate-700">{selectedCamp.name}</span>
            </div>
            <div>
              <span className="text-slate-400 block">Tuition Rate:</span>
              <span className="font-extrabold text-brand-accent text-sm">KES 85,000 / Semester</span>
            </div>
          </div>
        </Card>

        {/* Help Desk Support */}
        <Card className="p-5 border-slate-200 bg-brand-primary text-white rounded-2xl shadow-sm space-y-3">
          <div className="flex items-center gap-2">
            <HelpCircle className="w-5 h-5 text-brand-accent" />
            <h3 className="font-extrabold text-sm uppercase tracking-wider">Admissions Hotline</h3>
          </div>
          <p className="text-xs text-slate-300 leading-relaxed">
            Need guidance, eligibility confirmation, or experiencing issues with certificate upload? Get in touch immediately.
          </p>
          <div className="space-y-1.5 pt-1 text-xs">
            <div className="flex items-center gap-2 text-white">
              <Phone className="w-3.5 h-3.5 text-brand-accent" />
              <span>+254 20 892 000</span>
            </div>
            <div className="flex items-center gap-2 text-white">
              <Mail className="w-3.5 h-3.5 text-brand-accent" />
              <span>admissions@mema.ac.ke</span>
            </div>
          </div>
        </Card>

      </div>

    </div>
  );
}
