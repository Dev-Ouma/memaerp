'use client';

import React, { useState, useEffect, useRef } from 'react';
import Image from 'next/image';
import {
  ArrowRight,
  BookOpen,
  CheckCircle2,
  Building,
  ShieldCheck,
  Award,
  ChevronRight,
  ChevronLeft,
  Phone,
  Mail,
  MapPin,
  Users,
  GraduationCap,
  FlaskConical,
  Globe,
  Calendar,
  Clock,
  Play,
  Star,
  Microscope,
  Code2,
  BrainCircuit,
  Landmark,
} from 'lucide-react';

// ─── Hero Slides ─────────────────────────────────────────────────────────────
const heroSlides = [
  {
    id: 1,
    tag: 'September 2026 Admissions Open',
    title: 'Empowering the Next Generation of',
    highlight: 'Innovators & Leaders',
    sub: 'World-class undergraduate and postgraduate programmes in computing, software engineering, business, and data sciences.',
    bg: '/hero-campus.jpg',
  },
  {
    id: 2,
    tag: 'CUE Accredited Programmes',
    title: 'Pioneering Research &',
    highlight: 'Innovation in East Africa',
    sub: 'Join over 5,400 students and 300+ faculty members shaping the digital future of our continent.',
    bg: '/about-students.jpg',
  },
  {
    id: 3,
    tag: 'State-of-the-Art Facilities',
    title: 'Learn, Discover &',
    highlight: 'Transform the World',
    sub: 'Modern labs, guaranteed industry attachments, HELB support, and 96% graduate employment rate.',
    bg: '/hero-campus.jpg',
  },
];

// ─── Stats ────────────────────────────────────────────────────────────────────
const statsData = [
  { value: 5400, suffix: '+', label: 'Active Students' },
  { value: 96, suffix: '%', label: 'Employment Rate' },
  { value: 42, suffix: '+', label: 'Degree Programmes' },
  { value: 300, suffix: '+', label: 'Faculty Members' },
];

// ─── Programmes ───────────────────────────────────────────────────────────────
const programmes = [
  { code: 'BSc. CS', icon: Code2, title: 'BSc. Computer Science', duration: '4 Years', units: 120, color: 'teal', dept: 'Faculty of Computing & IT' },
  { code: 'BSc. SE', icon: BrainCircuit, title: 'BSc. Software Engineering', duration: '4 Years', units: 128, color: 'green', dept: 'Faculty of Computing & IT' },
  { code: 'BSc. DS', icon: FlaskConical, title: 'BSc. Data Science & AI', duration: '4 Years', units: 120, color: 'orange', dept: 'School of Data Sciences' },
  { code: 'BBA', icon: Landmark, title: 'BBA Business Administration', duration: '3 Years', units: 108, color: 'teal', dept: 'School of Business & Economics' },
  { code: 'MSc. CS', icon: Microscope, title: 'MSc. Computer Science', duration: '2 Years', units: 64, color: 'green', dept: 'Postgraduate Studies' },
  { code: 'MSc. AI', icon: Globe, title: 'MSc. Artificial Intelligence', duration: '2 Years', units: 60, color: 'orange', dept: 'Postgraduate Studies' },
];

const researchAreas = [
  { icon: BrainCircuit, title: 'Artificial Intelligence & ML', desc: 'Cutting-edge research in deep learning, NLP, computer vision, and AI ethics for real-world applications.', count: '12 Active Projects' },
  { icon: Globe, title: 'Cybersecurity & Networks', desc: 'Research on threat intelligence, blockchain security, zero-trust architectures, and national cyber resilience.', count: '8 Active Projects' },
  { icon: FlaskConical, title: 'Biomedical Informatics', desc: 'Applying data science and computing to healthcare, genomics, and disease surveillance in Africa.', count: '6 Active Projects' },
];

const eventsData = [
  { day: '28', month: 'AUG', title: 'September 2026 Intake — Open Day', time: '9:00 AM – 4:00 PM', location: 'Main Campus, Nairobi', cat: 'Admissions' },
  { day: '05', month: 'SEP', title: 'Annual Hackathon: AI for Africa', time: '8:00 AM – 8:00 PM', location: 'Tech Innovation Hub', cat: 'Competition' },
  { day: '12', month: 'SEP', title: 'Prof. Kamau Lecture: Future of Fintech', time: '2:00 PM – 5:00 PM', location: 'Auditorium A, Block C', cat: 'Lecture' },
];

