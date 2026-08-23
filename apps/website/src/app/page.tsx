'use client';

import React, { useState, useEffect, useRef } from 'react';
import Image from 'next/image';
import {
  ArrowRight, BookOpen, CheckCircle2, Building, ShieldCheck, Award,
  ChevronRight, ChevronLeft, Globe, Calendar, Clock, Play, Star,
  Microscope, Code2, BrainCircuit, Landmark, Quote, Newspaper,
  FlaskConical, MapPin, Zap, Trophy, Handshake, ExternalLink, X,
} from 'lucide-react';

// ─── Data ─────────────────────────────────────────────────────────────────────

const heroSlides = [
  { id:1, tag:'September 2026 Admissions Open', title:'Empowering the Next Generation of', highlight:'Innovators & Leaders', sub:'World-class undergraduate and postgraduate programmes in computing, software engineering, business, and data sciences.', bg:'/hero-campus.jpg' },
  { id:2, tag:'CUE Accredited Programmes', title:'Pioneering Research &', highlight:'Innovation in East Africa', sub:'Join over 5,400 students and 300+ faculty members across four campuses shaping the digital future of our continent.', bg:'/about-students.jpg' },
  { id:3, tag:'Modern Research Facilities', title:'Where Talent Meets', highlight:'Opportunity & Impact', sub:'State-of-the-art AI labs, guaranteed industry attachments, HELB financial support, and award-winning faculty mentorship.', bg:'/graduation.jpg' },
];

const ticker = [
  '🎓 September 2026 Intake applications now open — Apply before 30 Aug 2026',
  '🏆 Mema University ranked #1 tech university in East Africa 2026',
  '📢 Annual Hackathon: AI for Africa — Register by 1 Sep 2026',
  '🔬 New AI & Machine Learning Research Centre officially launched',
  '💼 85 students placed in industry attachment at top tech firms this semester',
  '📚 HELB loan applications open — Apply through your student portal',
];

const statsData = [
  { value:5400, suffix:'+', label:'Active Students', icon:'👨‍🎓' },
  { value:96, suffix:'%', label:'Employment Rate', icon:'💼' },
  { value:42, suffix:'+', label:'Degree Programmes', icon:'📚' },
  { value:300, suffix:'+', label:'Faculty Members', icon:'🎓' },
];

const facultyCategories = ['All Programmes', 'Computing & IT', 'Data Science & AI', 'Business', 'Postgraduate'];

const programmes = [
  { code:'BSc. CS', icon:Code2, title:'BSc. Computer Science', duration:'4 Years', units:120, color:'teal', dept:'Faculty of Computing & IT', category:'Computing & IT' },
  { code:'BSc. SE', icon:BrainCircuit, title:'BSc. Software Engineering', duration:'4 Years', units:128, color:'green', dept:'Faculty of Computing & IT', category:'Computing & IT' },
  { code:'BSc. DS', icon:FlaskConical, title:'BSc. Data Science & AI', duration:'4 Years', units:120, color:'orange', dept:'School of Data Sciences', category:'Data Science & AI' },
  { code:'BBA', icon:Landmark, title:'BBA Business Administration', duration:'3 Years', units:108, color:'teal', dept:'School of Business & Economics', category:'Business' },
  { code:'MSc. CS', icon:Microscope, title:'MSc. Computer Science', duration:'2 Years', units:64, color:'green', dept:'Postgraduate Studies', category:'Postgraduate' },
  { code:'MSc. AI', icon:Globe, title:'MSc. Artificial Intelligence', duration:'2 Years', units:60, color:'orange', dept:'Postgraduate Studies', category:'Postgraduate' },
];

const researchAreas = [
  { icon:BrainCircuit, title:'Artificial Intelligence & ML', desc:'Deep learning, NLP, computer vision, and AI ethics for real-world East African applications.', count:'12 Active Projects', tag:'AI / ML' },
  { icon:Globe, title:'Cybersecurity & Networks', desc:'Threat intelligence, blockchain security, zero-trust architectures, and national cyber resilience.', count:'8 Active Projects', tag:'Security' },
  { icon:FlaskConical, title:'Biomedical Informatics', desc:'Applying data science to healthcare, genomics, and disease surveillance across Africa.', count:'6 Active Projects', tag:'Health Tech' },
];

const aiCapabilities = [
  { icon:'🤖', label:'Swahili LLM Research' },
  { icon:'⚡', label:'Intelligent Course Advisor' },
  { icon:'📊', label:'Student Risk Analytics' },
  { icon:'🔬', label:'Autonomous IoT Labs' },
  { icon:'💡', label:'Automated Exam Evaluation' },
  { icon:'🚀', label:'Campus Navigation AI' },
];

const techStackBadges = [
  { label:'Core ERP', name:'Laravel 12 REST API', accent:'v12' },
  { label:'Frontend', name:'Next.js 15 & React 19', accent:'v15' },
  { label:'Mobile', name:'Flutter 3.x Apps', accent:'v3' },
  { label:'AI Model', name:'Gemini & OpenAI LLMs', accent:'AI' },
  { label:'Accreditation', name:'CUE Kenya Verified', accent:'100%' },
];

const eventsData = [
  { day:'28', month:'AUG', title:'September 2026 Intake — Open Day', time:'9:00 AM – 4:00 PM', location:'Main Campus, Nairobi', cat:'Admissions' },
  { day:'05', month:'SEP', title:'Annual Hackathon: AI for Africa', time:'8:00 AM – 8:00 PM', location:'Tech Innovation Hub', cat:'Competition' },
  { day:'12', month:'SEP', title:'Prof. Kamau Lecture: Future of Fintech', time:'2:00 PM – 5:00 PM', location:'Auditorium A, Block C', cat:'Lecture' },
];

