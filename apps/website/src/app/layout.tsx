import React from 'react';
import './globals.css';
import { GraduationCap, Home, BookOpen, Users, Shield, FileText, ArrowRight, Phone } from 'lucide-react';
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
  title: 'Mema University — Inspiring Innovation & Academic Excellence',
  description: 'Mema University official portal, accredited programmes, admissions, and research.',
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en" className={`${quicksand.variable} ${nunito.variable}`}>
      <head>
        <style>{`
          .nav-link { display:inline-flex;align-items:center;gap:0.35rem;color:rgba(255,255,255,0.85);font-size:0.875rem;font-weight:600;padding:0.4rem 0.85rem;border-radius:6px;text-decoration:none;transition:all 0.2s; }
          .nav-link:hover { background:rgba(255,255,255,0.1);color:#fff; }
          .portal-link { display:inline-flex;align-items:center;gap:0.35rem;color:rgba(255,255,255,0.85);font-size:0.78rem;font-weight:600;padding:0.4rem 0.9rem;border-radius:6px;border:1px solid rgba(255,255,255,0.2);text-decoration:none;background:transparent;transition:all 0.2s; }
          .portal-link:hover { background:rgba(255,255,255,0.1);border-color:rgba(255,255,255,0.4);color:#fff; }
          .apply-nav-btn { display:inline-flex;align-items:center;gap:0.4rem;color:#fff;font-size:0.85rem;font-weight:700;padding:0.5rem 1.25rem;border-radius:6px;background:#E67E22;text-decoration:none;box-shadow:0 2px 10px rgba(230,126,34,0.4);transition:all 0.2s; }
          .apply-nav-btn:hover { background:#d0691a;transform:translateY(-1px);box-shadow:0 4px 16px rgba(230,126,34,0.5); }
          .footer-link { font-size:0.82rem;color:rgba(255,255,255,0.6);text-decoration:none;transition:color 0.2s; }
          .footer-link:hover { color:#fff; }
        `}</style>
      </head>
      <body style={{ margin: 0, padding: 0, fontFamily: "var(--font-quicksand, 'Quicksand', 'Nunito', sans-serif)" }}>

        {/* STICKY NAVBAR */}
        <header style={{ position:'sticky', top:0, zIndex:100, background:'rgba(10,62,80,0.97)', backdropFilter:'blur(12px)', borderBottom:'1px solid rgba(255,255,255,0.1)', boxShadow:'0 2px 20px rgba(0,0,0,0.2)' }}>
          <div style={{ maxWidth:'1280px', margin:'0 auto', padding:'0 2rem', height:'72px', display:'flex', alignItems:'center', justifyContent:'space-between', gap:'1rem' }}>

            <a href="http://localhost:3000" style={{ display:'flex', alignItems:'center', gap:'0.75rem', textDecoration:'none' }}>
              <div style={{ width:'44px', height:'44px', borderRadius:'10px', background:'linear-gradient(135deg, #1E8449, #E67E22)', display:'flex', alignItems:'center', justifyContent:'center', flexShrink:0 }}>
                <GraduationCap style={{ width:'24px', height:'24px', color:'#fff' }} />
              </div>
              <div>
                <div style={{ fontWeight:900, fontSize:'1rem', color:'#fff', letterSpacing:'0.05em', lineHeight:1.1 }}>MEMA UNIVERSITY</div>
                <div style={{ fontWeight:600, fontSize:'0.65rem', color:'rgba(255,255,255,0.6)', textTransform:'uppercase', letterSpacing:'0.1em' }}>Excellence in Research &amp; Technology</div>
              </div>
            </a>

            <nav style={{ display:'flex', alignItems:'center', gap:'0.25rem' }}>
              <a href="http://localhost:3000" className="nav-link"><Home style={{ width:'0.9rem', height:'0.9rem' }} /> Home</a>
              <a href="#programmes" className="nav-link"><BookOpen style={{ width:'0.9rem', height:'0.9rem' }} /> Programmes</a>
              <a href="#" className="nav-link">About</a>
              <a href="#" className="nav-link">Research</a>
            </nav>

            <div style={{ display:'flex', alignItems:'center', gap:'0.5rem' }}>
              <a href="http://localhost:3002" className="portal-link"><Users style={{ width:'0.8rem', height:'0.8rem' }} /> Students</a>
              <a href="http://localhost:3005" className="portal-link"><Shield style={{ width:'0.8rem', height:'0.8rem' }} /> Staff</a>
              <a href="http://localhost:3001" className="apply-nav-btn"><FileText style={{ width:'0.85rem', height:'0.85rem' }} /> Apply Now <ArrowRight style={{ width:'0.85rem', height:'0.85rem' }} /></a>
            </div>
          </div>
        </header>

        <main>{children}</main>

        {/* FOOTER */}
        <footer style={{ background:'#051c24', color:'#fff', paddingTop:'4rem', paddingBottom:'2rem', borderTop:'1px solid rgba(255,255,255,0.08)' }}>
          <div style={{ maxWidth:'1280px', margin:'0 auto', padding:'0 2rem 3rem', display:'grid', gridTemplateColumns:'repeat(auto-fit, minmax(200px, 1fr))', gap:'3rem' }}>
            <div>
              <div style={{ display:'flex', alignItems:'center', gap:'0.6rem', marginBottom:'1rem' }}>
                <GraduationCap style={{ width:'1.5rem', height:'1.5rem', color:'#E67E22' }} />
                <span style={{ fontWeight:900, fontSize:'1rem', letterSpacing:'0.05em' }}>MEMA UNIVERSITY</span>
              </div>
              <p style={{ fontSize:'0.83rem', color:'rgba(255,255,255,0.6)', lineHeight:1.7, margin:'0 0 1rem' }}>Pioneering education, computing, and transformative research in East Africa since 2008.</p>
              <div style={{ display:'flex', alignItems:'center', gap:'0.35rem', fontSize:'0.8rem', color:'rgba(255,255,255,0.6)' }}>
                <Phone style={{ width:'0.8rem', height:'0.8rem' }} /> +254 20 892 000
              </div>
            </div>
            <div>
              <h4 style={{ fontWeight:700, fontSize:'0.85rem', color:'#fff', marginBottom:'1rem', textTransform:'uppercase', letterSpacing:'0.08em', marginTop:0 }}>Academic Schools</h4>
              <ul style={{ listStyle:'none', padding:0, margin:0, display:'flex', flexDirection:'column', gap:'0.6rem' }}>
                {['Faculty of Computing & IT','School of Business & Economics','School of Engineering','Postgraduate Studies'].map((s,i) => <li key={i} style={{ fontSize:'0.82rem', color:'rgba(255,255,255,0.6)' }}>{s}</li>)}
              </ul>
            </div>
            <div>
              <h4 style={{ fontWeight:700, fontSize:'0.85rem', color:'#fff', marginBottom:'1rem', textTransform:'uppercase', letterSpacing:'0.08em', marginTop:0 }}>Quick Portals</h4>
              <ul style={{ listStyle:'none', padding:0, margin:0, display:'flex', flexDirection:'column', gap:'0.6rem' }}>
                <li><a href="http://localhost:3002" className="footer-link">Student Information Portal</a></li>
                <li><a href="http://localhost:3005" className="footer-link">ERP Administration</a></li>
                <li><a href="http://localhost:3001" className="footer-link">Applicant Online System</a></li>
              </ul>
            </div>
            <div>
              <h4 style={{ fontWeight:700, fontSize:'0.85rem', color:'#fff', marginBottom:'1rem', textTransform:'uppercase', letterSpacing:'0.08em', marginTop:0 }}>Contact</h4>
              <p style={{ fontSize:'0.82rem', color:'rgba(255,255,255,0.6)', margin:'0 0 0.4rem' }}>Main Campus, Nairobi, Kenya</p>
              <p style={{ fontSize:'0.82rem', color:'rgba(255,255,255,0.6)', margin:'0 0 0.4rem' }}>Tel: +254 20 892 000</p>
              <p style={{ fontSize:'0.82rem', color:'rgba(255,255,255,0.6)', margin:0 }}>info@mema.ac.ke</p>
            </div>
          </div>
          <div style={{ maxWidth:'1280px', margin:'0 auto', padding:'2rem 2rem 0', borderTop:'1px solid rgba(255,255,255,0.08)', textAlign:'center', fontSize:'0.78rem', color:'rgba(255,255,255,0.4)' }}>
            © {new Date().getFullYear()} Mema University. Commission for University Education (CUE) Accredited. All Rights Reserved.
          </div>
        </footer>
      </body>
    </html>
  );
}
