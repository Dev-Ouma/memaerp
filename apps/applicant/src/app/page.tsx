'use client';

import React, { useEffect, useState, useRef, useMemo } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Card, CardContent, Button, Input } from '@mema/ui';
import {
  User, GraduationCap, FileCheck, CheckCircle2, Upload, Phone, Mail, MapPin,
  HelpCircle, FileText, CreditCard, Clock, Printer, QrCode, Shield, ChevronDown,
  AlertCircle, Star, BookOpen, Building2, Search, Check, X, Sparkles, ArrowRight,
  Send, Compass, Award, BadgeCheck, ShieldAlert
} from 'lucide-react';
import { api, ApiError } from '@mema/api-client';

// ─── Data & Constants ────────────────────────────────────────────────────────
const COUNTRIES = [
  { code: 'KE', name: 'Kenya', flag: '🇰🇪', dial: '+254' },
  { code: 'UG', name: 'Uganda', flag: '🇺🇬', dial: '+256' },
  { code: 'TZ', name: 'Tanzania', flag: '🇹🇿', dial: '+255' },
  { code: 'RW', name: 'Rwanda', flag: '🇷🇼', dial: '+250' },
  { code: 'BI', name: 'Burundi', flag: '🇧🇮', dial: '+257' },
  { code: 'SS', name: 'South Sudan', flag: '🇸🇸', dial: '+211' },
  { code: 'ET', name: 'Ethiopia', flag: '🇪🇹', dial: '+251' },
  { code: 'SO', name: 'Somalia', flag: '🇸🇴', dial: '+252' },
  { code: 'NG', name: 'Nigeria', flag: '🇳🇬', dial: '+234' },
  { code: 'GH', name: 'Ghana', flag: '🇬🇭', dial: '+233' },
  { code: 'ZA', name: 'South Africa', flag: '🇿🇦', dial: '+27' },
  { code: 'EG', name: 'Egypt', flag: '🇪🇬', dial: '+20' },
  { code: 'GB', name: 'United Kingdom', flag: '🇬🇧', dial: '+44' },
  { code: 'US', name: 'United States', flag: '🇺🇸', dial: '+1' },
  { code: 'CA', name: 'Canada', flag: '🇨🇦', dial: '+1' },
  { code: 'IN', name: 'India', flag: '🇮🇳', dial: '+91' },
  { code: 'AU', name: 'Australia', flag: '🇦🇺', dial: '+61' },
  { code: 'DE', name: 'Germany', flag: '🇩🇪', dial: '+49' },
  { code: 'AE', name: 'United Arab Emirates', flag: '🇦🇪', dial: '+971' },
  { code: 'CN', name: 'China', flag: '🇨🇳', dial: '+86' },
];

export interface OptionProg {
  id: string;
  code?: string;
  name?: string;
  duration_years?: number;
  award_level?: string;
  department?: { name?: string };
  tuition?: string;
}

export interface OptionCamp {
  id: string;
  name?: string;
  town?: string;
  features?: string;
  code?: string;
}

const DEFAULT_PROGRAMMES: OptionProg[] = [
  { id: 'prog-cs', code: 'BSc. CS', name: 'BSc. Computer Science', duration_years: 4, award_level: 'Degree', department: { name: 'Faculty of Computing & IT' }, tuition: 'KES 85,000 / Sem' },
  { id: 'prog-se', code: 'BSc. SE', name: 'BSc. Software Engineering', duration_years: 4, award_level: 'Degree', department: { name: 'Faculty of Computing & IT' }, tuition: 'KES 85,000 / Sem' },
  { id: 'prog-ds', code: 'BSc. DS', name: 'BSc. Data Science & AI', duration_years: 4, award_level: 'Degree', department: { name: 'School of Data Sciences' }, tuition: 'KES 90,000 / Sem' },
  { id: 'prog-bba', code: 'BBA', name: 'BBA Business Administration', duration_years: 3, award_level: 'Degree', department: { name: 'School of Business & Economics' }, tuition: 'KES 75,000 / Sem' },
  { id: 'prog-msc-cs', code: 'MSc. CS', name: 'MSc. Computer Science', duration_years: 2, award_level: 'Masters', department: { name: 'Postgraduate Studies' }, tuition: 'KES 110,000 / Sem' },
  { id: 'prog-msc-ai', code: 'MSc. AI', name: 'MSc. Artificial Intelligence', duration_years: 2, award_level: 'Masters', department: { name: 'Postgraduate Studies' }, tuition: 'KES 120,000 / Sem' },
];

const DEFAULT_CAMPUSES: OptionCamp[] = [
  { id: 'camp-main', name: 'Main Campus (Nairobi)', town: 'Westlands, Nairobi', features: 'AI Center, Library, Hostels, Innovation Hub', code: 'MAIN' },
  { id: 'camp-mombasa', name: 'Mombasa Campus', town: 'Nyali, Mombasa', features: 'Ocean Hub, Computing Labs, Executive Suites', code: 'MSA' },
  { id: 'camp-kisumu', name: 'Kisumu Hub', town: 'Milimani, Kisumu', features: 'Data Research Center, Modern Lecture Halls', code: 'KSM' },
  { id: 'camp-eldoret', name: 'Eldoret Campus', town: 'Town Centre, Eldoret', features: 'Engineering Lab, Smart Classrooms', code: 'ELD' },
];

const POST_APPLICATION_STEPS = [
  {
    step: 1,
    title: 'Instant Reference & SMS Notification',
    time: 'Immediate',
    icon: <Send className="w-5 h-5 text-brand-primary" />,
    desc: 'You receive an official Application Number (e.g. APP-2026-XXXX) via SMS and Email to track real-time progress.',
  },
  {
    step: 2,
    title: 'Registrar Evaluation & Academic Verification',
    time: '24 – 48 Hours',
    icon: <BadgeCheck className="w-5 h-5 text-emerald-600" />,
    desc: 'Admissions committee verifies your KCSE credentials against KNEC and reserves your slot in the selected intake.',
  },
  {
    step: 3,
    title: 'Provisional Admission Letter & Student Portal',
    time: 'Day 3',
    icon: <Award className="w-5 h-5 text-amber-500" />,
    desc: 'Download your official signed Admission Letter with university registration details, HELB eligibility code, and fee structures.',
  },
  {
    step: 4,
    title: 'Campus Orientation & Biometric Onboarding',
    time: 'Sep 2026',
    icon: <Compass className="w-5 h-5 text-brand-secondary" />,
    desc: 'Report to your designated campus for student ID photo capture, hostel check-in, laptop issuance, and faculty welcome.',
  },
];

