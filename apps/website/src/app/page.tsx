'use client';

import React, { useState } from 'react';
import {
  Card,
  Button,
  Badge,
  Input,
} from '@mema/ui';
import {
  Sparkles,
  ArrowRight,
  BookOpen,
  Search,
  CheckCircle2,
  Building,
  ShieldCheck,
  Award,
} from 'lucide-react';
import { mockProgrammes } from '@mema/api-client';

function programmeLabel(programme: (typeof mockProgrammes)[number]) {
  return programme.title ?? programme.name ?? programme.code;
}

export default function PublicWebsiteHomePage() {
  const [searchQuery, setSearchQuery] = useState('');

  const filteredProgrammes = mockProgrammes.filter(
    (p) =>
      programmeLabel(p).toLowerCase().includes(searchQuery.toLowerCase()) ||
      p.code.toLowerCase().includes(searchQuery.toLowerCase())
  );

  return (
    <div className="space-y-20 pb-20">
      {/* Hero Section */}
      <section className="relative overflow-hidden bg-gradient-to-br from-mema-teal-950 via-mema-teal-900 to-slate-900 text-white py-24 px-4 sm:px-8">
        <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-mema-green-600/20 via-transparent to-transparent opacity-60" />

        <div className="max-w-6xl mx-auto relative z-10 text-center space-y-6">
          <Badge className="bg-mema-teal-800 text-mema-green-300 border-mema-teal-700 px-4 py-1 text-xs uppercase tracking-widest font-bold">
            <Sparkles className="h-3.5 w-3.5 mr-1" /> September 2026 Admissions Open
          </Badge>

          <h1 className="text-4xl sm:text-6xl font-extrabold font-heading tracking-tight text-white max-w-4xl mx-auto leading-tight">
            Empowering the Next Generation of <span className="text-transparent bg-clip-text bg-gradient-to-r from-mema-green-400 to-emerald-200">Innovators & Leaders</span>
          </h1>

          <p className="text-base sm:text-xl text-mema-teal-100 max-w-2xl mx-auto leading-relaxed">
            World-class undergraduate and postgraduate programmes in computing, software engineering, business, and data sciences.
          </p>

          <div className="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
            <a href="http://localhost:3001">
              <Button size="lg" className="bg-mema-green-600 hover:bg-mema-green-700 text-white font-bold px-8 shadow-lg gap-2 text-base">
                Apply for Sep 2026 Intake <ArrowRight className="h-5 w-5" />
              </Button>
            </a>
            <a href="#programmes">
              <Button size="lg" variant="outline" className="text-white border-white/30 bg-white/10 hover:bg-white/20 px-8 text-base">
                Explore Programmes
              </Button>
            </a>
          </div>
        </div>
      </section>

      {/* Key Numbers / Institutional Stats */}
      <section className="max-w-6xl mx-auto px-4 sm:px-8">
        <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
          <div className="p-6 rounded-2xl bg-white border border-slate-200 text-center shadow-xs">
            <h3 className="text-3xl sm:text-4xl font-extrabold text-mema-teal-900 font-heading">
              100%
            </h3>
            <p className="text-xs text-slate-500 font-semibold mt-1">CUE Accredited Degrees</p>
          </div>

          <div className="p-6 rounded-2xl bg-white border border-slate-200 text-center shadow-xs">
            <h3 className="text-3xl sm:text-4xl font-extrabold text-mema-green-600 font-heading">
              96%
            </h3>
            <p className="text-xs text-slate-500 font-semibold mt-1">Graduate Employment Rate</p>
          </div>

          <div className="p-6 rounded-2xl bg-white border border-slate-200 text-center shadow-xs">
            <h3 className="text-3xl sm:text-4xl font-extrabold text-slate-900 font-heading">
              5,400+
            </h3>
            <p className="text-xs text-slate-500 font-semibold mt-1">Active Students</p>
          </div>

          <div className="p-6 rounded-2xl bg-white border border-slate-200 text-center shadow-xs">
            <h3 className="text-3xl sm:text-4xl font-extrabold text-mema-teal-800 font-heading">
              2
            </h3>
            <p className="text-xs text-slate-500 font-semibold mt-1">State-of-the-Art Campuses</p>
          </div>
        </div>
      </section>

      {/* Programme Explorer Section */}
      <section id="programmes" className="max-w-6xl mx-auto px-4 sm:px-8 space-y-8">
        <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
          <div>
            <span className="text-xs font-bold text-mema-green-600 uppercase tracking-widest">
              Degree Programmes
            </span>
            <h2 className="text-3xl font-bold text-slate-900 font-heading mt-1">
              Find Your Academic Path
            </h2>
            <p className="text-sm text-slate-500 mt-1">
              Industry-aligned curricula crafted in collaboration with leading technology companies
            </p>
          </div>

          <div className="w-full md:w-80">
            <Input
              placeholder="Search programmes..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              leftIcon={<Search className="h-4 w-4" />}
            />
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {filteredProgrammes.map((prog) => (
            <Card key={prog.id} className="p-6 hover:border-mema-teal-600 transition-all flex flex-col justify-between">
              <div className="space-y-3">
                <div className="flex items-center justify-between">
                  <Badge variant="default" className="font-mono font-bold">
                    {prog.code}
                  </Badge>
                  <Badge variant="outline" className="text-xs">
                    {prog.duration_years} Years Full-Time
                  </Badge>
                </div>
                <h3 className="text-xl font-bold text-slate-900">{programmeLabel(prog)}</h3>
                <p className="text-xs text-slate-600 leading-relaxed">
                  Offered by the {prog.department?.name}. Covers advanced software design, distributed systems, machine learning, and hands-on practicum attachments.
                </p>
                <div className="flex items-center gap-4 text-xs text-slate-500 pt-1">
                  <span className="flex items-center gap-1 font-semibold text-slate-700">
                    <BookOpen className="h-3.5 w-3.5 text-mema-teal-700" />
                    {prog.credit_units_required} Credit Units
                  </span>
                  <span className="flex items-center gap-1 font-semibold text-emerald-700">
                    <CheckCircle2 className="h-3.5 w-3.5" /> Direct & KUCCPS Entry
                  </span>
                </div>
              </div>

              <div className="pt-6 mt-4 border-t border-slate-100 flex items-center justify-between">
                <span className="text-xs font-bold text-mema-teal-800">
                  Tuition: KES 85,000 / Semester
                </span>
                <a href="http://localhost:3001">
                  <Button size="sm" className="bg-mema-teal-800 hover:bg-mema-teal-700 text-white gap-1 text-xs">
                    Apply Now <ArrowRight className="h-3.5 w-3.5" />
                  </Button>
                </a>
              </div>
            </Card>
          ))}
        </div>
      </section>

      {/* Why Choose Mema Section */}
      <section className="bg-slate-100 py-16 px-4 sm:px-8 border-y border-slate-200">
        <div className="max-w-6xl mx-auto space-y-12">
          <div className="text-center space-y-2">
            <h2 className="text-3xl font-bold text-slate-900 font-heading">
              Why Study at Mema University?
            </h2>
            <p className="text-sm text-slate-500 max-w-xl mx-auto">
              Our campus experience blends academic rigor, cutting-edge laboratories, and strong industry linkages.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div className="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-3">
              <div className="h-12 w-12 rounded-xl bg-mema-teal-50 text-mema-teal-800 flex items-center justify-center font-bold">
                <Building className="h-6 w-6" />
              </div>
              <h4 className="font-bold text-lg text-slate-900">Modern Research Facilities</h4>
              <p className="text-xs text-slate-600 leading-relaxed">
                Equipped with high-performance computing clusters, IoT testing labs, and gigabit campus-wide fiber connectivity.
              </p>
            </div>

            <div className="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-3">
              <div className="h-12 w-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold">
                <ShieldCheck className="h-6 w-6" />
              </div>
              <h4 className="font-bold text-lg text-slate-900">Guaranteed Industry Attachment</h4>
              <p className="text-xs text-slate-600 leading-relaxed">
                Mandatory supervised 3-month industrial practicum with leading tech hubs, financial institutions, and multinationals.
              </p>
            </div>

            <div className="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-3">
              <div className="h-12 w-12 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center font-bold">
                <Award className="h-6 w-6" />
              </div>
              <h4 className="font-bold text-lg text-slate-900">Scholarships & Financial Aid</h4>
              <p className="text-xs text-slate-600 leading-relaxed">
                Direct integration with HELB, institutional bursaries, and merit scholarships for top academic achievers.
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* CTA Box */}
      <section className="max-w-5xl mx-auto px-4 sm:px-8">
        <div className="rounded-3xl bg-gradient-to-r from-mema-teal-900 to-mema-green-800 text-white p-8 sm:p-12 text-center space-y-6 shadow-2xl">
          <h2 className="text-3xl sm:text-4xl font-extrabold font-heading">
            Begin Your University Journey Today
          </h2>
          <p className="text-sm sm:text-base text-mema-teal-100 max-w-xl mx-auto">
            Applications for September 2026 intake are currently open. Complete your online registration in less than 10 minutes.
          </p>
          <a href="http://localhost:3001" className="inline-block">
            <Button size="lg" className="bg-white text-mema-teal-950 hover:bg-slate-100 font-bold px-8 shadow-lg text-base">
              Start Online Application
            </Button>
          </a>
        </div>
      </section>
    </div>
  );
}
