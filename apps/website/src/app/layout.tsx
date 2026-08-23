'use client';

import React, { useState } from 'react';
import './globals.css';
import { GraduationCap, FileText, ArrowRight, ChevronDown, Phone } from 'lucide-react';
import { Quicksand, Nunito } from 'next/font/google';

const quicksand = Quicksand({
  subsets: ['latin'],
  variable: '--font-quicksand',
  display: 'swap',
});

const nunito = Nunito({
  subsets: ['latin'],
  variable: '--font-nunito',
  display: 'swap',
});

// ─── Menu Configuration matching provided samples ───────────────────────────
interface MenuItem {
  label: string;
  href: string;
}

interface NavSection {
  title: string;
  href?: string;
  items?: MenuItem[];
}

const navSections: NavSection[] = [
  {
    title: 'ABOUT US',
    items: [
      { label: 'About MEMA', href: '#about' },
      { label: 'MEMA Uniqueness', href: '#about' },
      { label: 'The Chancellor', href: '#about' },
      { label: 'Vice Chancellor', href: '#about' },
      { label: 'The Governing Council', href: '#about' },
      { label: 'Downloads', href: '#about' },
      { label: 'Contact Us', href: '#contact' },
      { label: 'Complaints And Compliments', href: '#contact' },
      { label: 'Service Charter', href: '#about' },
      { label: 'Frequent Asked Questions', href: '#about' },
    ],
  },
  {
    title: 'PROGRAMMES',
    items: [
      { label: 'Schools', href: '#programmes' },
      { label: 'Programmes', href: '#programmes' },
      { label: 'Professional Development Courses', href: '#programmes' },
      { label: 'Timetables', href: '#programmes' },
      { label: 'Peer Learners', href: '#programmes' },
      { label: 'How To Apply', href: 'http://localhost:3001' },
      { label: 'ICT Professional Certification', href: '#programmes' },
      { label: 'KUCCPS List', href: 'http://localhost:3001' },
    ],
  },
  {
    title: 'RESEARCH',
    href: '#research',
  },
  {
    title: 'LIBRARY',
    items: [
      { label: 'MEMA Library', href: '#about' },
      { label: 'Online Catalogue', href: '#about' },
      { label: 'Information Literacy', href: '#about' },
      { label: 'E-Resources', href: '#about' },
      { label: 'Plagiarism Checker', href: '#about' },
    ],
  },
  {
    title: 'MEDIA DESK',
    items: [
      { label: 'News & Press Releases', href: '#news' },
      { label: 'Gallery & Media', href: '#about' },
      { label: 'University Announcements', href: '#news' },
      { label: 'Publications', href: '#research' },
      { label: 'Event Coverage', href: '#events' },
    ],
  },
];

// ─── Navbar Component ────────────────────────────────────────────────────────
function Navbar() {
  const [openMenu, setOpenMenu] = useState<string | null>(null);
  let closeTimer: ReturnType<typeof setTimeout> | null = null;

  const handleEnter = (title: string) => {
    if (closeTimer) clearTimeout(closeTimer);
    setOpenMenu(title);
  };

  const handleLeave = () => {
    closeTimer = setTimeout(() => setOpenMenu(null), 150);
  };

  return (
    <header className="site-header">
      <div className="header-inner">
        {/* Logo (Home) */}
        <a href="http://localhost:3000" className="header-logo" title="Mema University Home">
          <div className="header-logo-icon">
            <GraduationCap style={{ width: '24px', height: '24px', color: '#fff' }} />
          </div>
          <div>
            <div className="header-logo-name">MEMA UNIVERSITY</div>
            <div className="header-logo-tagline">Excellence in Research &amp; Technology</div>
          </div>
        </a>

        {/* Clean Nav Links */}
        <nav className="main-nav">
          {navSections.map((section) => {
            const hasDropdown = section.items && section.items.length > 0;
            const isOpen = openMenu === section.title;

            if (!hasDropdown) {
              return (
                <a
                  key={section.title}
                  href={section.href ?? '#'}
                  className="nav-link-btn"
                >
                  {section.title}
                </a>
              );
            }

            return (
              <div
                key={section.title}
                className="nav-dropdown-wrap"
                onMouseEnter={() => handleEnter(section.title)}
                onMouseLeave={handleLeave}
              >
                <button
                  className={`nav-link-btn${isOpen ? ' active' : ''}`}
                  onClick={() => setOpenMenu(isOpen ? null : section.title)}
                  aria-expanded={isOpen}
                >
                  <span>{section.title}</span>
                  <ChevronDown className={`nav-chevron${isOpen ? ' open' : ''}`} />
                </button>

                {isOpen && (
                  <div
                    className="clean-dropdown"
                    onMouseEnter={() => handleEnter(section.title)}
                    onMouseLeave={handleLeave}
                  >
                    <ul className="clean-dropdown-list">
                      {section.items!.map((item, idx) => (
                        <li key={idx} className="clean-dropdown-item">
                          <a
                            href={item.href}
                            className="clean-dropdown-link"
                            onClick={() => setOpenMenu(null)}
                          >
                            {item.label}
                          </a>
                        </li>
                      ))}
                    </ul>
                  </div>
                )}
              </div>
            );
          })}
        </nav>

        {/* Right Action: Clean Apply Now Button */}
        <div className="header-actions">
          <a href="http://localhost:3001" className="apply-nav-btn">
            <FileText style={{ width: '0.9rem', height: '0.9rem' }} />
            <span>Apply Now</span>
            <ArrowRight style={{ width: '0.9rem', height: '0.9rem' }} />
          </a>
        </div>
      </div>
    </header>
  );
}

