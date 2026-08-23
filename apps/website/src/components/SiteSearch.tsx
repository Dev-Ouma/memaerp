'use client';

import React, { useState, useEffect, useRef, useCallback } from 'react';
import { Search, X, ArrowRight, BookOpen, Users, Shield, FileText, GraduationCap, FlaskConical, Building, Zap, Globe } from 'lucide-react';

// ─── Searchable Items ─────────────────────────────────────────────────────────
interface SearchItem {
  id: string;
  title: string;
  subtitle: string;
  category: 'Programme' | 'Portal' | 'Page' | 'Quick Link' | 'Research';
  href: string;
  icon: React.ReactNode;
  keywords: string[];
}

const searchItems: SearchItem[] = [
  // Programmes
  { id: 'bsc-cs', title: 'BSc. Computer Science', subtitle: 'Faculty of Computing & IT — 4 Years', category: 'Programme', href: 'http://localhost:3001', icon: <BookOpen style={{ width: '1rem', height: '1rem' }} />, keywords: ['computer', 'science', 'computing', 'cs', 'programming', 'software'] },
  { id: 'bsc-se', title: 'BSc. Software Engineering', subtitle: 'Faculty of Computing & IT — 4 Years', category: 'Programme', href: 'http://localhost:3001', icon: <BookOpen style={{ width: '1rem', height: '1rem' }} />, keywords: ['software', 'engineering', 'se', 'development', 'coding'] },
  { id: 'bsc-ds', title: 'BSc. Data Science & AI', subtitle: 'School of Data Sciences — 4 Years', category: 'Programme', href: 'http://localhost:3001', icon: <BookOpen style={{ width: '1rem', height: '1rem' }} />, keywords: ['data', 'science', 'ai', 'artificial', 'intelligence', 'machine', 'learning', 'ml'] },
  { id: 'bba', title: 'BBA Business Administration', subtitle: 'School of Business & Economics — 3 Years', category: 'Programme', href: 'http://localhost:3001', icon: <BookOpen style={{ width: '1rem', height: '1rem' }} />, keywords: ['business', 'administration', 'bba', 'management', 'economics'] },
  { id: 'msc-cs', title: 'MSc. Computer Science', subtitle: 'Postgraduate Studies — 2 Years', category: 'Programme', href: 'http://localhost:3001', icon: <GraduationCap style={{ width: '1rem', height: '1rem' }} />, keywords: ['masters', 'computer', 'postgraduate', 'msc'] },
  { id: 'msc-ai', title: 'MSc. Artificial Intelligence', subtitle: 'Postgraduate Studies — 2 Years', category: 'Programme', href: 'http://localhost:3001', icon: <GraduationCap style={{ width: '1rem', height: '1rem' }} />, keywords: ['masters', 'ai', 'artificial', 'intelligence', 'postgraduate', 'msc'] },

  // Portals
  { id: 'portal-apply', title: 'Apply for Admission', subtitle: 'September 2026 Intake — Online Application', category: 'Portal', href: 'http://localhost:3001', icon: <FileText style={{ width: '1rem', height: '1rem' }} />, keywords: ['apply', 'admission', 'application', 'intake', 'enroll', 'register'] },
  { id: 'portal-student', title: 'Student Portal', subtitle: 'Grades, Registration, Fee Statements', category: 'Portal', href: 'http://localhost:3002', icon: <Users style={{ width: '1rem', height: '1rem' }} />, keywords: ['student', 'portal', 'grades', 'results', 'gpa', 'registration', 'fee'] },
  { id: 'portal-staff', title: 'Staff & Admin ERP', subtitle: 'ERP Administration Console', category: 'Portal', href: 'http://localhost:3005', icon: <Shield style={{ width: '1rem', height: '1rem' }} />, keywords: ['staff', 'admin', 'erp', 'management', 'lecturer', 'teacher'] },

  // Pages
  { id: 'page-home', title: 'Home Page', subtitle: 'Mema University Main Website', category: 'Page', href: 'http://localhost:3000', icon: <Building style={{ width: '1rem', height: '1rem' }} />, keywords: ['home', 'main', 'website', 'landing'] },
  { id: 'page-about', title: 'About Mema University', subtitle: 'History, Mission, Vision, Accreditation', category: 'Page', href: 'http://localhost:3000#about', icon: <Building style={{ width: '1rem', height: '1rem' }} />, keywords: ['about', 'history', 'mission', 'vision', 'cue', 'accreditation'] },
  { id: 'page-programmes', title: 'All Programmes', subtitle: '42+ Degree Programmes Available', category: 'Page', href: 'http://localhost:3000#programmes', icon: <BookOpen style={{ width: '1rem', height: '1rem' }} />, keywords: ['programmes', 'courses', 'degrees', 'all'] },

  // Research
  { id: 'research-ai', title: 'AI & Machine Learning Lab', subtitle: '12 Active Research Projects', category: 'Research', href: '#', icon: <FlaskConical style={{ width: '1rem', height: '1rem' }} />, keywords: ['ai', 'machine', 'learning', 'research', 'lab', 'nlp'] },
  { id: 'research-cyber', title: 'Cybersecurity & Networks', subtitle: '8 Active Research Projects', category: 'Research', href: '#', icon: <Globe style={{ width: '1rem', height: '1rem' }} />, keywords: ['cybersecurity', 'security', 'network', 'blockchain'] },

  // Quick Links
  { id: 'ql-helb', title: 'HELB Loan Application', subtitle: 'Higher Education Loans Board Kenya', category: 'Quick Link', href: '#', icon: <Zap style={{ width: '1rem', height: '1rem' }} />, keywords: ['helb', 'loan', 'financial', 'aid', 'bursary', 'scholarship'] },
  { id: 'ql-fees', title: 'Fee Structure 2026', subtitle: 'Tuition & Accommodation Fees', category: 'Quick Link', href: '#', icon: <Zap style={{ width: '1rem', height: '1rem' }} />, keywords: ['fees', 'tuition', 'cost', 'price', 'payment', 'mpesa'] },
  { id: 'ql-contact', title: 'Contact Admissions', subtitle: '+254 20 892 000 | info@mema.ac.ke', category: 'Quick Link', href: 'tel:+254208920000', icon: <Zap style={{ width: '1rem', height: '1rem' }} />, keywords: ['contact', 'phone', 'email', 'call', 'admissions', 'help'] },
];

