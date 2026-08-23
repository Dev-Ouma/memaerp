import React from 'react';
import './globals.css';
import { GraduationCap, Phone, Mail } from 'lucide-react';

export const metadata = {
  title: 'Admissions & Online Application — Mema University',
  description: 'Apply for undergraduate, diploma and postgraduate programmes at Mema University.',
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en">
      <body className="min-h-screen flex flex-col bg-slate-50 text-slate-900 antialiased">
        {/* Header */}
        <header className="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200">
          <div className="max-w-7xl mx-auto px-4 sm:px-8 h-18 flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className="h-10 w-10 rounded-xl bg-gradient-to-br from-mema-teal-700 to-mema-green-600 flex items-center justify-center text-white shadow-md">
                <GraduationCap className="h-6 w-6" />
              </div>
              <div>
                <span className="font-heading font-bold text-lg text-mema-teal-900 tracking-tight block">
                  MEMA UNIVERSITY
                </span>
                <span className="text-xs text-slate-500 block font-medium">
                  Admissions & Applicant Portal
                </span>
              </div>
            </div>

            <div className="flex items-center gap-4 text-xs font-semibold text-slate-600">
              <span className="hidden sm:flex items-center gap-1">
                <Phone className="h-3.5 w-3.5 text-mema-green-600" /> +254 20 892 000
              </span>
              <span className="hidden sm:flex items-center gap-1">
                <Mail className="h-3.5 w-3.5 text-mema-teal-700" /> admissions@mema.ac.ke
              </span>
            </div>
          </div>
        </header>

        {/* Content */}
        <main className="flex-1 max-w-5xl w-full mx-auto p-4 sm:p-8">
          {children}
        </main>

        {/* Footer */}
        <footer className="border-t border-slate-200 bg-white py-6 text-center text-xs text-slate-500">
          <p>© {new Date().getFullYear()} Mema University. All rights reserved. Registered with CUE & KUCCPS.</p>
        </footer>
      </body>
    </html>
  );
}