// ─── Reusable Components ─────────────────────────────────────────────────────

function SectionHeader({
  icon, title, subtitle, step,
}: { icon: React.ReactNode; title: string; subtitle: string; step: number }) {
  return (
    <div className="flex items-start gap-4 mb-6 pb-5 border-b border-slate-100">
      <div className="flex-shrink-0 w-11 h-11 rounded-xl bg-brand-primary/10 text-brand-primary flex items-center justify-center shadow-sm">
        {icon}
      </div>
      <div className="flex-1">
        <div className="flex items-center gap-2">
          <span className="inline-flex items-center justify-center w-5 h-5 rounded-full bg-brand-primary text-white text-[10px] font-black">{step}</span>
          <h2 className="text-base font-extrabold text-brand-primary font-heading">{title}</h2>
        </div>
        <p className="text-xs text-slate-500 mt-0.5">{subtitle}</p>
      </div>
    </div>
  );
}

function FieldLabel({ children, required, isValid, error }: { children: React.ReactNode; required?: boolean; isValid?: boolean; error?: string | null }) {
  return (
    <div className="flex items-center justify-between mb-1.5">
      <label className="text-xs font-bold text-slate-700 block">
        {children}{required && <span className="text-red-500 ml-0.5">*</span>}
      </label>
      {isValid && (
        <span className="inline-flex items-center gap-1 text-[10px] font-extrabold text-emerald-600">
          <Check className="w-3 h-3" /> Valid
        </span>
      )}
      {error && (
        <span className="inline-flex items-center gap-1 text-[10px] font-semibold text-rose-500">
          <ShieldAlert className="w-3 h-3" /> {error}
        </span>
      )}
    </div>
  );
}

// ─── Searchable Select Dropdown Component ─────────────────────────────────────
interface SearchableSelectProps<T> {
  options: T[];
  value: string;
  onChange: (val: string) => void;
  getOptionLabel: (opt: T) => string;
  getOptionValue: (opt: T) => string;
  renderOption?: (opt: T, isSelected: boolean) => React.ReactNode;
  renderSelected?: (opt: T) => React.ReactNode;
  placeholder?: string;
  searchPlaceholder?: string;
}