// Category colors
const catColors: Record<string, { bg: string; text: string; border: string }> = {
  Programme:  { bg: '#EBF5F8', text: '#0A3E50', border: '#AAD4E1' },
  Portal:     { bg: '#FEF5EC', text: '#E67E22', border: '#FBD0A7' },
  Page:       { bg: '#F0FDF4', text: '#1E8449', border: '#A6DFC0' },
  Research:   { bg: '#F5F3FF', text: '#7C3AED', border: '#C4B5FD' },
  'Quick Link': { bg: '#FFF7ED', text: '#D97706', border: '#FDE68A' },
};

export default function SiteSearch() {
  const [open, setOpen] = useState(false);
  const [query, setQuery] = useState('');
  const inputRef = useRef<HTMLInputElement>(null);

  // ⌘K / Ctrl+K keyboard shortcut
  useEffect(() => {
    const handler = (e: KeyboardEvent) => {
      if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        setOpen(prev => !prev);
      }
      if (e.key === 'Escape') setOpen(false);
    };
    window.addEventListener('keydown', handler);
    return () => window.removeEventListener('keydown', handler);
  }, []);

  // Auto-focus input when modal opens
  useEffect(() => {
    if (open) {
      setQuery('');
      setTimeout(() => inputRef.current?.focus(), 50);
    }
  }, [open]);

  // Filter results
  const getResults = useCallback(() => {
    if (!query.trim()) return searchItems.slice(0, 8); // show top 8 by default
    const q = query.toLowerCase().trim();
    return searchItems.filter(item =>
      item.title.toLowerCase().includes(q) ||
      item.subtitle.toLowerCase().includes(q) ||
      item.category.toLowerCase().includes(q) ||
      item.keywords.some(kw => kw.includes(q))
    );
  }, [query]);

  const results = getResults();

  // Group by category
  const grouped = results.reduce<Record<string, SearchItem[]>>((acc, item) => {
    if (!acc[item.category]) acc[item.category] = [];
    acc[item.category]!.push(item);
    return acc;
  }, {});

  if (!open) {
    return (
      <button
        onClick={() => setOpen(true)}
        title="Search (⌘K)"
        style={{
          display: 'flex',
          alignItems: 'center',
          gap: '0.5rem',
          background: 'rgba(255,255,255,0.08)',
          border: '1px solid rgba(255,255,255,0.15)',
          borderRadius: '10px',
          padding: '0.45rem 0.85rem',
          cursor: 'pointer',
          color: 'rgba(255,255,255,0.6)',
          fontSize: '0.82rem',
          fontWeight: 600,
          fontFamily: 'inherit',
          transition: 'all 0.2s',
        }}
        onMouseEnter={(e) => {
          e.currentTarget.style.background = 'rgba(255,255,255,0.14)';
          e.currentTarget.style.borderColor = 'rgba(255,255,255,0.3)';
          e.currentTarget.style.color = '#fff';
        }}
        onMouseLeave={(e) => {
          e.currentTarget.style.background = 'rgba(255,255,255,0.08)';
          e.currentTarget.style.borderColor = 'rgba(255,255,255,0.15)';
          e.currentTarget.style.color = 'rgba(255,255,255,0.6)';
        }}
      >
        <Search style={{ width: '0.9rem', height: '0.9rem' }} />
        Search
        <kbd style={{
          fontSize: '0.65rem',
          fontWeight: 700,
          background: 'rgba(255,255,255,0.1)',
          border: '1px solid rgba(255,255,255,0.15)',
          borderRadius: '5px',
          padding: '0.15rem 0.4rem',
          marginLeft: '0.25rem',
          fontFamily: 'inherit',
          color: 'rgba(255,255,255,0.5)',
        }}>
          ⌘K
        </kbd>
      </button>
    );
  }

  return (
    <>
      {/* Backdrop */}
      <div
        style={{
          position: 'fixed',
          inset: 0,
          zIndex: 10010,
          background: 'rgba(7, 45, 58, 0.75)',
          backdropFilter: 'blur(12px)',
          animation: 'fadeIn 0.15s ease-out',
        }}
        onClick={() => setOpen(false)}
      />

      {/* Search Modal */}
      <div
        style={{
          position: 'fixed',
          top: '15%',
          left: '50%',
          transform: 'translateX(-50%)',
          zIndex: 10011,
          width: '100%',
          maxWidth: '620px',
          background: '#fff',
          borderRadius: '20px',
          boxShadow: '0 25px 80px rgba(0,0,0,0.35)',
          border: '1px solid rgba(255,255,255,0.2)',
          overflow: 'hidden',
          animation: 'fadeInUp 0.2s ease-out',
        }}
      >
        {/* Search Input Row */}
        <div style={{
          display: 'flex',
          alignItems: 'center',
          gap: '0.75rem',
          padding: '1rem 1.25rem',
          borderBottom: '1px solid #e2e8f0',
        }}>
          <Search style={{ width: '1.25rem', height: '1.25rem', color: '#94a3b8', flexShrink: 0 }} />
          <input
            ref={inputRef}
            type="text"
            value={query}
            onChange={(e) => setQuery(e.target.value)}
            placeholder="Search programmes, portals, pages…"
            style={{
              flex: 1,
              border: 'none',
              outline: 'none',
              fontSize: '1rem',
              fontWeight: 600,
              color: '#0f172a',
              fontFamily: 'inherit',
              background: 'none',
            }}
          />
          {query && (
            <button
              onClick={() => setQuery('')}
              style={{
                background: '#f1f5f9',
                border: 'none',
                borderRadius: '6px',
                padding: '0.3rem',
                cursor: 'pointer',
                display: 'flex',
                color: '#64748b',
              }}
            >
              <X style={{ width: '0.85rem', height: '0.85rem' }} />
            </button>
          )}
          <kbd style={{
            fontSize: '0.65rem',
            fontWeight: 700,
            background: '#f1f5f9',
            border: '1px solid #e2e8f0',
            borderRadius: '5px',
            padding: '0.2rem 0.5rem',
            color: '#94a3b8',
            fontFamily: 'inherit',
            flexShrink: 0,
          }}>
            ESC
          </kbd>
        </div>

        {/* Results */}
        <div style={{
          maxHeight: '420px',
          overflowY: 'auto',
          padding: '0.5rem',
        }}>
          {results.length === 0 ? (
            <div style={{
              padding: '3rem 2rem',
              textAlign: 'center',
              color: '#94a3b8',
            }}>
              <Search style={{ width: '2.5rem', height: '2.5rem', marginBottom: '0.75rem', opacity: 0.3 }} />
              <div style={{ fontWeight: 700, fontSize: '0.95rem', color: '#475569' }}>No results found</div>
              <div style={{ fontSize: '0.82rem', marginTop: '0.35rem' }}>
                Try searching for &quot;computer science&quot;, &quot;apply&quot;, or &quot;fees&quot;
              </div>
            </div>
          ) : (
            Object.entries(grouped).map(([category, items]) => (
              <div key={category} style={{ marginBottom: '0.5rem' }}>
                <div style={{
                  fontSize: '0.68rem',
                  fontWeight: 800,
                  color: '#94a3b8',
                  textTransform: 'uppercase',
                  letterSpacing: '0.08em',
                  padding: '0.5rem 0.75rem 0.35rem',
                }}>
                  {category}
                </div>
                {items.map((item) => {
                  const colors = catColors[item.category] ?? catColors['Page']!;
                  return (
                    <a
                      key={item.id}
                      href={item.href}
                      onClick={() => setOpen(false)}
                      style={{
                        display: 'flex',
                        alignItems: 'center',
                        gap: '0.85rem',
                        padding: '0.75rem',
                        borderRadius: '12px',
                        textDecoration: 'none',
                        color: '#0f172a',
                        transition: 'all 0.15s',
                      }}
                      onMouseEnter={(e) => {
                        e.currentTarget.style.background = '#f8fafc';
                      }}
                      onMouseLeave={(e) => {
                        e.currentTarget.style.background = 'transparent';
                      }}
                    >
                      <div style={{
                        width: '36px',
                        height: '36px',
                        borderRadius: '10px',
                        background: colors.bg,
                        border: `1px solid ${colors.border}`,
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        color: colors.text,
                        flexShrink: 0,
                      }}>
                        {item.icon}
                      </div>
                      <div style={{ flex: 1, minWidth: 0 }}>
                        <div style={{ fontWeight: 700, fontSize: '0.88rem', lineHeight: 1.3 }}>
                          {item.title}
                        </div>
                        <div style={{ fontSize: '0.75rem', color: '#94a3b8', fontWeight: 600, marginTop: '0.1rem', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
                          {item.subtitle}
                        </div>
                      </div>
                      <ArrowRight style={{ width: '0.85rem', height: '0.85rem', color: '#cbd5e1', flexShrink: 0 }} />
                    </a>
                  );
                })}
              </div>
            ))
          )}
        </div>

        {/* Footer */}
        <div style={{
          borderTop: '1px solid #f1f5f9',
          padding: '0.65rem 1.25rem',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
          fontSize: '0.72rem',
          color: '#94a3b8',
          fontWeight: 600,
        }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
            <span>↑↓ Navigate</span>
            <span>↵ Open</span>
            <span>esc Close</span>
          </div>
          <div style={{ display: 'flex', alignItems: 'center', gap: '0.35rem' }}>
            <Search style={{ width: '0.7rem', height: '0.7rem' }} />
            Mema Search
          </div>
        </div>
      </div>
    </>
  );
}