const testimonials = [
  { name:'Amara Wanjiku', programme:'BSc. Computer Science, Class of 2025', quote:'Mema transformed how I see technology. The AI lab access and industry attachments gave me hands-on experience that landed me a role at a leading fintech firm in Nairobi before I even graduated.', img:'/student-1.jpg', stars:5 },
  { name:'David Otieno', programme:'MSc. Software Engineering, Class of 2024', quote:'The faculty here truly invest in your success. My research on distributed systems was co-published in an IEEE journal, and I credit the incredible mentorship I received at Mema for making that possible.', img:'/student-2.jpg', stars:5 },
  { name:'Fatuma Hassan', programme:'BSc. Data Science & AI, Year 3', quote:'Coming from Mombasa, I was nervous about the city. But Mema\'s inclusive environment, HELB support, and world-class Data Science programme have made it the best decision of my life.', img:'/student-3.jpg', stars:5 },
];

const newsItems = [
  { cat:'Research', date:'20 Aug 2026', title:'Mema AI Lab Publishes Breakthrough Paper on Swahili Language Models', desc:'Our NLP team has published landmark research on large language models trained on African languages, accepted by NeurIPS 2026.', img:'/about-students.jpg' },
  { cat:'Admissions', date:'15 Aug 2026', title:'Sep 2026 Intake: Record 12,000 Applications Received Across All Programmes', desc:'The university received a record number of applications for the upcoming September 2026 academic intake across all faculties.', img:'/hero-campus.jpg' },
  { cat:'Industry', date:'10 Aug 2026', title:'Mema Signs MoU with 3 Leading Tech Companies for Student Placements', desc:'A landmark industry partnership agreement will guarantee internship placements for over 200 students per semester starting 2027.', img:'/campus-life.jpg' },
];

const partners = [
  { name:'Commission for University Education', abbr:'CUE' },
  { name:'KUCCPS', abbr:'KUCCPS' },
  { name:'Kenya ICT Board', abbr:'ICT Board' },
  { name:'IEEE Kenya', abbr:'IEEE' },
  { name:'Microsoft Africa', abbr:'Microsoft' },
  { name:'Google Developers', abbr:'Google' },
  { name:'HELB', abbr:'HELB' },
  { name:'ACM Kenya', abbr:'ACM' },
];

const whyCards = [
  { icon:Building, color:'teal', title:'Modern Research Labs', desc:'High-performance computing clusters, IoT testing labs, and gigabit fiber campus-wide.' },
  { icon:ShieldCheck, color:'green', title:'Industry Attachment', desc:'Mandatory 3-month practicum with leading tech companies and multinationals.' },
  { icon:Award, color:'orange', title:'Scholarships & HELB', desc:'Direct HELB integration, institutional bursaries, and merit scholarships.' },
  { icon:Globe, color:'teal', title:'Global Partnerships', desc:'Academic ties with universities in Kenya, Europe, and North America.' },
  { icon:Zap, color:'green', title:'Innovation Incubator', desc:'Our Startup Hub has launched 14 student-led tech startups since 2022.' },
  { icon:Trophy, color:'orange', title:'Award-Winning Faculty', desc:'Over 300 faculty, 40% holding international research recognition.' },
];

// ─── Animated Counter ─────────────────────────────────────────────────────────
function AnimatedCounter({ target, suffix }: { target: number; suffix: string }) {
  const [count, setCount] = useState(0);
  const ref = useRef<HTMLSpanElement>(null);
  const started = useRef(false);
  useEffect(() => {
    const observer = new IntersectionObserver(([entry]) => {
      if (entry?.isIntersecting && !started.current) {
        started.current = true;
        let start = 0;
        const step = target / (2000 / 16);
        const timer = setInterval(() => {
          start += step;
          if (start >= target) { setCount(target); clearInterval(timer); }
          else { setCount(Math.floor(start)); }
        }, 16);
      }
    }, { threshold: 0.3 });
    if (ref.current) observer.observe(ref.current);
    return () => observer.disconnect();
  }, [target]);
  return <span ref={ref}>{count.toLocaleString()}{suffix}</span>;
}

