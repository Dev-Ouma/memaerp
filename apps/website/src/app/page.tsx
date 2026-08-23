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
  { id:2, tag:'CUE Accredited Programmes', title:'Pioneering Research &', highlight:'Innovation in East Africa', sub:'Join over 5,400 students and 300+ faculty members shaping the digital future of our continent.', bg:'/about-students.jpg' },
  { id:3, tag:'State-of-the-Art Facilities', title:'Experience the Future of', highlight:'Education & Discovery', sub:'Modern AI labs, IoT hubs, guaranteed industry attachments, HELB support and 96% graduate employment.', bg:'/campus-life.jpg' },
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

const programmes = [
  { code:'BSc. CS', icon:Code2, title:'BSc. Computer Science', duration:'4 Years', units:120, color:'teal', dept:'Faculty of Computing & IT' },
  { code:'BSc. SE', icon:BrainCircuit, title:'BSc. Software Engineering', duration:'4 Years', units:128, color:'green', dept:'Faculty of Computing & IT' },
  { code:'BSc. DS', icon:FlaskConical, title:'BSc. Data Science & AI', duration:'4 Years', units:120, color:'orange', dept:'School of Data Sciences' },
  { code:'BBA', icon:Landmark, title:'BBA Business Administration', duration:'3 Years', units:108, color:'teal', dept:'School of Business & Economics' },
  { code:'MSc. CS', icon:Microscope, title:'MSc. Computer Science', duration:'2 Years', units:64, color:'green', dept:'Postgraduate Studies' },
  { code:'MSc. AI', icon:Globe, title:'MSc. Artificial Intelligence', duration:'2 Years', units:60, color:'orange', dept:'Postgraduate Studies' },
];

const researchAreas = [
  { icon:BrainCircuit, title:'Artificial Intelligence & ML', desc:'Deep learning, NLP, computer vision, and AI ethics for real-world East African applications.', count:'12 Active Projects', tag:'AI / ML' },
  { icon:Globe, title:'Cybersecurity & Networks', desc:'Threat intelligence, blockchain security, zero-trust architectures, and national cyber resilience.', count:'8 Active Projects', tag:'Security' },
  { icon:FlaskConical, title:'Biomedical Informatics', desc:'Applying data science to healthcare, genomics, and disease surveillance across Africa.', count:'6 Active Projects', tag:'Health Tech' },
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
      if (entry.isIntersecting && !started.current) {
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
  const [testSlide, setTestSlide] = useState(0);
  const [videoOpen, setVideoOpen] = useState(false);

  // Hero auto-play
  useEffect(() => {
    const t = setInterval(() => changeSlide((slide + 1) % heroSlides.length), 6500);
    return () => clearInterval(t);
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [slide]);

  function changeSlide(next: number) {
    setFading(true);
    setTimeout(() => { setSlide(next); setFading(false); }, 450);
  }

  const currentSlide = heroSlides[slide];

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
      <section style={{ position:'relative', height:'100vh', minHeight:'620px', overflow:'hidden', display:'flex', alignItems:'center' }}>
        <div className={`hero-bg-layer${fading?' fade-out':''}`}>
          <Image src={currentSlide.bg} alt="Mema Campus" fill style={{ objectFit:'cover', objectPosition:'center top' }} priority />
          <div className="hero-overlay" />
        </div>

        <div className={`hero-content-layer${fading?' fade-out':''}`}>
          <div className="hero-tag-pill"><Star className="hero-star-icon" /> {currentSlide.tag}</div>
          <h1 className="hero-h1">{currentSlide.title} <span className="hero-highlight">{currentSlide.highlight}</span></h1>
          <p className="hero-paragraph">{currentSlide.sub}</p>
          <div className="hero-cta-row">
            <a href="http://localhost:3001" className="hero-cta-primary">Apply for Sep 2026 <ArrowRight style={{ display:'inline', width:'1.1em', height:'1.1em', marginLeft:'0.4em' }} /></a>
            <a href="#programmes" className="hero-cta-outline"><Play style={{ display:'inline', width:'1em', height:'1em', marginRight:'0.4em' }} /> Explore Programmes</a>
          </div>
        </div>

        {/* Quick portal widgets at hero bottom */}
        <div className="hero-quick-portals">
          <a href="http://localhost:3001" className="hqp-item hqp-apply">
            <span className="hqp-icon">📝</span>
            <div><div className="hqp-title">Apply Now</div><div className="hqp-sub">Sep 2026 Intake</div></div>
          </a>
          <a href="http://localhost:3002" className="hqp-item hqp-student">
            <span className="hqp-icon">🎓</span>
            <div><div className="hqp-title">Student Portal</div><div className="hqp-sub">Results & Records</div></div>
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

        {/* Dots */}
        <div className="hero-dots-bar">
          <button className="hero-arrow-btn" onClick={() => changeSlide((slide-1+heroSlides.length)%heroSlides.length)}><ChevronLeft /></button>
          <div className="hero-dots-list">{heroSlides.map((_,i)=><button key={i} className={`hero-dot${i===slide?' active':''}`} onClick={()=>changeSlide(i)} />)}</div>
          <button className="hero-arrow-btn" onClick={() => changeSlide((slide+1)%heroSlides.length)}><ChevronRight /></button>
        </div>
      </section>

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

      {/* ══ ABOUT ═══════════════════════════════════════════════════════════ */}
      <section className="about-section">
        <div className="about-wrap">
          <div className="about-img-col">
            <div className="about-img-container">
              <Image src="/about-students.jpg" alt="Mema University Students" fill style={{ objectFit:'cover', borderRadius:'1.25rem' }} />
            </div>
            <div className="about-badge">
              <span style={{ fontSize:'1.75rem' }}>🎓</span>
              <div><div className="about-badge-val">Est. 2008</div><div className="about-badge-sub">16+ Years of Excellence</div></div>
            </div>
          </div>

          <div className="about-text-col">
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
              <div key={i} className={`why-card why-${color}`}>
                <div className={`why-icon-box why-icon-${color}`}><Icon className="why-icon" /></div>
                <h3 className="why-title">{title}</h3>
                <p className="why-desc">{desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ══ PROGRAMMES ══════════════════════════════════════════════════════ */}
      <section id="programmes" className="prog-section">
        <div className="section-center-wrap">
          <div className="section-tag-label">Academic Programmes</div>
          <h2 className="section-h2">Find Your <span className="text-accent">Academic Path</span></h2>
          <p className="section-subtext">Industry-aligned curricula crafted with leading technology companies and research institutions.</p>
          <div className="prog-grid">
            {programmes.map((prog, i) => {
              const Icon = prog.icon;
              return (
                <div key={i} className={`prog-card prog-${prog.color}`}>
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
            <a href="#" className="view-all-btn">View All 42 Programmes <ChevronRight className="btn-icon-inline" /></a>
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
              <div key={i} className={`testimonial-card${i===1?' testimonial-featured':''}`}>
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
              <div key={i} className="event-card">
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
              <article key={i} className="news-card">
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
          <div className="cta-text-col">
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

    </div>
  );
}
