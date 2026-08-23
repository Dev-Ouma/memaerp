import React from 'react';
import './globals.css';
import { GraduationCap, ArrowRight, User } from 'lucide-react';

export const metadata = {
  title: 'Mema University — Inspiring Innovation & Academic Excellence',
  description: 'Mema University official portal, accredited programmes, admissions, and research.',
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en">
      <body className="min-h-screen flex flex-col bg-slate-50 text-slate-900 antialiased">
        {/* Navigation Bar */}
        <header className="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-200">
          <div className="max-w-7xl mx-auto px-4 sm:px-8 h-20 flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className="h-11 w-11 rounded-xl bg-gradient-to-br from-mema-teal-700 to-mema-green-600 flex items-center justify-center text-white shadow-md">
                <GraduationCap className="h-7 w-7" />
              </div>
              <div>
                <span className="font-heading font-bold text-xl text-mema-teal-900 tracking-tight block">
                  MEMA UNIVERSITY
                </span>
                <span className="text-[11px] text-mema-teal-700 block font-semibold tracking-wider uppercase">
                  Excellence in Research & Technology
                </span>
              </div>
            </div>

            <nav className="hidden md:flex items-center gap-6 text-sm font-medium text-slate-700">
              <a href="#programmes" className="hover:text-mema-teal-800 transition-colors">Programmes</a>
              <a href="#admissions" className="hover:text-mema-teal-800 transition-colors">Admissions</a>
              <a href="#campuses" className="hover:text-mema-teal-800 transition-colors">Campuses</a>
              <a href="#research" className="hover:text-mema-teal-800 transition-colors">Research & Innovation</a>
            </nav>

            <div className="flex items-center gap-3">
              <a
                href="http://localhost:3002"
                className="hidden sm:inline-flex items-center gap-1.5 text-xs font-semibold text-mema-teal-900 hover:text-mema-teal-700 px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50"
              >
                <User className="h-3.5 w-3.5" /> Student Portal
              </a>
              <a
                href="http://localhost:3001"
                className="inline-flex items-center gap-1.5 text-xs font-bold bg-mema-green-600 hover:bg-mema-green-700 text-white px-4 py-2.5 rounded-lg shadow-sm transition-all"
              >
                Apply Online <ArrowRight className="h-3.5 w-3.5" />
              </a>
            </div>
          </div>
        </header>

        {/* Content */}
        <main className="flex-1">{children}</main>

        {/* Footer */}
        <footer className="bg-mema-teal-950 text-white pt-16 pb-12 border-t border-mema-teal-900">
          <div className="max-w-7xl mx-auto px-4 sm:px-8 grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
            <div className="space-y-3">
              <div className="flex items-center gap-2">
                <GraduationCap className="h-6 w-6 text-mema-green-400" />
                <span className="font-heading font-bold text-lg">MEMA UNIVERSITY</span>
              </div>
              <p className="text-xs text-mema-teal-200 leading-relaxed">
                Pioneering education, computer sciences, and transformative research in East Africa.
              </p>
            </div>

            <div>
              <h4 className="font-bold text-sm text-white mb-3">Academic Schools</h4>
              <ul className="space-y-2 text-xs text-mema-teal-200">
                <li>Faculty of Computing & IT</li>
                <li>School of Business & Economics</li>
                <li>School of Engineering</li>
                <li>Postgraduate Studies</li>
              </ul>
            </div>

            <div>
              <h4 className="font-bold text-sm text-white mb-3">Quick Portals</h4>
              <ul className="space-y-2 text-xs text-mema-teal-200">
                <li><a href="http://localhost:3002" className="hover:underline">Student Information Portal</a></li>
                <li><a href="http://localhost:3005" className="hover:underline">ERP Administration (Staff)</a></li>
                <li><a href="http://localhost:3001" className="hover:underline">Applicant Online System</a></li>
                <li><a href="#" className="hover:underline">E-Learning / Moodle Sync</a></li>
              </ul>
            </div>

            <div>
              <h4 className="font-bold text-sm text-white mb-3">Contact & Inquiries</h4>
              <p className="text-xs text-mema-teal-200">Main Campus, Nairobi</p>
              <p className="text-xs text-mema-teal-200 mt-1">Tel: +254 20 892 000</p>
              <p className="text-xs text-mema-teal-200 mt-1">Email: info@mema.ac.ke</p>
            </div>
          </div>

          <div className="max-w-7xl mx-auto px-4 sm:px-8 pt-8 border-t border-mema-teal-900 text-center text-xs text-mema-teal-300">
            <p>© {new Date().getFullYear()} Mema University. Commission for University Education (CUE) Accredited.</p>
          </div>
        </footer>
      </body>
    </html>
  );
}
