import React from 'react';
import './globals.css';
import { GraduationCap, Home, Users, Shield, FileText, ArrowRight } from 'lucide-react';
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

export const metadata = {
  title: 'Admissions & Online Application — Mema University',
  description: 'Apply for undergraduate, diploma and postgraduate programmes at Mema University.',
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en" className={`${quicksand.variable} ${nunito.variable}`}>
      <head>
        <style>{`
          .app-nav-link { display:inline-flex;align-items:center;gap:0.3rem;color:rgba(255,255,255,0.85);font-size:0.82rem;font-weight:600;padding:0.35rem 0.8rem;border-radius:6px;text-decoration:none;transition:all 0.2s; }
          .app-nav-link:hover { background:rgba(255,255,255,0.1);color:#fff; }
          .app-apply-btn { display:inline-flex;align-items:center;gap:0.35rem;background:#1E8449;color:#fff;font-size:0.82rem;font-weight:700;padding:0.4rem 1.1rem;border-radius:6px;text-decoration:none;box-shadow:0 2px 8px rgba(30,132,73,0.35);transition:all 0.2s; }
          .app-apply-btn:hover { background:#16703d;transform:translateY(-1px); }
        `}</style>
      </head>
      <body style={{ margin:0, padding:0, minHeight:'100vh', display:'flex', flexDirection:'column', fontFamily:"var(--font-quicksand,'Quicksand'),var(--font-nunito,'Nunito'),system-ui,sans-serif", background:'#f8fafc', color:'#0f172a' }}>

        {/* ── HEADER ── */}
        <header style={{ position:'sticky', top:0, zIndex:100, background:'rgba(10,62,80,0.97)', backdropFilter:'blur(12px)', borderBottom:'1px solid rgba(255,255,255,0.1)', boxShadow:'0 2px 16px rgba(0,0,0,0.2)' }}>
          <div style={{ maxWidth:'1200px', margin:'0 auto', padding:'0 2rem', height:'66px', display:'flex', alignItems:'center', justifyContent:'space-between', gap:'1rem' }}>

            {/* Logo */}
            <a href="http://localhost:3000" style={{ display:'flex', alignItems:'center', gap:'0.7rem', textDecoration:'none' }}>
              <div style={{ width:'40px', height:'40px', borderRadius:'9px', background:'linear-gradient(135deg,#1E8449,#E67E22)', display:'flex', alignItems:'center', justifyContent:'center', flexShrink:0 }}>
                <GraduationCap style={{ width:'22px', height:'22px', color:'#fff' }} />
              </div>
              <div>
                <div style={{ fontWeight:900, fontSize:'0.95rem', color:'#fff', letterSpacing:'0.04em', lineHeight:1.1 }}>MEMA UNIVERSITY</div>
                <div style={{ fontWeight:600, fontSize:'0.6rem', color:'rgba(255,255,255,0.55)', textTransform:'uppercase', letterSpacing:'0.1em' }}>Admissions &amp; Applicant Portal</div>
              </div>
            </a>

            {/* Nav */}
            <nav style={{ display:'flex', alignItems:'center', gap:'0.15rem' }}>
              <a href="http://localhost:3000" className="app-nav-link"><Home style={{ width:'0.85rem', height:'0.85rem' }} /> Home</a>
              <a href="http://localhost:3002" className="app-nav-link"><Users style={{ width:'0.85rem', height:'0.85rem' }} /> Student Portal</a>
              <a href="http://localhost:3005" className="app-nav-link"><Shield style={{ width:'0.85rem', height:'0.85rem' }} /> Staff Portal</a>
              <a href="http://localhost:3001" className="app-apply-btn"><FileText style={{ width:'0.85rem', height:'0.85rem' }} /> Apply Now <ArrowRight style={{ width:'0.85rem', height:'0.85rem' }} /></a>
            </nav>
          </div>
        </header>

        {/* ── CONTENT ── */}
        <main style={{ flex:1, maxWidth:'1200px', width:'100%', margin:'0 auto', padding:'2rem' }}>
          {children}
        </main>

        {/* ── FOOTER ── */}
        <footer style={{ background:'#051c24', color:'rgba(255,255,255,0.5)', padding:'1.5rem 2rem', textAlign:'center', fontSize:'0.78rem', borderTop:'1px solid rgba(255,255,255,0.07)' }}>
          © {new Date().getFullYear()} Mema University. All rights reserved. Registered with CUE &amp; KUCCPS.
        </footer>
      </body>
    </html>
  );
}