function SearchableSelect<T>({
  options,
  value,
  onChange,
  getOptionLabel,
  getOptionValue,
  renderOption,
  renderSelected,
  placeholder = 'Select an option…',
  searchPlaceholder = 'Type to search…',
}: SearchableSelectProps<T>) {
  const [open, setOpen] = useState(false);
  const [search, setSearch] = useState('');
  const wrapperRef = useRef<HTMLDivElement>(null);
  const searchInputRef = useRef<HTMLInputElement>(null);

  const selectedOpt = useMemo(
    () => options.find(o => getOptionValue(o) === value),
    [options, value, getOptionValue]
  );

  const filtered = useMemo(() => {
    if (!search.trim()) return options;
    const q = search.toLowerCase().trim();
    return options.filter(o => getOptionLabel(o).toLowerCase().includes(q));
  }, [options, search, getOptionLabel]);

  // Click outside to close
  useEffect(() => {
    function handleClickOutside(e: MouseEvent) {
      if (wrapperRef.current && !wrapperRef.current.contains(e.target as Node)) {
        setOpen(false);
      }
    }
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  // Auto focus on open
  useEffect(() => {
    if (open) {
      setTimeout(() => searchInputRef.current?.focus(), 50);
    } else {
      setSearch('');
    }
  }, [open]);

  return (
    <div className="relative" ref={wrapperRef}>
      {/* Trigger Button */}
      <div
        onClick={() => setOpen(!open)}
        className={`w-full min-h-[44px] px-3.5 py-2 rounded-xl border bg-white cursor-pointer transition-all flex items-center justify-between gap-2 ${
          open
            ? 'border-brand-primary ring-2 ring-brand-primary/20 shadow-sm'
            : 'border-slate-300 hover:border-slate-400'
        }`}
      >
        <div className="flex-1 min-w-0">
          {selectedOpt ? (
            renderSelected ? (
              renderSelected(selectedOpt)
            ) : (
              <span className="text-sm font-semibold text-slate-900 truncate block">
                {getOptionLabel(selectedOpt)}
              </span>
            )
          ) : (
            <span className="text-sm text-slate-400">{placeholder}</span>
          )}
        </div>
        <ChevronDown className={`w-4 h-4 text-slate-400 transition-transform ${open ? 'rotate-180 text-brand-primary' : ''}`} />
      </div>

      {/* Dropdown Menu */}
      {open && (
        <div className="absolute top-full left-0 right-0 mt-1.5 z-50 bg-white rounded-2xl border border-slate-200 shadow-2xl overflow-hidden animate-fadeInUp">
          {/* Search Box */}
          <div className="p-2 border-b border-slate-100 flex items-center gap-2 bg-slate-50">
            <Search className="w-4 h-4 text-slate-400 ml-1.5" />
            <input
              ref={searchInputRef}
              type="text"
              value={search}
              onChange={e => setSearch(e.target.value)}
              placeholder={searchPlaceholder}
              className="w-full text-xs font-semibold bg-transparent border-none outline-none text-slate-900 placeholder:text-slate-400 py-1"
            />
            {search && (
              <button
                type="button"
                onClick={() => setSearch('')}
                className="p-1 rounded-md text-slate-400 hover:text-slate-600 hover:bg-slate-200"
              >
                <X className="w-3.5 h-3.5" />
              </button>
            )}
          </div>

          {/* Options List */}
          <div className="max-h-60 overflow-y-auto p-1.5 space-y-1">
            {filtered.length === 0 ? (
              <div className="py-6 text-center text-xs text-slate-400 font-medium">
                No matching options found
              </div>
            ) : (
              filtered.map(opt => {
                const optVal = getOptionValue(opt);
                const isSelected = optVal === value;
                return (
                  <div
                    key={optVal}
                    onClick={() => {
                      onChange(optVal);
                      setOpen(false);
                    }}
                    className={`px-3 py-2.5 rounded-xl cursor-pointer text-xs font-medium transition-all flex items-center justify-between gap-2 ${
                      isSelected
                        ? 'bg-brand-primary/10 text-brand-primary font-bold border border-brand-primary/20'
                        : 'text-slate-700 hover:bg-slate-100'
                    }`}
                  >
                    <div className="flex-1 min-w-0">
                      {renderOption ? renderOption(opt, isSelected) : getOptionLabel(opt)}
                    </div>
                    {isSelected && <CheckCircle2 className="w-4 h-4 text-brand-primary flex-shrink-0" />}
                  </div>
                );
              })
            )}
          </div>
        </div>
      )}
    </div>
  );
}

// ─── Main Component ──────────────────────────────────────────────────────────

export default function ApplicantPortalPage() {
  const catalogue = useQuery({
    queryKey: ['admissions', 'catalogue'],
    queryFn: () => api.getAdmissionsCatalogue(),
  });

  const apiProgrammes = catalogue.data?.programmes as OptionProg[] | undefined;
  const programmes: OptionProg[] = apiProgrammes && apiProgrammes.length > 0 ? apiProgrammes : DEFAULT_PROGRAMMES;
  
  const apiCampuses = catalogue.data?.campuses as OptionCamp[] | undefined;
  const campuses: OptionCamp[] = apiCampuses && apiCampuses.length > 0 ? apiCampuses : DEFAULT_CAMPUSES;
  
  const applicationFee = catalogue.data?.application_fee?.amount ?? 1500;

  const [formData, setFormData] = useState({
    firstName: '',
    lastName: '',
    email: '',
    phone: '',
    password: '',
    confirmPassword: '',
    nationalId: '',
    gender: 'Female',
    nationality: 'Kenya',
    programmeId: '',
    campusId: '',
    highSchool: '',
    kcseMeanGrade: 'A-',
    kcseIndexNumber: '',
    mpesaPhone: '',
    mpesaTxnId: '',
  });

  // Touch tracking for real-time validation
  const [touched, setTouched] = useState<Record<string, boolean>>({});
  const markTouched = (field: string) => setTouched(prev => ({ ...prev, [field]: true }));

  useEffect(() => {
    if (!formData.programmeId && programmes[0]) {
      setFormData(f => ({ ...f, programmeId: programmes[0]!.id }));
    }
    if (!formData.campusId && campuses[0]) {
      setFormData(f => ({ ...f, campusId: campuses[0]!.id }));
    }
  }, [programmes, campuses, formData.programmeId, formData.campusId]);

  const [uploadedFile, setUploadedFile] = useState<File | null>(null);
  const [uploadedMeta, setUploadedMeta] = useState<{ name: string; size: string } | null>(null);
  const [isUploading, setIsUploading] = useState(false);
  const [paymentSent, setPaymentSent] = useState(false);
  const [paymentSuccess, setPaymentSuccess] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitError, setSubmitError] = useState<string | null>(null);
  const [submittedAppNumber, setSubmittedAppNumber] = useState<string | null>(null);

  // Field validation rules
  const validators = useMemo(() => {
    const isEmail = (val: string) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
    const isPhone = (val: string) => /^(?:\+254|0)[17]\d{8}$/.test(val.replace(/\s+/g, ''));
    const isKcseIndex = (val: string) => /^\d{8,11}\/\d{4}$/.test(val.trim()) || val.trim().length >= 8;

    return {
      firstName: formData.firstName.trim().length >= 2,
      lastName: formData.lastName.trim().length >= 2,
      email: isEmail(formData.email),
      phone: isPhone(formData.phone) || formData.phone.trim().length >= 9,
      password: formData.password.length >= 8,
      confirmPassword: formData.confirmPassword.length >= 8 && formData.confirmPassword === formData.password,
      nationalId: formData.nationalId.trim().length >= 6,
      highSchool: formData.highSchool.trim().length >= 3,
      kcseIndexNumber: isKcseIndex(formData.kcseIndexNumber),
      programmeId: Boolean(formData.programmeId),
      campusId: Boolean(formData.campusId),
      nationality: Boolean(formData.nationality),
      documents: Boolean(uploadedMeta),
      payment: paymentSuccess,
    };
  }, [formData, uploadedMeta, paymentSuccess]);

  const selectedProg = programmes.find((p) => p.id === formData.programmeId) ?? programmes[0];
  const selectedCamp = campuses.find((c) => c.id === formData.campusId) ?? campuses[0];

  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      setIsUploading(true);
      const file = e.target.files[0];
      setUploadedFile(file);
      setUploadedMeta({ name: file.name, size: (file.size / (1024 * 1024)).toFixed(2) + ' MB' });
      setIsUploading(false);
    }
  };

  const handleSendPayment = () => {
    setPaymentSent(true);
    setTimeout(() => setPaymentSuccess(true), 1500);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!validators.confirmPassword) {
      setSubmitError('Passwords do not match or are shorter than 8 characters.');
      return;
    }

    setIsSubmitting(true);
    setSubmitError(null);

    try {
      await api.getCsrfCookie().catch(() => undefined);

      try {
        await api.registerApplicant({
          given_name: formData.firstName,
          family_name: formData.lastName,
          email: formData.email,
          phone: formData.phone,
          national_id: formData.nationalId,
          gender: formData.gender,
          nationality: formData.nationality,
          password: formData.password,
        });
      } catch (reason) {
        if (!(reason instanceof ApiError) || reason.status !== 422) {
          throw reason;
        }
      }

      await api.login({ login: formData.email, password: formData.password });

      const intakeId = catalogue.data?.intakes?.[0]?.id;
      const application = await api.createApplication({
        programme_id: formData.programmeId,
        campus_id: formData.campusId,
        intake_id: intakeId,
        secondary_school_name: formData.highSchool,
        mean_grade: formData.kcseMeanGrade,
        kcse_index_number: formData.kcseIndexNumber,
      });

      if (uploadedFile) {
        await api.uploadApplicationDocument(application.id, uploadedFile, 'KCSE_CERTIFICATE');
      }

      await api.payApplicationFee(application.id, {
        channel: 'MPESA',
        phone: formData.mpesaPhone || formData.phone,
        transaction_reference: formData.mpesaTxnId || undefined,
      });

      const submitted = await api.submitApplication(application.id);
      setSubmittedAppNumber(submitted.application_number);
    } catch (reason) {
      setSubmitError(reason instanceof ApiError ? reason.message : 'Application submission failed.');
    } finally {
      setIsSubmitting(false);
    }
  };

  const completionScore = useMemo(() => {
    let s = 0;
    if (validators.firstName && validators.lastName) s += 20;
    if (validators.email && validators.phone) s += 15;
    if (validators.programmeId && validators.campusId) s += 20;
    if (validators.highSchool && validators.kcseIndexNumber) s += 20;
    if (validators.documents) s += 10;
    if (validators.payment) s += 15;
    return s;
  }, [validators]);

  // ─── Confirmation & Slip View ──────────────────────────────────────────────
  if (submittedAppNumber) {
    return (
      <div className="max-w-3xl mx-auto text-center space-y-8 py-8 animate-fadeInUp">
        <div className="h-20 w-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto shadow-lg ring-8 ring-emerald-50">
          <CheckCircle2 className="h-11 w-11" />
        </div>
        <div>
          <h2 className="text-3xl font-black text-brand-primary font-heading">Application Successfully Submitted!</h2>
          <p className="text-slate-500 text-sm mt-2 max-w-md mx-auto">
            Your registration file has been received and indexed by the Mema University Admissions Directorate.
          </p>
        </div>

        {/* Printable Official Slip */}
        <div className="border border-slate-200 rounded-3xl p-8 bg-white text-left shadow-xl relative overflow-hidden">
          <div className="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-brand-primary via-brand-secondary to-brand-accent" />
          <div className="flex justify-between items-start border-b border-slate-100 pb-6 mb-6">
            <div>
              <div className="flex items-center gap-2">
                <GraduationCap className="w-6 h-6 text-brand-primary" />
                <span className="font-extrabold text-brand-primary block text-lg tracking-tight">MEMA UNIVERSITY</span>
              </div>
              <span className="text-[11px] text-slate-400 block tracking-widest uppercase font-semibold mt-0.5">
                Directorate of Admissions · Official Registration Slip
              </span>
            </div>
            <QrCode className="w-14 h-14 text-brand-primary opacity-70" />
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm mb-6">
            {[
              { label: 'Applicant Name', value: `${formData.firstName} ${formData.lastName}` },
              { label: 'Official Email', value: formData.email },
              { label: 'Phone Number', value: formData.phone },
              { label: 'National ID / Passport', value: formData.nationalId },
              { label: 'Programme Chosen', value: selectedProg?.name ?? '—' },
              { label: 'Preferred Campus', value: selectedCamp?.name ?? '—' },
              { label: 'KCSE Index / Grade', value: `${formData.kcseIndexNumber} (${formData.kcseMeanGrade})` },
              { label: 'Intake Period', value: 'September 2026 Academic Term' },
            ].map(row => (
              <div key={row.label} className="p-3 bg-slate-50 rounded-xl border border-slate-100">
                <span className="text-slate-400 text-[11px] font-bold uppercase tracking-wider block">{row.label}</span>
                <span className="font-bold text-slate-800 text-xs mt-0.5 block truncate">{row.value}</span>
              </div>
            ))}
          </div>

          <div className="p-4 bg-emerald-50 rounded-2xl border border-emerald-200 flex items-center justify-between">
            <div>
              <span className="text-[11px] text-emerald-700 font-bold uppercase tracking-wider block">Official Tracking Ref:</span>
              <span className="font-mono font-black text-emerald-900 text-lg tracking-wider">{submittedAppNumber}</span>
            </div>
            <span className="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-200 text-emerald-800">
              Verified &amp; Active
            </span>
          </div>

          {/* Post Application Next Steps Timeline */}
          <div className="mt-8 pt-6 border-t border-slate-100">
            <h4 className="text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-4 flex items-center gap-1.5">
              <Clock className="w-4 h-4 text-brand-accent" /> What Happens Next?
            </h4>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              {POST_APPLICATION_STEPS.map((s) => (
                <div key={s.step} className="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-start gap-3">
                  <div className="p-2 rounded-xl bg-white shadow-xs flex-shrink-0">
                    {s.icon}
                  </div>
                  <div>
                    <div className="flex items-center gap-2">
                      <span className="text-xs font-bold text-slate-900 leading-tight">{s.title}</span>
                    </div>
                    <p className="text-[11px] text-slate-500 mt-1 leading-normal">{s.desc}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        <div className="flex items-center justify-center gap-4">
          <Button onClick={() => window.print()} variant="outline" className="gap-2 font-bold px-6 h-11 rounded-xl">
            <Printer className="w-4 h-4" /> Print Tracking Slip
          </Button>
          <a
            href="http://localhost:3002"
            className="inline-flex items-center gap-2 px-6 h-11 bg-brand-primary hover:bg-brand-primary-dark text-white font-bold rounded-xl shadow-md transition-all text-sm"
          >
            Go to Student Portal <ArrowRight className="w-4 h-4" />
          </a>
        </div>
      </div>
    );
  }

  // ─── Active Application Form View ──────────────────────────────────────────
  return (
    <form onSubmit={handleSubmit} className="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start max-w-7xl mx-auto pb-16">

      {submitError && (
        <div className="lg:col-span-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 flex items-center gap-3">
          <AlertCircle className="w-5 h-5 text-red-600 flex-shrink-0" />
          <span>{submitError}</span>
        </div>
      )}

      {/* LEFT COLUMN: CONTINUOUS SINGLE-PAGE FORM */}
      <div className="lg:col-span-2 space-y-6">

        {/* Real-time Progress Bar */}
        <div className="bg-white border border-slate-200 rounded-3xl p-5 shadow-xs">
          <div className="flex items-center justify-between mb-2.5">
            <div className="flex items-center gap-2">
              <Sparkles className="w-4 h-4 text-brand-accent" />
              <span className="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Application Completeness</span>
            </div>
            <span className="text-xs font-black px-2.5 py-0.5 rounded-full bg-brand-primary text-white">{completionScore}%</span>
          </div>
          <div className="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
            <div
              className="h-full bg-gradient-to-r from-brand-primary via-brand-secondary to-brand-accent transition-all duration-500 rounded-full"
              style={{ width: `${completionScore}%` }}
            />
          </div>
          <div className="mt-3.5 flex items-center gap-1.5 flex-wrap">
            {[
              { label: 'Personal Info', done: validators.firstName && validators.lastName && validators.email },
              { label: 'Programme & Campus', done: validators.programmeId && validators.campusId },
              { label: 'Academics & Slip', done: validators.highSchool && validators.kcseIndexNumber && validators.documents },
              { label: 'M-PESA Settlement', done: validators.payment },
            ].map((item) => (
              <span
                key={item.label}
                className={`inline-flex items-center gap-1.5 text-[11px] font-bold px-3 py-1 rounded-full transition-colors ${
                  item.done
                    ? 'bg-emerald-100 text-emerald-800'
                    : 'bg-slate-100 text-slate-500'
                }`}
              >
                {item.done ? <CheckCircle2 className="w-3 h-3 text-emerald-600" /> : <div className="w-2.5 h-2.5 rounded-full border border-slate-400" />}
                {item.label}
              </span>
            ))}
          </div>
        </div>

        {/* SECTION 1: PERSONAL & CONTACT PROFILE */}
        <Card className="shadow-xs border-slate-200 bg-white rounded-3xl overflow-hidden">
          <CardContent className="p-7">
            <SectionHeader
              step={1}
              icon={<User className="h-5 w-5" />}
              title="Personal & Identity Profile"
              subtitle="Provide your official legal details matching your National ID / Passport."
            />
            
            <div className="space-y-4">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <FieldLabel required isValid={validators.firstName} error={touched.firstName && !validators.firstName ? 'Enter at least 2 characters' : null}>
                    First Name
                  </FieldLabel>
                  <Input
                    placeholder="e.g. Grace"
                    value={formData.firstName}
                    onChange={e => setFormData({ ...formData, firstName: e.target.value })}
                    onBlur={() => markTouched('firstName')}
                    required
                  />
                </div>
                <div>
                  <FieldLabel required isValid={validators.lastName} error={touched.lastName && !validators.lastName ? 'Enter at least 2 characters' : null}>
                    Last / Family Name
                  </FieldLabel>
                  <Input
                    placeholder="e.g. Mutiso"
                    value={formData.lastName}
                    onChange={e => setFormData({ ...formData, lastName: e.target.value })}
                    onBlur={() => markTouched('lastName')}
                    required
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <FieldLabel required isValid={validators.email} error={touched.email && !validators.email ? 'Valid email required' : null}>
                    Official Email Address
                  </FieldLabel>
                  <Input
                    type="email"
                    placeholder="grace.mutiso@example.com"
                    value={formData.email}
                    onChange={e => setFormData({ ...formData, email: e.target.value })}
                    onBlur={() => markTouched('email')}
                    required
                  />
                </div>
                <div>
                  <FieldLabel required isValid={validators.phone} error={touched.phone && !validators.phone ? 'e.g. +254 712 345 678' : null}>
                    Phone Number
                  </FieldLabel>
                  <Input
                    placeholder="+254 712 345 678"
                    value={formData.phone}
                    onChange={e => setFormData({ ...formData, phone: e.target.value })}
                    onBlur={() => markTouched('phone')}
                    required
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <FieldLabel required isValid={validators.password} error={touched.password && !validators.password ? 'Min 8 characters' : null}>
                    Student Portal Password
                  </FieldLabel>
                  <Input
                    type="password"
                    placeholder="Create strong password (min 8 chars)"
                    value={formData.password}
                    onChange={e => setFormData({ ...formData, password: e.target.value })}
                    onBlur={() => markTouched('password')}
                    required
                    minLength={8}
                  />
                </div>
                <div>
                  <FieldLabel required isValid={validators.confirmPassword} error={touched.confirmPassword && !validators.confirmPassword ? 'Passwords must match' : null}>
                    Confirm Password
                  </FieldLabel>
                  <Input
                    type="password"
                    placeholder="Repeat password"
                    value={formData.confirmPassword}
                    onChange={e => setFormData({ ...formData, confirmPassword: e.target.value })}
                    onBlur={() => markTouched('confirmPassword')}
                    required
                    minLength={8}
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
                <div>
                  <FieldLabel required isValid={validators.nationalId}>
                    National ID / Passport
                  </FieldLabel>
                  <Input
                    placeholder="e.g. 38402910"
                    value={formData.nationalId}
                    onChange={e => setFormData({ ...formData, nationalId: e.target.value })}
                    onBlur={() => markTouched('nationalId')}
                    required
                  />
                </div>
                <div>
                  <FieldLabel>Gender</FieldLabel>
                  <div className="relative">
                    <select
                      className="w-full h-11 px-3.5 pr-8 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-primary appearance-none"
                      value={formData.gender}
                      onChange={e => setFormData({ ...formData, gender: e.target.value })}
                    >
                      <option>Female</option>
                      <option>Male</option>
                      <option>Other / Prefer not to say</option>
                    </select>
                    <ChevronDown className="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                  </div>
                </div>
                <div>
                  <FieldLabel required isValid={validators.nationality}>
                    Nationality
                  </FieldLabel>
                  <SearchableSelect
                    options={COUNTRIES}
                    value={formData.nationality}
                    onChange={val => setFormData({ ...formData, nationality: val })}
                    getOptionLabel={c => `${c.flag} ${c.name}`}
                    getOptionValue={c => c.name}
                    placeholder="Select Nationality"
                    searchPlaceholder="Search country…"
                    renderSelected={c => (
                      <div className="flex items-center gap-2">
                        <span className="text-base">{c.flag}</span>
                        <span className="text-xs font-bold text-slate-900">{c.name}</span>
                      </div>
                    )}
                    renderOption={c => (
                      <div className="flex items-center gap-2.5">
                        <span className="text-lg">{c.flag}</span>
                        <span className="text-xs font-semibold">{c.name}</span>
                        <span className="text-[10px] text-slate-400 ml-auto font-mono">{c.dial}</span>
                      </div>
                    )}
                  />
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* SECTION 2: ACADEMIC PROGRAMME & CAMPUS WITH SEARCHABLE SELECTS */}
        <Card className="shadow-xs border-slate-200 bg-white rounded-3xl overflow-hidden">
          <CardContent className="p-7">
            <SectionHeader
              step={2}
              icon={<GraduationCap className="h-5 w-5" />}
              title="Programme & Campus Selection"
              subtitle="Search and select your desired degree course and preferred university campus."
            />
            
            <div className="space-y-5">
              {/* Searchable Programme Selection */}
              <div>
                <FieldLabel required isValid={validators.programmeId}>
                  Choose Degree Programme (Searchable)
                </FieldLabel>
                <SearchableSelect
                  options={programmes}
                  value={formData.programmeId}
                  onChange={val => setFormData({ ...formData, programmeId: val })}
                  getOptionLabel={p => `${p.code ?? ''} — ${p.name ?? ''} (${p.duration_years ?? 4} Yrs)`}
                  getOptionValue={p => p.id}
                  placeholder="Type or select degree programme…"
                  searchPlaceholder="Search by title, code (e.g. BSc. CS), or department…"
                  renderSelected={p => (
                    <div className="flex items-center gap-3">
                      <div className="w-8 h-8 rounded-lg bg-brand-primary/10 text-brand-primary flex items-center justify-center flex-shrink-0">
                        <BookOpen className="w-4 h-4" />
                      </div>
                      <div className="min-w-0">
                        <div className="flex items-center gap-2">
                          <span className="font-bold text-xs text-slate-900 truncate">{p.name}</span>
                          {p.code && <span className="px-1.5 py-0.2 rounded-md bg-brand-accent/20 text-brand-accent text-[10px] font-black">{p.code}</span>}
                        </div>
                        <span className="text-[11px] text-slate-500 font-medium block">
                          {p.duration_years ?? 4} Years · {p.department?.name ?? 'Faculty of Computing'}
                        </span>
                      </div>
                    </div>
                  )}
                  renderOption={(p, isSelected) => (
                    <div className="flex items-start gap-3 py-1">
                      <div className={`w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 ${isSelected ? 'bg-brand-primary text-white' : 'bg-slate-100 text-slate-500'}`}>
                        <BookOpen className="w-4 h-4" />
                      </div>
                      <div className="flex-1 min-w-0">
                        <div className="flex items-center justify-between gap-2">
                          <span className="font-bold text-xs text-slate-900 truncate">{p.name}</span>
                          {p.code && <span className="px-1.5 py-0.5 rounded text-[9px] font-black bg-brand-accent/15 text-brand-accent uppercase">{p.code}</span>}
                        </div>
                        <div className="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                          <span>{p.duration_years ?? 4} Years</span>
                          <span>•</span>
                          <span>{p.award_level ?? 'Degree'}</span>
                          <span>•</span>
                          <span className="text-brand-secondary font-bold">{p.tuition ?? 'KES 85,000 / Sem'}</span>
                        </div>
                      </div>
                    </div>
                  )}
                />
              </div>

              {/* Searchable Campus Selection */}
              <div>
                <FieldLabel required isValid={validators.campusId}>
                  Preferred University Campus (Searchable)
                </FieldLabel>
                <SearchableSelect
                  options={campuses}
                  value={formData.campusId}
                  onChange={val => setFormData({ ...formData, campusId: val })}
                  getOptionLabel={c => `${c.name ?? ''} — ${c.town ?? 'Kenya'}`}
                  getOptionValue={c => c.id}
                  placeholder="Select Preferred Campus…"
                  searchPlaceholder="Search campus name or location…"
                  renderSelected={c => (
                    <div className="flex items-center gap-3">
                      <div className="w-8 h-8 rounded-lg bg-brand-secondary/10 text-brand-secondary flex items-center justify-center flex-shrink-0">
                        <Building2 className="w-4 h-4" />
                      </div>
                      <div className="min-w-0">
                        <span className="font-bold text-xs text-slate-900 block truncate">{c.name}</span>
                        <span className="text-[11px] text-slate-500 flex items-center gap-1">
                          <MapPin className="w-3 h-3 text-brand-secondary" /> {c.town ?? 'Kenya'}
                        </span>
                      </div>
                    </div>
                  )}
                  renderOption={(c, isSelected) => (
                    <div className="flex items-start gap-3 py-1">
                      <div className={`w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 ${isSelected ? 'bg-brand-secondary text-white' : 'bg-slate-100 text-slate-500'}`}>
                        <Building2 className="w-4 h-4" />
                      </div>
                      <div className="flex-1 min-w-0">
                        <span className="font-bold text-xs text-slate-900 block">{c.name}</span>
                        <span className="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                          <MapPin className="w-3 h-3" /> {c.town ?? 'Kenya'} — {c.features ?? 'All Facilities'}
                        </span>
                      </div>
                    </div>
                  )}
                />
              </div>
            </div>
          </CardContent>
        </Card>

        {/* SECTION 3: ACADEMIC CREDENTIALS & KCSE */}
        <Card className="shadow-xs border-slate-200 bg-white rounded-3xl overflow-hidden">
          <CardContent className="p-7">
            <SectionHeader
              step={3}
              icon={<FileCheck className="h-5 w-5" />}
              title="Academic Qualifications & Document Upload"
              subtitle="Enter your high school qualifications and attach your KCSE results slip or certificate."
            />
            
            <div className="space-y-4">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <FieldLabel required isValid={validators.highSchool} error={touched.highSchool && !validators.highSchool ? 'High school name required' : null}>
                    Secondary / High School Name
                  </FieldLabel>
                  <Input
                    placeholder="e.g. Alliance High School"
                    value={formData.highSchool}
                    onChange={e => setFormData({ ...formData, highSchool: e.target.value })}
                    onBlur={() => markTouched('highSchool')}
                    required
                  />
                </div>
                <div>
                  <FieldLabel required isValid={validators.kcseIndexNumber} error={touched.kcseIndexNumber && !validators.kcseIndexNumber ? 'Valid index number required' : null}>
                    KCSE Index Number
                  </FieldLabel>
                  <Input
                    placeholder="e.g. 12345678001/2025"
                    value={formData.kcseIndexNumber}
                    onChange={e => setFormData({ ...formData, kcseIndexNumber: e.target.value })}
                    onBlur={() => markTouched('kcseIndexNumber')}
                    required
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <FieldLabel required>KCSE Overall Mean Grade</FieldLabel>
                  <div className="relative">
                    <select
                      className="w-full h-11 px-3.5 pr-8 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-primary appearance-none"
                      value={formData.kcseMeanGrade}
                      onChange={e => setFormData({ ...formData, kcseMeanGrade: e.target.value })}
                    >
                      {['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D', 'D-', 'E'].map(g => (
                        <option key={g}>{g}</option>
                      ))}
                    </select>
                    <ChevronDown className="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                  </div>
                </div>
                <div>
                  <FieldLabel>Graduation Year</FieldLabel>
                  <Input placeholder="e.g. 2025" type="number" min={2000} max={2026} defaultValue={2025} />
                </div>
              </div>

              {/* Upload Certificate */}
              <div>
                <FieldLabel required isValid={validators.documents}>
                  Upload KCSE Results Slip or Certificate (PDF / Image)
                </FieldLabel>
                {!uploadedMeta ? (
                  <div className="border-2 border-dashed border-slate-300 rounded-2xl p-8 text-center bg-slate-50/60 hover:bg-slate-50 hover:border-brand-primary/60 transition-all relative cursor-pointer group">
                    <input
                      type="file"
                      accept="application/pdf,image/*"
                      onChange={handleFileUpload}
                      className="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                    />
                    <Upload className="h-10 w-10 text-brand-primary mx-auto mb-2.5 group-hover:scale-110 transition-transform" />
                    <p className="text-xs font-extrabold text-slate-800">
                      {isUploading ? 'Uploading file…' : 'Drag & drop or click to choose file'}
                    </p>
                    <p className="text-[11px] text-slate-400 mt-1">PDF, JPG, PNG — Maximum 5 MB</p>
                  </div>
                ) : (
                  <div className="flex items-center justify-between p-4 bg-emerald-50 border border-emerald-200 rounded-2xl">
                    <div className="flex items-center gap-3">
                      <div className="p-2.5 bg-emerald-100 rounded-xl text-emerald-600">
                        <FileText className="w-5 h-5" />
                      </div>
                      <div>
                        <p className="text-xs font-bold text-slate-900 truncate max-w-[240px]">{uploadedMeta.name}</p>
                        <p className="text-[11px] text-slate-500">{uploadedMeta.size} · Uploaded</p>
                      </div>
                    </div>
                    <Button
                      variant="outline"
                      size="sm"
                      className="text-red-600 hover:text-red-700 hover:bg-red-50 border-red-200 font-bold text-xs"
                      onClick={() => { setUploadedFile(null); setUploadedMeta(null); }}
                      type="button"
                    >
                      Remove
                    </Button>
                  </div>
                )}
              </div>
            </div>
          </CardContent>
        </Card>

        {/* SECTION 4: APPLICATION FEE SETTLEMENT */}
        <Card className="shadow-xs border-slate-200 bg-white rounded-3xl overflow-hidden">
          <CardContent className="p-7">
            <SectionHeader
              step={4}
              icon={<CreditCard className="h-5 w-5" />}
              title="Application Fee Settlement"
              subtitle={`Pay the non-refundable processing fee of KES ${applicationFee.toLocaleString()} via M-PESA STK Push.`}
            />

            <div className="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-2 mb-5">
              <div className="flex justify-between text-xs">
                <span className="text-slate-500">Processing Fee</span>
                <span className="font-semibold text-slate-700">KES 1,000.00</span>
              </div>
              <div className="flex justify-between text-xs">
                <span className="text-slate-500">Administrative Levy</span>
                <span className="font-semibold text-slate-700">KES 500.00</span>
              </div>
              <div className="border-t border-slate-200 pt-2 flex justify-between items-center">
                <span className="font-extrabold text-brand-primary text-xs">Total Payable</span>
                <span className="font-extrabold text-base text-brand-primary">KES {applicationFee.toLocaleString()}.00</span>
              </div>
            </div>

            {!paymentSuccess ? (
              <div className="space-y-4">
                <div>
                  <FieldLabel required>M-PESA Phone Number for STK Push</FieldLabel>
                  <Input
                    placeholder="e.g. 0712 345 678"
                    value={formData.mpesaPhone || formData.phone}
                    onChange={e => setFormData({ ...formData, mpesaPhone: e.target.value })}
                  />
                </div>
                {!paymentSent ? (
                  <Button
                    type="button"
                    onClick={handleSendPayment}
                    className="w-full bg-[#1E8449] hover:bg-[#16703d] text-white font-bold h-11 rounded-xl shadow-xs gap-2 text-xs"
                  >
                    <Shield className="w-4 h-4" /> Send M-PESA STK Push (Simulate KES 1,500)
                  </Button>
                ) : (
                  <div className="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-center gap-3">
                    <Clock className="w-5 h-5 text-amber-600 animate-spin flex-shrink-0" />
                    <div>
                      <p className="text-xs font-bold text-amber-900">STK Push Request Dispatched</p>
                      <p className="text-[11px] text-amber-700">Check your phone screen and enter your M-PESA PIN.</p>
                    </div>
                  </div>
                )}
              </div>
            ) : (
              <div className="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3">
                <div className="w-9 h-9 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center flex-shrink-0">
                  <CheckCircle2 className="w-5 h-5" />
                </div>
                <div>
                  <p className="text-xs font-extrabold text-emerald-900">M-PESA Payment Verified!</p>
                  <p className="text-[11px] text-emerald-700 font-mono">Ref: TFK7291A04 · KES {applicationFee.toLocaleString()}.00 Confirmed</p>
                </div>
              </div>
            )}
          </CardContent>
        </Card>

        {/* SUBMIT BUTTON CARD */}
        <div className="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-4">
          <div className="flex items-start gap-3 p-3.5 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-800">
            <Star className="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" />
            <span>
              <strong>Ready to submit?</strong> Ensure all fields are filled, certificate is uploaded, and M-PESA payment is confirmed.
            </span>
          </div>

          <Button
            type="submit"
            isLoading={isSubmitting}
            disabled={!validators.firstName || !validators.lastName || !validators.email || !validators.documents || !validators.payment}
            className="w-full bg-brand-primary hover:bg-brand-primary-dark text-white font-extrabold h-12 text-sm shadow-md gap-2 rounded-2xl transition-all"
          >
            <CheckCircle2 className="w-4 h-4" /> Submit Official University Application
          </Button>
        </div>

      </div>

      {/* RIGHT COLUMN: STICKY SUMMARY & WHAT HAPPENS NEXT GUIDE */}
      <div className="space-y-6 lg:sticky lg:top-24">

        {/* Application Summary Card */}
        <Card className="p-6 border-slate-200 bg-white rounded-3xl shadow-xs">
          <h3 className="font-extrabold text-xs text-brand-primary mb-4 uppercase tracking-wider flex items-center gap-2">
            <FileText className="w-4 h-4" /> Application Summary
          </h3>
          <div className="space-y-3 text-xs">
            {[
              { label: 'Applicant', value: formData.firstName || formData.lastName ? `${formData.firstName} ${formData.lastName}`.trim() : 'Not entered' },
              { label: 'Programme', value: selectedProg?.name ?? '—' },
              { label: 'Campus', value: selectedCamp?.name ?? '—' },
              { label: 'Duration', value: selectedProg ? `${selectedProg.duration_years ?? 4} Years (${selectedProg.award_level ?? 'Degree'})` : '—' },
              { label: 'Fee Status', value: paymentSuccess ? 'KES 1,500 Paid' : 'Pending Payment' },
              { label: 'Academic Intake', value: 'September 2026' },
            ].map(row => (
              <div key={row.label} className="flex justify-between items-start gap-2 pb-2 border-b border-slate-100 last:border-0">
                <span className="text-slate-400 flex-shrink-0">{row.label}:</span>
                <span className="font-bold text-slate-800 text-right truncate max-w-[170px]">{row.value}</span>
              </div>
            ))}
          </div>
        </Card>

        {/* WHAT HAPPENS AFTER APPLICATION GUIDE */}
        <Card className="p-6 border-slate-200 bg-white rounded-3xl shadow-xs">
          <div className="flex items-center gap-2 mb-4">
            <Clock className="w-4 h-4 text-brand-accent" />
            <h3 className="font-extrabold text-xs text-brand-primary uppercase tracking-wider">
              What Happens Next?
            </h3>
          </div>
          <div className="space-y-4">
            {POST_APPLICATION_STEPS.map((s, idx) => (
              <div key={s.step} className="flex items-start gap-3 relative">
                {idx < POST_APPLICATION_STEPS.length - 1 && (
                  <div className="absolute left-3.5 top-8 bottom-0 w-0.5 bg-slate-200" />
                )}
                <div className="w-7 h-7 rounded-full bg-brand-primary/10 text-brand-primary flex items-center justify-center font-bold text-[11px] flex-shrink-0 z-10">
                  {s.step}
                </div>
                <div className="flex-1 min-w-0">
                  <div className="flex items-center justify-between gap-1">
                    <span className="font-bold text-slate-900 text-xs">{s.title}</span>
                    <span className="text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 uppercase">{s.time}</span>
                  </div>
                  <p className="text-[11px] text-slate-500 mt-0.5 leading-snug">{s.desc}</p>
                </div>
              </div>
            ))}
          </div>
        </Card>

        {/* Admissions Hotline Card */}
        <Card className="p-6 border-0 bg-gradient-to-br from-brand-primary to-brand-primary-dark text-white rounded-3xl shadow-md">
          <div className="flex items-center gap-2 mb-2">
            <HelpCircle className="w-5 h-5 text-brand-accent" />
            <h3 className="font-extrabold text-xs uppercase tracking-wider">Admissions Directorate</h3>
          </div>
          <p className="text-xs text-slate-300 leading-relaxed mb-4">
            Have questions about qualification thresholds or course transfer? Contact our admissions counselors.
          </p>
          <div className="space-y-2 text-xs">
            <div className="flex items-center gap-2">
              <Phone className="w-3.5 h-3.5 text-brand-accent flex-shrink-0" />
              <span className="font-semibold">+254 20 892 000</span>
            </div>
            <div className="flex items-center gap-2">
              <Mail className="w-3.5 h-3.5 text-brand-accent flex-shrink-0" />
              <span className="font-semibold">admissions@mema.ac.ke</span>
            </div>
          </div>
        </Card>

      </div>

    </form>
  );
}