// ─── Root Layout ─────────────────────────────────────────────────────────────
export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en" suppressHydrationWarning className={`${quicksand.variable} ${nunito.variable}`}>
      <head>
        <title>Mema University — Inspiring Innovation &amp; Academic Excellence</title>
        <meta name="description" content="Mema University official portal, accredited programmes, admissions, and research." />
      </head>
      <body suppressHydrationWarning style={{ margin: 0, padding: 0, fontFamily: "var(--font-quicksand, 'Quicksand', 'Nunito', sans-serif)" }}>

        {/* SCROLL PROGRESS BAR */}
        <div id="scroll-progress-bar" aria-hidden="true" />

        {/* STICKY NAVBAR */}
        <Navbar />

        <main>{children}</main>

        {/* FOOTER */}
        <footer style={{ background: '#051c24', color: '#fff', paddingTop: '4rem', paddingBottom: '2rem', borderTop: '1px solid rgba(255,255,255,0.08)' }}>
          <div style={{ maxWidth: '1280px', margin: '0 auto', padding: '0 2rem 3rem', display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '3rem' }}>
            <div>
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.6rem', marginBottom: '1rem' }}>
                <GraduationCap style={{ width: '1.5rem', height: '1.5rem', color: '#E67E22' }} />
                <span style={{ fontWeight: 900, fontSize: '1rem', letterSpacing: '0.05em' }}>MEMA UNIVERSITY</span>
              </div>
              <p style={{ fontSize: '0.83rem', color: 'rgba(255,255,255,0.6)', lineHeight: 1.7, margin: '0 0 1rem' }}>
                Pioneering education, computing, and transformative research in East Africa since 2008.
              </p>
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.35rem', fontSize: '0.8rem', color: 'rgba(255,255,255,0.6)' }}>
                <Phone style={{ width: '0.8rem', height: '0.8rem' }} /> +254 20 892 000
              </div>
            </div>
            <div>
              <h4 style={{ fontWeight: 700, fontSize: '0.85rem', color: '#fff', marginBottom: '1rem', textTransform: 'uppercase', letterSpacing: '0.08em', marginTop: 0 }}>Academic Schools</h4>
              <ul style={{ listStyle: 'none', padding: 0, margin: 0, display: 'flex', flexDirection: 'column', gap: '0.6rem' }}>
                {['Faculty of Computing & IT', 'School of Business & Economics', 'School of Engineering', 'Postgraduate Studies'].map((s, i) => (
                  <li key={i} style={{ fontSize: '0.82rem', color: 'rgba(255,255,255,0.6)' }}>{s}</li>
                ))}
              </ul>
            </div>
            <div>
              <h4 style={{ fontWeight: 700, fontSize: '0.85rem', color: '#fff', marginBottom: '1rem', textTransform: 'uppercase', letterSpacing: '0.08em', marginTop: 0 }}>Quick Portals</h4>
              <ul style={{ listStyle: 'none', padding: 0, margin: 0, display: 'flex', flexDirection: 'column', gap: '0.6rem' }}>
                <li><a href="http://localhost:3002" className="footer-link">Student Information Portal</a></li>
                <li><a href="http://localhost:3005" className="footer-link">ERP Administration</a></li>
                <li><a href="http://localhost:3001" className="footer-link">Applicant Online System</a></li>
              </ul>
            </div>
            <div>
              <h4 style={{ fontWeight: 700, fontSize: '0.85rem', color: '#fff', marginBottom: '1rem', textTransform: 'uppercase', letterSpacing: '0.08em', marginTop: 0 }}>Contact</h4>
              <p style={{ fontSize: '0.82rem', color: 'rgba(255,255,255,0.6)', margin: '0 0 0.4rem' }}>Main Campus, Nairobi, Kenya</p>
              <p style={{ fontSize: '0.82rem', color: 'rgba(255,255,255,0.6)', margin: '0 0 0.4rem' }}>Tel: +254 20 892 000</p>
              <p style={{ fontSize: '0.82rem', color: 'rgba(255,255,255,0.6)', margin: 0 }}>info@mema.ac.ke</p>
            </div>
          </div>
          <div style={{ maxWidth: '1280px', margin: '0 auto', padding: '2rem 2rem 0', borderTop: '1px solid rgba(255,255,255,0.08)', textAlign: 'center', fontSize: '0.78rem', color: 'rgba(255,255,255,0.4)' }}>
            © 2026 Mema University. Commission for University Education (CUE) Accredited. All Rights Reserved.
          </div>
        </footer>
      </body>
    </html>
  );
}