// ─── Page ─────────────────────────────────────────────────────────────────────
export default function PublicWebsiteHomePage() {
  const [slide, setSlide] = useState(0);
  const [fading, setFading] = useState(false);
  const [videoOpen, setVideoOpen] = useState(false);
  const [botModalOpen, setBotModalOpen] = useState(false);
  const [selectedFaculty, setSelectedFaculty] = useState('All Programmes');

  // Hero auto-play
  useEffect(() => {
    const t = setInterval(() => changeSlide((slide + 1) % heroSlides.length), 6500);
    return () => clearInterval(t);
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [slide]);

  // Scroll progress, reveal animations, particle swarm, and 3D bot/card physics
  useEffect(() => {
    // 1. Scroll progress bar
    const bar = document.getElementById('scroll-progress-bar');
    const updateBar = () => {
      const doc = document.documentElement;
      const scrolled = doc.scrollTop || document.body.scrollTop;
      const total = doc.scrollHeight - doc.clientHeight;
      const pct = total > 0 ? Math.min((scrolled / total) * 100, 100) : 0;
      if (bar) bar.style.width = pct + '%';
    };
    window.addEventListener('scroll', updateBar, { passive: true });
    updateBar();

    // 2. Viewport Reveal Observer
    const els = document.querySelectorAll('.reveal');
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((e) => {
          if (e.isIntersecting) {
            e.target.classList.add('is-visible');
            io.unobserve(e.target);
          }
        });
      },
      { threshold: 0.1, rootMargin: '0px 0px -30px 0px' }
    );
    els.forEach((el) => io.observe(el));

    // 3. Card Hover Spotlight & 3D Robot Head Tracking
    const handleMouseMove = (e: MouseEvent) => {
      // Ambient card spotlight
      const cards = document.querySelectorAll<HTMLElement>('.why-card,.prog-card,.news-card,.testimonial-card');
      cards.forEach((card) => {
        const rect = card.getBoundingClientRect();
        const x = (((e.clientX - rect.left) / rect.width) * 100).toFixed(1) + '%';
        const y = (((e.clientY - rect.top) / rect.height) * 100).toFixed(1) + '%';
        card.style.setProperty('--mx', x);
        card.style.setProperty('--my', y);
      });

      // 3D Robot Tracker
      const botHead = document.getElementById('botHead');
      const botEyes = document.getElementById('botEyes');
      const botGlare = document.getElementById('botGlare');
      if (botHead && botEyes && botGlare) {
        const rect = botHead.getBoundingClientRect();
        const botX = rect.left + rect.width / 2;
        const botY = rect.top + rect.height / 2;
        const deltaX = e.clientX - botX;
        const deltaY = e.clientY - botY;
        const normX = Math.max(-1, Math.min(1, deltaX / (window.innerWidth / 1.5)));
        const normY = Math.max(-1, Math.min(1, deltaY / (window.innerHeight / 1.5)));
        botHead.style.transform = `rotateX(${-normY * 25}deg) rotateY(${normX * 25}deg)`;
        botEyes.style.transform = `translate(${normX * 6}px, ${normY * 6}px)`;
        botGlare.style.transform = `translate(${-normX * 12}px, ${-normY * 12}px)`;
      }
    };
    window.addEventListener('mousemove', handleMouseMove, { passive: true });

    // 4. Antigravity Interactive Galaxy Swarm Canvas
    const canvas = document.getElementById('hero-galaxy-canvas') as HTMLCanvasElement | null;
    let animId: number;
    if (canvas) {
      const ctx = canvas.getContext('2d');
      if (ctx) {
        let w = (canvas.width = window.innerWidth);
        let h = (canvas.height = window.innerHeight);
        const handleResize = () => {
          w = canvas.width = window.innerWidth;
          h = canvas.height = window.innerHeight;
        };
        window.addEventListener('resize', handleResize);

        let mouseX = w / 2;
        let mouseY = h / 2;
        const trackMouse = (ev: MouseEvent) => {
          mouseX = ev.clientX;
          mouseY = ev.clientY;
        };
        window.addEventListener('mousemove', trackMouse);

        const colors = ['#0A3E50', '#1E8449', '#E67E22', '#38BDF8', '#F59E0B'];
        const particles: Array<{
          x: number; y: number; baseX: number; baseY: number;
          vx: number; vy: number; size: number; color: string;
          friction: number; springFactor: number; wanderAngle: number;
        }> = [];

        for (let i = 0; i < 90; i++) {
          const sx = Math.random() * w;
          const sy = Math.random() * h;
          particles.push({
            x: sx, y: sy, baseX: sx, baseY: sy,
            vx: 0, vy: 0,
            size: Math.random() * 2 + 1,
            color: colors[Math.floor(Math.random() * colors.length)] ?? '#1E8449',
            friction: Math.random() * 0.04 + 0.88,
            springFactor: Math.random() * 0.02 + 0.01,
            wanderAngle: Math.random() * Math.PI * 2,
          });
        }

        const render = () => {
          ctx.clearRect(0, 0, w, h);
          particles.forEach((p) => {
            p.wanderAngle += 0.02;
            const targetX = p.baseX + Math.cos(p.wanderAngle) * 24;
            const targetY = p.baseY + Math.sin(p.wanderAngle * 1.5) * 24;
            const dx = mouseX - p.x;
            const dy = mouseY - p.y;
            const dist = Math.sqrt(dx * dx + dy * dy);

            if (dist < 150) {
              const force = (150 - dist) / 150;
              p.vx -= (dx / dist) * force * 1.8;
              p.vy -= (dy / dist) * force * 1.8;
            }

            p.vx += (targetX - p.x) * p.springFactor;
            p.vy += (targetY - p.y) * p.springFactor;
            p.vx *= p.friction;
            p.vy *= p.friction;
            p.x += p.vx;
            p.y += p.vy;

            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
            ctx.lineTo(p.x - p.vx * 2.2, p.y - p.vy * 2.2);
            ctx.strokeStyle = p.color;
            ctx.lineWidth = p.size;
            ctx.lineCap = 'round';
            ctx.globalAlpha = 0.35;
            ctx.stroke();
          });
          animId = requestAnimationFrame(render);
        };
        render();
      }
    }

    return () => {
      window.removeEventListener('scroll', updateBar);
      window.removeEventListener('mousemove', handleMouseMove);
      io.disconnect();
      if (animId) cancelAnimationFrame(animId);
    };
  }, []);

  function changeSlide(next: number) {
    setFading(true);
    setTimeout(() => { setSlide(next); setFading(false); }, 450);
  }

  const currentSlide = heroSlides[slide] ?? heroSlides[0]!;

  return (
    <div className="mema-site">

      {/* ══ NEWS TICKER ══════════════════════════════════════════════════════ */}
      <div className="ticker-bar">
        <div className="ticker-label">📣 News</div>
        <div className="ticker-track-wrap">
          <div className="ticker-track">
            {[...ticker, ...ticker].map((item, i) => (
              <span key={i} className="ticker-item">{item}</span>
            ))}
          </div>
        </div>
      </div>

      {/* ══ HERO ════════════════════════════════════════════════════════════ */}
      <section className="hero-section-frame">
        <canvas id="hero-galaxy-canvas" style={{ position:'absolute', inset:0, width:'100%', height:'100%', pointerEvents:'none', zIndex:2 }} />

        {/* Background layer — only this fades */}
        <div className={`hero-bg-layer${fading?' fade-out':''}`}>
          <Image src={currentSlide.bg} alt="Mema University Campus" fill style={{ objectFit:'cover', objectPosition:'center 30%' }} priority />
          <div className="hero-overlay" />
        </div>

        {/* Content layer — only this fades */}
        <div className={`hero-content-layer${fading?' fade-out':''}`}>
          <div className="hero-tag-pill"><Star className="hero-star-icon" /> {currentSlide.tag}</div>
          <h1 className="hero-h1">{currentSlide.title} <span className="hero-highlight">{currentSlide.highlight}</span></h1>
          <p className="hero-paragraph">{currentSlide.sub}</p>
          <div className="hero-proof-badge"><CheckCircle2 style={{ width:'1em', height:'1em' }} /> 96% Graduate Employment Rate</div>
          <div className="hero-cta-row">
            <a href="http://localhost:3001" className="hero-cta-primary">Apply for Sep 2026 <ArrowRight style={{ display:'inline', width:'1.1em', height:'1.1em', marginLeft:'0.4em' }} /></a>
            <a href="#programmes" className="hero-cta-outline"><Play style={{ display:'inline', width:'1em', height:'1em', marginRight:'0.4em' }} /> Explore Programmes</a>
          </div>
        </div>

        {/* Quick portal cards — fixed shell, never rotates */}
        <div className="hero-quick-portals">
          <a href="http://localhost:3001" className="hqp-item hqp-apply">
            <span className="hqp-icon">📝</span>
            <div><div className="hqp-title">Apply Now</div><div className="hqp-sub">Sep 2026 Intake</div></div>
          </a>
          <a href="http://localhost:3002" className="hqp-item hqp-student">
            <span className="hqp-icon">🎓</span>
            <div><div className="hqp-title">Student Portal</div><div className="hqp-sub">Results &amp; Records</div></div>
          </a>
          <a href="http://localhost:3005" className="hqp-item hqp-staff">
            <span className="hqp-icon">🏢</span>
            <div><div className="hqp-title">Staff Portal</div><div className="hqp-sub">ERP Console</div></div>
          </a>
          <a href="#programmes" className="hqp-item hqp-prog">
            <span className="hqp-icon">📚</span>
            <div><div className="hqp-title">Programmes</div><div className="hqp-sub">42 Degrees Available</div></div>
          </a>
        </div>

        {/* Slider controls — fixed shell, never rotates */}
        <div className="hero-dots-bar">
          <button className="hero-arrow-btn" onClick={() => changeSlide((slide-1+heroSlides.length)%heroSlides.length)}><ChevronLeft /></button>
          <div className="hero-dots-list">{heroSlides.map((_,i)=><button key={i} className={`hero-dot${i===slide?' active':''}`} onClick={()=>changeSlide(i)} />)}</div>
          <button className="hero-arrow-btn" onClick={() => changeSlide((slide+1)%heroSlides.length)}><ChevronRight /></button>
        </div>
      </section>

      {/* ══ TECH STACK & SYSTEM ARCHITECTURE STRIP ═════════════════════════ */}
      <div className="tech-stack-strip">
        <div className="tech-stack-inner">
          {techStackBadges.map((b, i) => (
            <React.Fragment key={i}>
              <div className="tech-badge">
                <div>
                  <div className="tech-badge-label">{b.label}</div>
                  <div className="tech-badge-name">{b.name} <span>{b.accent}</span></div>
                </div>
              </div>
              {i < techStackBadges.length - 1 && <div className="tech-sep" />}
            </React.Fragment>
          ))}
        </div>
      </div>

      {/* ══ STATS STRIP ═════════════════════════════════════════════════════ */}
      <section className="stats-strip">
        {statsData.map((s,i) => (
          <div key={i} className="stat-block">
            <div className="stat-emoji">{s.icon}</div>
            <div className="stat-number"><AnimatedCounter target={s.value} suffix={s.suffix} /></div>
            <div className="stat-label">{s.label}</div>
          </div>
        ))}
      </section>

      {/* ══ ABOUT & PERFORMANCE TELEMETRY ═══════════════════════════════════ */}
      <section className="about-section">
        <div className="about-wrap">
          <div className="about-img-col reveal from-left">
            <div className="about-img-container">
              <Image src="/about-students.jpg" alt="Mema University Students" fill style={{ objectFit:'cover', borderRadius:'1.25rem' }} />
            </div>
            <div className="about-badge">
              <span style={{ fontSize:'1.75rem' }}>🎓</span>
              <div><div className="about-badge-val">Est. 2008</div><div className="about-badge-sub">16+ Years of Excellence</div></div>
            </div>

            {/* Live Academic Telemetry Card */}
            <div style={{ marginTop:'1.5rem', background:'#fff', padding:'1.5rem', borderRadius:'1.25rem', border:'1px solid #e2e8f0', boxShadow:'0 10px 30px rgba(0,0,0,0.06)' }}>
              <div style={{ fontSize:'0.75rem', fontWeight:800, color:'#0A3E50', textTransform:'uppercase', letterSpacing:'0.05em', marginBottom:'0.75rem' }}>
                📈 Institutional Performance Overview (2025/2026)
              </div>
              <div className="donut-wrap">
                <div className="donut">
                  <div className="donut-num">96%</div>
                </div>
                <div>
                  <div style={{ fontSize:'0.88rem', fontWeight:800, color:'#0A3E50' }}>Graduate Employment</div>
                  <div style={{ fontSize:'0.78rem', color:'#64748b', marginTop:'0.2rem' }}>Graduates in career-track employment or master&apos;s study within 6 months.</div>
                </div>
              </div>

              {/* Department Bars */}
              <div className="mini-bar-row">
                <div className="mini-bar-label">Computer Science</div>
                <div className="mini-bar-track"><div className="mini-bar-fill" style={{ width:'94%' }} /></div>
                <div className="mini-bar-val">94%</div>
              </div>
              <div className="mini-bar-row">
                <div className="mini-bar-label">Software Eng.</div>
                <div className="mini-bar-track"><div className="mini-bar-fill" style={{ width:'92%' }} /></div>
                <div className="mini-bar-val">92%</div>
              </div>
              <div className="mini-bar-row">
                <div className="mini-bar-label">Data Science & AI</div>
                <div className="mini-bar-track"><div className="mini-bar-fill" style={{ width:'96%' }} /></div>
                <div className="mini-bar-val">96%</div>
              </div>
              <div className="mini-bar-row">
                <div className="mini-bar-label">Business Admin</div>
                <div className="mini-bar-track"><div className="mini-bar-fill" style={{ width:'89%' }} /></div>
                <div className="mini-bar-val">89%</div>
              </div>

              {/* KPI summary */}
              <div className="kpi-row">
                <div className="kpi-box">
                  <div className="kpi-num">5,420+</div>
                  <div className="kpi-label">Active Scholars</div>
                </div>
                <div className="kpi-box">
                  <div className="kpi-num">100%</div>
                  <div className="kpi-label">CUE Accredited</div>
                </div>
              </div>
            </div>
          </div>

          <div className="about-text-col reveal from-right">
            <div className="section-tag-label">About Mema University</div>
            <h2 className="section-h2">A Legacy of Academic <span className="text-accent">Excellence</span> in East Africa</h2>
            <p className="about-para">Founded in 2008, Mema University has grown into East Africa&apos;s premier technology-focused university. Accredited by the Commission for University Education (CUE), our mission is to produce world-class graduates, drive innovation, and deliver transformative research.</p>
            <ul className="about-checklist">
              {['CUE Accredited — recognised nationally and internationally','State-of-the-art AI, IoT, and Computing research labs','Guaranteed 3-month supervised industry attachment','HELB, bursary & merit scholarship integration','96% graduate employment rate within 6 months','Active startup incubation hub — 14 ventures launched'].map((p,i)=>(
                <li key={i} className="about-check-item"><CheckCircle2 className="check-icon" /><span>{p}</span></li>
              ))}
            </ul>
            <div className="about-btn-row">
              <a href="#programmes" className="btn-solid">Our Programmes <ArrowRight className="btn-icon-inline" /></a>
              <a href="http://localhost:3001" className="btn-ghost">Apply Now</a>
            </div>
          </div>
        </div>
      </section>

      {/* ══ WHY CHOOSE US ═══════════════════════════════════════════════════ */}
      <section className="why-section">
        <div className="section-center-wrap">
          <div className="section-tag-label center-text">Why Choose Mema?</div>
          <h2 className="section-h2 center-text">The Mema <span className="text-accent">Advantage</span></h2>
          <p className="section-subtext center-text">Academic rigour, industry connections, and a campus designed for tomorrow&apos;s leaders.</p>
          <div className="why-grid">
            {whyCards.map(({ icon:Icon, color, title, desc }, i) => (
              <div key={i} className={`why-card why-${color} reveal stagger-${Math.min(i+1,6) as 1|2|3|4|5|6}`}>
                <div className={`why-icon-box why-icon-${color}`}><Icon className="why-icon" /></div>
                <h3 className="why-title">{title}</h3>
                <p className="why-desc">{desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ══ PROGRAMMES WITH FACULTY TABS ════════════════════════════════════ */}
      <section id="programmes" className="prog-section">
        <div className="section-center-wrap">
          <div className="section-tag-label">Academic Programmes</div>
          <h2 className="section-h2">Find Your <span className="text-accent">Academic Path</span></h2>
          <p className="section-subtext">Industry-aligned curricula crafted with leading technology companies and research institutions.</p>
          
          {/* Faculty Tabs */}
          <div className="feat-tabs-wrap">
            {facultyCategories.map((cat, i) => (
              <button
                key={i}
                className={`feat-tab ${selectedFaculty === cat ? 'active' : ''}`}
                onClick={() => setSelectedFaculty(cat)}
              >
                {cat}
              </button>
            ))}
          </div>

          <div className="prog-grid">
            {programmes
              .filter(p => selectedFaculty === 'All Programmes' || p.category === selectedFaculty)
              .map((prog, i) => {
              const Icon = prog.icon;
              return (
                <div key={i} className={`prog-card prog-${prog.color} reveal stagger-${Math.min(i+1,6) as 1|2|3|4|5|6}`}>
                  <div className={`prog-icon-box prog-icon-${prog.color}`}><Icon className="prog-icon" /></div>
                  <div className="prog-meta-row">
                    <span className={`prog-code-badge prog-code-${prog.color}`}>{prog.code}</span>
                    <span className="prog-dur-badge">{prog.duration}</span>
                  </div>
                  <h3 className="prog-h3">{prog.title}</h3>
                  <p className="prog-dept-txt">{prog.dept}</p>
                  <div className="prog-details-row">
                    <span className="prog-detail"><BookOpen className="prog-detail-icon" /> {prog.units} Credit Units</span>
                    <span className="prog-detail"><CheckCircle2 className="prog-detail-icon" /> CUE Accredited</span>
                  </div>
                  <div className="prog-footer-row">
                    <span className="prog-fee-txt">KES 85,000 / Semester</span>
                    <a href="http://localhost:3001" className={`prog-apply-btn prog-apply-${prog.color}`}>Apply <ArrowRight className="btn-icon-inline" /></a>
                  </div>
                </div>
              );
            })}
          </div>
          <div style={{ textAlign:'center', marginTop:'2.5rem' }}>
            <a href="http://localhost:3001" className="view-all-btn">Browse All 42 Programmes & Admissions <ChevronRight className="btn-icon-inline" /></a>
          </div>
        </div>
      </section>

      {/* ══ AI INNOVATION MATRIX ═════════════════════════════════════════════ */}
      <section className="ai-section">
        <div style={{ maxWidth:'1240px', margin:'0 auto', display:'grid', gridTemplateColumns:'repeat(auto-fit, minmax(320px, 1fr))', gap:'3.5rem', alignItems:'center' }}>
          <div>
            <div className="section-tag-label white-tag">AI Innovation Matrix</div>
            <h2 className="section-h2 white-heading">Kenya&apos;s Leading <span className="text-accent-light">AI Research Hub</span></h2>
            <p style={{ color:'rgba(255,255,255,0.75)', fontSize:'0.95rem', lineHeight:1.75, margin:'1rem 0 1.5rem' }}>
              At Mema University, artificial intelligence powers both academic research and campus operations. From local African language LLMs to predictive student advising, we lead the technological frontier.
            </p>
            <div className="ai-chips-grid">
              {aiCapabilities.map((cap, i) => (
                <div key={i} className="ai-chip">
                  <span>{cap.icon}</span> {cap.label}
                </div>
              ))}
            </div>
            <div style={{ marginTop:'2rem' }}>
              <a href="http://localhost:3001" className="hero-cta-primary" style={{ display:'inline-flex' }}>
                Join the AI Cohort 2026 <ArrowRight style={{ width:'1rem', height:'1rem', marginLeft:'0.4rem' }} />
              </a>
            </div>
          </div>
          <div style={{ display:'flex', justifyContent:'center', position:'relative' }}>
            <div style={{ position:'relative', width:'260px', height:'260px', display:'flex', alignItems:'center', justifyContent:'center' }}>
              <div className="orb-ring" />
              <div className="orb-ring" />
              <div className="orb-ring" />
              <div className="ai-orb-outer">
                <div className="ai-orb-inner">🤖</div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ══ RESEARCH ════════════════════════════════════════════════════════ */}
      <section className="research-section">
        <div className="section-center-wrap">
          <div className="section-tag-label white-tag">Research & Innovation</div>
          <h2 className="section-h2 white-heading">Advancing Knowledge for a <span className="text-accent-light">Better World</span></h2>
          <p className="section-subtext white-sub">Our researchers tackle pressing global challenges through interdisciplinary collaboration.</p>
          <div className="research-grid">
            {researchAreas.map((area, i) => {
              const Icon = area.icon;
              return (
                <div key={i} className="research-card">
                  <div className="research-tag">{area.tag}</div>
                  <div className="research-icon-box"><Icon className="research-icon" /></div>
                  <h3 className="research-h3">{area.title}</h3>
                  <p className="research-desc">{area.desc}</p>
                  <div className="research-count">{area.count}</div>
                  <a href="#" className="research-link">Learn More <ArrowRight className="btn-icon-inline" /></a>
                </div>
              );
            })}
          </div>
        </div>
      </section>

      {/* ══ CAMPUS LIFE / VIDEO ══════════════════════════════════════════════ */}
      <section className="video-section">
        <div className="video-inner">
          <div className="video-thumb-wrap">
            <Image src="/campus-life.jpg" alt="Mema Campus Life" fill style={{ objectFit:'cover' }} />
            <div className="video-overlay" />
            <button className="video-play-btn" onClick={() => setVideoOpen(true)}>
              <Play style={{ width:'2rem', height:'2rem', fill:'white', color:'white' }} />
            </button>
          </div>
          <div className="video-text">
            <div className="section-tag-label white-tag">Campus Life</div>
            <h2 className="section-h2 white-heading">Experience the <span className="text-accent-light">Mema Spirit</span></h2>
            <p style={{ color:'rgba(255,255,255,0.75)', fontSize:'1rem', lineHeight:1.75, marginBottom:'1.5rem' }}>Life at Mema is more than academics. From hackathons and innovation challenges to cultural festivals, sports clubs, and student leadership — we shape the whole person.</p>
            <ul style={{ listStyle:'none', padding:0, margin:'0 0 2rem', display:'flex', flexDirection:'column', gap:'0.65rem' }}>
              {['Student Innovation Hub & Maker Space','Annual Tech Expo & Startup Demo Day','Inter-university hackathon & coding competitions','Campus sports, arts, and cultural societies','Alumni mentorship network: 8,000+ professionals'].map((item, i) => (
                <li key={i} style={{ display:'flex', alignItems:'center', gap:'0.5rem', color:'rgba(255,255,255,0.85)', fontSize:'0.9rem' }}>
                  <Zap style={{ width:'0.9rem', height:'0.9rem', color:'#E67E22', flexShrink:0 }} /> {item}
                </li>
              ))}
            </ul>
            <button className="video-cta-btn" onClick={() => setVideoOpen(true)}>
              <Play style={{ width:'1rem', height:'1rem' }} /> Watch Campus Tour
            </button>
          </div>
        </div>
      </section>

      {/* ══ TESTIMONIALS ════════════════════════════════════════════════════ */}
      <section className="testimonials-section">
        <div className="section-center-wrap">
          <div className="section-tag-label center-text">Student Voices</div>
          <h2 className="section-h2 center-text">Hear From Our <span className="text-accent">Community</span></h2>
          <div className="testimonials-grid">
            {testimonials.map((t, i) => (
              <div key={i} className={`testimonial-card${i===1?' testimonial-featured':''} reveal stagger-${Math.min(i+1,3) as 1|2|3}`}>
                <Quote className="testimonial-quote-icon" />
                <p className="testimonial-text">&ldquo;{t.quote}&rdquo;</p>
                <div className="testimonial-stars">{Array(t.stars).fill('★').join('')}</div>
                <div className="testimonial-author">
                  <div className="testimonial-avatar">
                    <Image src={t.img} alt={t.name} fill style={{ objectFit:'cover', borderRadius:'50%' }} />
                  </div>
                  <div>
                    <div className="testimonial-name">{t.name}</div>
                    <div className="testimonial-prog">{t.programme}</div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ══ EVENTS ══════════════════════════════════════════════════════════ */}
      <section className="events-section">
        <div className="section-center-wrap">
          <div style={{ display:'flex', alignItems:'flex-end', justifyContent:'space-between', marginBottom:'2rem', flexWrap:'wrap', gap:'1rem' }}>
            <div>
              <div className="section-tag-label">Upcoming Events</div>
              <h2 className="section-h2" style={{ margin:0 }}>Stay <span className="text-accent">Connected</span></h2>
            </div>
            <a href="#" className="view-all-btn" style={{ padding:'0.5rem 1.25rem', fontSize:'0.82rem' }}>All Events <ExternalLink style={{ width:'0.8rem', height:'0.8rem', display:'inline', marginLeft:'0.3em' }} /></a>
          </div>
          <div className="events-list">
            {eventsData.map((ev, i) => (
              <div key={i} className={`event-card reveal stagger-${Math.min(i+1,3) as 1|2|3}`}>
                <div className="event-date-box">
                  <span className="event-day">{ev.day}</span>
                  <span className="event-month">{ev.month}</span>
                </div>
                <div className="event-info">
                  <span className="event-cat-badge">{ev.cat}</span>
                  <h3 className="event-title">{ev.title}</h3>
                  <div className="event-meta-row">
                    <span className="event-meta"><Clock className="event-meta-icon" /> {ev.time}</span>
                    <span className="event-meta"><MapPin className="event-meta-icon" /> {ev.location}</span>
                  </div>
                </div>
                <a href="#" className="event-cal-btn"><Calendar className="event-cal-icon" /></a>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ══ NEWS ════════════════════════════════════════════════════════════ */}
      <section className="news-section">
        <div className="section-center-wrap">
          <div style={{ display:'flex', alignItems:'flex-end', justifyContent:'space-between', marginBottom:'2rem', flexWrap:'wrap', gap:'1rem' }}>
            <div>
              <div className="section-tag-label">Latest News</div>
              <h2 className="section-h2" style={{ margin:0 }}>News & <span className="text-accent">Announcements</span></h2>
            </div>
            <a href="#" className="view-all-btn" style={{ padding:'0.5rem 1.25rem', fontSize:'0.82rem' }}>All News <Newspaper style={{ width:'0.8rem', height:'0.8rem', display:'inline', marginLeft:'0.3em' }} /></a>
          </div>
          <div className="news-grid">
            {newsItems.map((item, i) => (
              <article key={i} className={`news-card reveal stagger-${Math.min(i+1,3) as 1|2|3}`}>
                <div className="news-img-wrap">
                  <Image src={item.img} alt={item.title} fill style={{ objectFit:'cover' }} />
                  <div className="news-cat-badge">{item.cat}</div>
                </div>
                <div className="news-body">
                  <div className="news-date">{item.date}</div>
                  <h3 className="news-title">{item.title}</h3>
                  <p className="news-desc">{item.desc}</p>
                  <a href="#" className="news-read-more">Read More <ArrowRight className="btn-icon-inline" /></a>
                </div>
              </article>
            ))}
          </div>
        </div>
      </section>

      {/* ══ PARTNERS / ACCREDITATION ════════════════════════════════════════ */}
      <section className="partners-section">
        <div className="section-center-wrap">
          <div className="section-tag-label center-text">Accreditations & Partnerships</div>
          <h2 className="section-h2 center-text" style={{ marginBottom:'2.5rem' }}>Trusted By <span className="text-accent">Industry Leaders</span></h2>
          <div className="partners-scroll">
            {[...partners, ...partners].map((p, i) => (
              <div key={i} className="partner-logo">
                <Handshake style={{ width:'1.2rem', height:'1.2rem', color:'#0A3E50', opacity:0.5 }} />
                <span className="partner-abbr">{p.abbr}</span>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ══ CTA BANNER ══════════════════════════════════════════════════════ */}
      <section className="cta-section">
        <div className="cta-inner-wrap">
          <div className="cta-text-col reveal from-left">
            <h2 className="cta-h2">Begin Your University Journey Today</h2>
            <p className="cta-para">Applications for September 2026 are open. Complete your registration in under 10 minutes.</p>
          </div>
          <div className="cta-btn-col">
            <a href="http://localhost:3001" className="cta-btn-primary">Start Application <ArrowRight className="btn-icon-inline" /></a>
            <a href="tel:+254208920000" className="cta-btn-outline">📞 Call Admissions</a>
          </div>
        </div>
      </section>

      {/* ══ VIDEO MODAL ═════════════════════════════════════════════════════ */}
      {videoOpen && (
        <div className="video-modal-overlay" onClick={() => setVideoOpen(false)}>
          <div className="video-modal-box" onClick={e => e.stopPropagation()}>
            <button className="video-modal-close" onClick={() => setVideoOpen(false)} aria-label="Close video">
              <X style={{ width: '1.75rem', height: '1.75rem' }} />
            </button>
            <div className="video-modal-inner" style={{ position: 'relative', width: '100%', height: '100%', borderRadius: '14px', overflow: 'hidden', background: '#000' }}>
              <video
                src="/campus-tour.mp4"
                poster="/campus-life.jpg"
                controls
                autoPlay
                playsInline
                loop
                style={{ width: '100%', height: '100%', objectFit: 'contain' }}
              >
                Your browser does not support the video tag.
              </video>
            </div>
          </div>
        </div>
      )}

      {/* ══ 3D SUPPORT AI ROBOT WIDGET ══════════════════════════════════════ */}
      <div id="supportBotWrapper" onClick={() => setBotModalOpen(true)} title="Click for AI Campus Advisor & Portals">
        <div className="bot-container" id="supportBot">
          <div className="bot-antenna">
            <div className="bot-antenna-bulb" />
          </div>
          <div className="bot-head" id="botHead">
            <div className="bot-visor">
              <div className="bot-eyes" id="botEyes">
                <div className="bot-eye" />
                <div className="bot-eye" />
              </div>
              <div className="bot-visor-glare" id="botGlare" />
            </div>
          </div>
          <div className="bot-shadow" />
        </div>
      </div>

      {/* ══ AI CAMPUS ADVISOR & PORTAL MATRIX MODAL ═════════════════════════ */}
      {botModalOpen && (
        <div
          style={{
            position: 'fixed',
            inset: 0,
            zIndex: 10000,
            background: 'rgba(7, 45, 58, 0.85)',
            backdropFilter: 'blur(16px)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            padding: '1.5rem',
          }}
          onClick={() => setBotModalOpen(false)}
        >
          <div
            style={{
              background: '#fff',
              borderRadius: '24px',
              maxWidth: '560px',
              width: '100%',
              padding: '2rem',
              boxShadow: '0 25px 60px rgba(0,0,0,0.3)',
              border: '1px solid rgba(255,255,255,0.2)',
              position: 'relative',
            }}
            onClick={(e) => e.stopPropagation()}
          >
            <button
              onClick={() => setBotModalOpen(false)}
              style={{
                position: 'absolute',
                top: '1.25rem',
                right: '1.25rem',
                background: '#f1f5f9',
                border: 'none',
                borderRadius: '50%',
                width: '36px',
                height: '36px',
                cursor: 'pointer',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                color: '#64748b',
              }}
            >
              <X style={{ width: '1.2rem', height: '1.2rem' }} />
            </button>

            <div style={{ display: 'flex', alignItems: 'center', gap: '0.85rem', marginBottom: '1.25rem' }}>
              <div style={{ width: '48px', height: '48px', borderRadius: '14px', background: 'linear-gradient(135deg, #0A3E50, #1E8449)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '1.5rem' }}>
                🤖
              </div>
              <div>
                <h3 style={{ fontSize: '1.15rem', fontWeight: 900, color: '#0A3E50', margin: 0 }}>Mema AI Campus Navigator</h3>
                <p style={{ fontSize: '0.78rem', color: '#64748b', margin: '0.2rem 0 0' }}>Instant Admissions Guidance &amp; Portal Hub</p>
              </div>
            </div>

            <p style={{ fontSize: '0.85rem', color: '#475569', lineHeight: 1.6, marginBottom: '1.5rem' }}>
              Welcome to Mema University! How can we help you accelerate your academic journey today?
            </p>

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '0.85rem' }}>
              <a
                href="http://localhost:3001"
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  gap: '0.75rem',
                  padding: '1rem',
                  borderRadius: '14px',
                  background: '#FEF5EC',
                  border: '1.5px solid #FBD0A7',
                  textDecoration: 'none',
                  color: '#E67E22',
                  fontWeight: 800,
                  fontSize: '0.82rem',
                }}
              >
                <span style={{ fontSize: '1.3rem' }}>📝</span>
                <div>
                  <div>Apply for Sep 2026</div>
                  <span style={{ fontSize: '0.7rem', color: '#64748b', fontWeight: 600 }}>10-min Single Form</span>
                </div>
              </a>

              <a
                href="http://localhost:3002"
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  gap: '0.75rem',
                  padding: '1rem',
                  borderRadius: '14px',
                  background: '#EAF7EE',
                  border: '1.5px solid #A6DFC0',
                  textDecoration: 'none',
                  color: '#1E8449',
                  fontWeight: 800,
                  fontSize: '0.82rem',
                }}
              >
                <span style={{ fontSize: '1.3rem' }}>🎓</span>
                <div>
                  <div>Student Portal</div>
                  <span style={{ fontSize: '0.7rem', color: '#64748b', fontWeight: 600 }}>GPA &amp; Registration</span>
                </div>
              </a>

              <a
                href="http://localhost:3005"
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  gap: '0.75rem',
                  padding: '1rem',
                  borderRadius: '14px',
                  background: '#EBF5F8',
                  border: '1.5px solid #AAD4E1',
                  textDecoration: 'none',
                  color: '#0A3E50',
                  fontWeight: 800,
                  fontSize: '0.82rem',
                }}
              >
                <span style={{ fontSize: '1.3rem' }}>🏢</span>
                <div>
                  <div>Staff &amp; Admin ERP</div>
                  <span style={{ fontSize: '0.7rem', color: '#64748b', fontWeight: 600 }}>Master Console</span>
                </div>
              </a>

              <a
                href="tel:+254208920000"
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  gap: '0.75rem',
                  padding: '1rem',
                  borderRadius: '14px',
                  background: '#F8FAFC',
                  border: '1.5px solid #E2E8F0',
                  textDecoration: 'none',
                  color: '#1E293B',
                  fontWeight: 800,
                  fontSize: '0.82rem',
                }}
              >
                <span style={{ fontSize: '1.3rem' }}>📞</span>
                <div>
                  <div>Admissions Hotline</div>
                  <span style={{ fontSize: '0.7rem', color: '#64748b', fontWeight: 600 }}>+254 20 892 000</span>
                </div>
              </a>
            </div>

            <div style={{ marginTop: '1.5rem', textAlign: 'center' }}>
              <a
                href="https://wa.me/254700000000?text=Hi%21+I%27m+interested+in+Mema+University+programmes."
                target="_blank"
                rel="noreferrer"
                style={{
                  display: 'inline-flex',
                  alignItems: 'center',
                  gap: '0.5rem',
                  background: 'linear-gradient(135deg, #25D366, #1BA94C)',
                  color: '#fff',
                  padding: '0.75rem 1.75rem',
                  borderRadius: '50px',
                  fontSize: '0.85rem',
                  fontWeight: 800,
                  textDecoration: 'none',
                  boxShadow: '0 4px 15px rgba(37,211,102,0.3)',
                }}
              >
                💬 Chat with Admissions on WhatsApp
              </a>
            </div>
          </div>
        </div>
      )}

    </div>
  );
}