// ─── Animated Counter ─────────────────────────────────────────────────────────
function AnimatedCounter({ target, suffix }: { target: number; suffix: string }) {
  const [count, setCount] = useState(0);
  const ref = useRef<HTMLSpanElement>(null);
  const started = useRef(false);

  useEffect(() => {
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting && !started.current) {
          started.current = true;
          let start = 0;
          const duration = 2000;
          const step = target / (duration / 16);
          const timer = setInterval(() => {
            start += step;
            if (start >= target) { setCount(target); clearInterval(timer); }
            else { setCount(Math.floor(start)); }
          }, 16);
        }
      },
      { threshold: 0.3 }
    );
    if (ref.current) observer.observe(ref.current);
    return () => observer.disconnect();
  }, [target]);

  return <span ref={ref}>{count.toLocaleString()}{suffix}</span>;
}

// ─── Page Component ───────────────────────────────────────────────────────────
export default function PublicWebsiteHomePage() {
  const [currentSlide, setCurrentSlide] = useState(0);
  const [fading, setFading] = useState(false);

  useEffect(() => {
    const interval = setInterval(() => {
      changeSlide((currentSlide + 1) % heroSlides.length);
    }, 6500);
    return () => clearInterval(interval);
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [currentSlide]);

  function changeSlide(next: number) {
    setFading(true);
    setTimeout(() => { setCurrentSlide(next); setFading(false); }, 500);
  }

  const slide = heroSlides[currentSlide];

  return (
    <div className="mema-site">

      {/* UTILITY BAR */}
      <div className="utility-bar">
        <div className="utility-inner">
          <div className="utility-left">
            <a href="tel:+254208920000" className="utility-link"><Phone className="util-icon" /> +254 20 892 000</a>
            <a href="mailto:info@mema.ac.ke" className="utility-link"><Mail className="util-icon" /> info@mema.ac.ke</a>
            <span className="utility-link hidden md:inline-flex"><MapPin className="util-icon" /> Nairobi, Kenya</span>
          </div>
          <div className="utility-right">
            <a href="http://localhost:3002" className="util-portal-btn"><Users className="util-icon" /> Student Portal</a>
            <a href="http://localhost:3005" className="util-portal-btn"><ShieldCheck className="util-icon" /> Staff Portal</a>
            <a href="http://localhost:3001" className="util-apply-btn">Apply Now <ArrowRight className="util-icon" /></a>
          </div>
        </div>
      </div>

      {/* HERO */}
      <section className="hero-section" style={{ position: 'relative', height: '100vh', minHeight: '600px', overflow: 'hidden' }}>
        <div className={`hero-bg-layer${fading ? ' fade-out' : ''}`}>
          <Image src={slide.bg} alt="Mema Campus" fill style={{ objectFit: 'cover', objectPosition: 'center top' }} priority />
          <div className="hero-overlay" />
        </div>

        <div className={`hero-content-layer${fading ? ' fade-out' : ''}`}>
          <div className="hero-tag-pill"><Star className="hero-star-icon" /> {slide.tag}</div>
          <h1 className="hero-h1">
            {slide.title} <span className="hero-highlight">{slide.highlight}</span>
          </h1>
          <p className="hero-paragraph">{slide.sub}</p>
          <div className="hero-cta-row">
            <a href="http://localhost:3001" className="hero-cta-primary">Apply for Sep 2026 <ArrowRight style={{ display:'inline', width:'1.1em', height:'1.1em', marginLeft:'0.4em' }} /></a>
            <a href="#programmes" className="hero-cta-outline"><Play style={{ display:'inline', width:'1em', height:'1em', marginRight:'0.4em' }} /> Explore Programmes</a>
          </div>
        </div>

        {/* Slide Dots */}
        <div className="hero-dots-bar">
          <button className="hero-arrow-btn" onClick={() => changeSlide((currentSlide - 1 + heroSlides.length) % heroSlides.length)}><ChevronLeft /></button>
          <div className="hero-dots-list">
            {heroSlides.map((_, i) => (
              <button key={i} className={`hero-dot${i === currentSlide ? ' active' : ''}`} onClick={() => changeSlide(i)} />
            ))}
          </div>
          <button className="hero-arrow-btn" onClick={() => changeSlide((currentSlide + 1) % heroSlides.length)}><ChevronRight /></button>
        </div>
      </section>

      {/* STATS STRIP */}
      <section className="stats-strip">
        {statsData.map((s, i) => (
          <div key={i} className="stat-block">
            <div className="stat-number"><AnimatedCounter target={s.value} suffix={s.suffix} /></div>
            <div className="stat-label">{s.label}</div>
          </div>
        ))}
      </section>

      {/* ABOUT */}
      <section className="about-section">
        <div className="about-wrap">
          <div className="about-img-col">
            <div className="about-img-container">
              <Image src="/about-students.jpg" alt="Mema University Students" fill style={{ objectFit: 'cover', borderRadius: '1.25rem' }} />
            </div>
            <div className="about-badge">
              <GraduationCap className="about-badge-icon" />
              <div><div className="about-badge-val">Est. 2008</div><div className="about-badge-sub">16+ Years of Excellence</div></div>
            </div>
          </div>

          <div className="about-text-col">
            <div className="section-tag-label">About Mema University</div>
            <h2 className="section-h2">A Legacy of Academic <span className="text-accent">Excellence</span> in East Africa</h2>
            <p className="about-para">Founded in 2008, Mema University has grown into one of East Africa&apos;s leading technology-focused universities. Our mission is to produce world-class graduates, drive innovation, and deliver research that transforms society.</p>
            <ul className="about-checklist">
              {['CUE Accredited — recognized nationally and internationally','State-of-the-art computing labs and IoT research hubs','Guaranteed 3-month industry attachment for all students','HELB, bursary, and merit scholarship integration','96% graduate employment rate within 6 months'].map((p, i) => (
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

      {/* WHY CHOOSE US */}
      <section className="why-section">
        <div className="section-center-wrap">
          <div className="section-tag-label center-text">Why Choose Mema?</div>
          <h2 className="section-h2 center-text">The Mema <span className="text-accent">Advantage</span></h2>
          <p className="section-subtext center-text">We blend academic rigour, cutting-edge facilities, and strong industry partnerships.</p>
          <div className="why-grid">
            {[
              { icon: Building, color: 'teal', title: 'Modern Research Labs', desc: 'High-performance computing clusters, IoT testing labs, and gigabit campus-wide fiber connectivity.' },
              { icon: ShieldCheck, color: 'green', title: 'Industry Attachment', desc: 'Mandatory 3-month supervised practicum with leading tech companies and multinationals.' },
              { icon: Award, color: 'orange', title: 'Scholarships & Aid', desc: 'Direct HELB integration, institutional bursaries, and merit scholarships for top achievers.' },
              { icon: Globe, color: 'teal', title: 'Global Partnerships', desc: 'Academic collaborations with universities in Kenya, Europe, and North America.' },
              { icon: Users, color: 'green', title: 'Expert Faculty', desc: 'Over 300 faculty members combining academic excellence with industry experience.' },
              { icon: BrainCircuit, color: 'orange', title: 'AI-Ready Curriculum', desc: 'Programmes designed with AI integration, data science, and emerging tech at their core.' },
            ].map(({ icon: Icon, color, title, desc }, i) => (
              <div key={i} className={`why-card why-${color}`}>
                <div className={`why-icon-box why-icon-${color}`}><Icon className="why-icon" /></div>
                <h3 className="why-title">{title}</h3>
                <p className="why-desc">{desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* PROGRAMMES */}
      <section id="programmes" className="prog-section">
        <div className="section-center-wrap">
          <div className="section-tag-label">Academic Programmes</div>
          <h2 className="section-h2">Find Your <span className="text-accent">Academic Path</span></h2>
          <p className="section-subtext">Industry-aligned curricula crafted in collaboration with leading technology companies.</p>
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
          <div style={{ textAlign: 'center', marginTop: '2.5rem' }}>
            <a href="#" className="view-all-btn">View All 42 Programmes <ChevronRight className="btn-icon-inline" /></a>
          </div>
        </div>
      </section>

      {/* RESEARCH */}
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

      {/* EVENTS */}
      <section className="events-section">
        <div className="section-center-wrap">
          <div className="section-tag-label">Upcoming Events</div>
          <h2 className="section-h2">Stay <span className="text-accent">Connected</span></h2>
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

      {/* CTA BANNER */}
      <section className="cta-section">
        <div className="cta-inner-wrap">
          <div className="cta-text-col">
            <h2 className="cta-h2">Begin Your University Journey Today</h2>
            <p className="cta-para">Applications for September 2026 are open. Complete your registration in under 10 minutes.</p>
          </div>
          <div className="cta-btn-col">
            <a href="http://localhost:3001" className="cta-btn-primary">Start Application <ArrowRight className="btn-icon-inline" /></a>
            <a href="tel:+254208920000" className="cta-btn-outline"><Phone className="btn-icon-inline" /> Call Admissions</a>
          </div>
        </div>
      </section>

    </div>
  );
}
