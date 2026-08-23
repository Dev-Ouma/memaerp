/**
 * Editorial content for the public site.
 *
 * This is deliberately a typed module rather than fetched data. The public website has no
 * authenticated user and no per-request variation, so every page here is statically rendered
 * at build time — which makes it fast, cheap to host behind Cloudflare, and impossible to take
 * down by overloading the API. When the CMS module lands, these exports become the shape that
 * the CMS endpoint returns, and the pages themselves do not change.
 */

import type { LucideIcon } from 'lucide-react';
import {
  Award, Binary, BrainCircuit, Building2, Cpu, FlaskConical, Globe2, GraduationCap,
  HeartPulse, Landmark, Leaf, LineChart, Microscope, Network, Rocket, ShieldCheck,
  Sparkles, Users, Wallet, Wrench,
} from 'lucide-react';

// ─── Hero ─────────────────────────────────────────────────────────────────────

export type HeroSlide = {
  id: number;
  eyebrow: string;
  title: string;
  highlight: string;
  body: string;
  image: string;
};

export const heroSlides: ReadonlyArray<HeroSlide> = [
  {
    id: 1,
    eyebrow: 'September 2026 admissions are open',
    title: 'Empowering the next generation of',
    highlight: 'innovators and leaders',
    body: 'World-class undergraduate and postgraduate programmes in computing, software engineering, business and data science.',
    image: '/hero-campus.jpg',
  },
  {
    id: 2,
    eyebrow: 'CUE-accredited programmes',
    title: 'Pioneering research and',
    highlight: 'innovation in East Africa',
    body: 'Join more than 5,400 students and 300 faculty members shaping the digital future of the continent.',
    image: '/about-students.jpg',
  },
  {
    id: 3,
    eyebrow: 'State-of-the-art facilities',
    title: 'Experience the future of',
    highlight: 'education and discovery',
    body: 'Modern AI labs, IoT hubs, guaranteed industry attachment, HELB support and 96% graduate employment.',
    image: '/campus-life.jpg',
  },
];

export const ticker: ReadonlyArray<string> = [
  'September 2026 intake applications close on 30 August 2026',
  'Mema University ranked first among East African technology universities, 2026',
  'Annual hackathon “AI for Africa” — registration closes 1 September 2026',
  'The AI & Machine Learning Research Centre is now open to postgraduate students',
  '85 students placed on industry attachment with partner firms this semester',
  'HELB loan applications are open through the student portal',
];

// ─── Statistics ───────────────────────────────────────────────────────────────

export type Stat = { value: number; suffix: string; label: string; icon: LucideIcon };

export const stats: ReadonlyArray<Stat> = [
  { value: 5400, suffix: '+', label: 'Active students', icon: Users },
  { value: 96, suffix: '%', label: 'Employment within 6 months', icon: LineChart },
  { value: 42, suffix: '+', label: 'Accredited programmes', icon: GraduationCap },
  { value: 300, suffix: '+', label: 'Faculty members', icon: Microscope },
];

// ─── Programmes ───────────────────────────────────────────────────────────────

export type ProgrammeLevel = 'undergraduate' | 'postgraduate' | 'diploma';
export type FacultyKey = 'computing' | 'business' | 'engineering' | 'data';

export type Programme = {
  slug: string;
  code: string;
  title: string;
  level: ProgrammeLevel;
  faculty: FacultyKey;
  facultyName: string;
  duration: string;
  units: number;
  feesPerSemester: number;
  icon: LucideIcon;
  summary: string;
  outcomes: ReadonlyArray<string>;
  entry: ReadonlyArray<string>;
  careers: ReadonlyArray<string>;
};

export const programmes: ReadonlyArray<Programme> = [
  {
    slug: 'bsc-computer-science',
    code: 'BSc CS',
    title: 'BSc Computer Science',
    level: 'undergraduate',
    faculty: 'computing',
    facultyName: 'Faculty of Computing & IT',
    duration: '4 years',
    units: 120,
    feesPerSemester: 85000,
    icon: Binary,
    summary:
      'A rigorous grounding in algorithms, systems and theory, with substantial practical work from the first semester.',
    outcomes: [
      'Design and analyse algorithms with formal reasoning about correctness and complexity',
      'Build and operate distributed systems on modern cloud infrastructure',
      'Apply statistical and machine-learning methods to real datasets',
      'Work effectively in a professional engineering team using version control and code review',
    ],
    entry: [
      'KCSE mean grade C+ or above',
      'C+ in Mathematics and C+ in English or Kiswahili',
      'C in Physics or Chemistry or Biology',
    ],
    careers: ['Software engineer', 'Systems architect', 'Research scientist', 'Data engineer'],
  },
  {
    slug: 'bsc-software-engineering',
    code: 'BSc SE',
    title: 'BSc Software Engineering',
    level: 'undergraduate',
    faculty: 'computing',
    facultyName: 'Faculty of Computing & IT',
    duration: '4 years',
    units: 128,
    feesPerSemester: 88000,
    icon: Cpu,
    summary:
      'Engineering practice at scale — requirements, architecture, testing and delivery — anchored by two industry projects.',
    outcomes: [
      'Elicit requirements and translate them into a defensible architecture',
      'Apply automated testing and continuous delivery to production systems',
      'Measure and improve software quality using objective metrics',
      'Lead a delivery team through a full development lifecycle',
    ],
    entry: [
      'KCSE mean grade C+ or above',
      'C+ in Mathematics and C+ in English or Kiswahili',
      'C in Physics or Chemistry',
    ],
    careers: ['Software engineer', 'DevOps engineer', 'Engineering manager', 'Quality lead'],
  },
  {
    slug: 'bsc-data-science-ai',
    code: 'BSc DS',
    title: 'BSc Data Science & Artificial Intelligence',
    level: 'undergraduate',
    faculty: 'data',
    facultyName: 'School of Data Sciences',
    duration: '4 years',
    units: 120,
    feesPerSemester: 92000,
    icon: BrainCircuit,
    summary:
      'Statistics, machine learning and data engineering applied to problems that matter in East African industry and public health.',
    outcomes: [
      'Build reproducible data pipelines from ingestion through to serving',
      'Select, train and evaluate models with honest treatment of uncertainty',
      'Communicate quantitative findings to a non-technical audience',
      'Recognise and mitigate bias and privacy risk in applied AI',
    ],
    entry: [
      'KCSE mean grade C+ or above',
      'B- in Mathematics',
      'C+ in English or Kiswahili',
    ],
    careers: ['Data scientist', 'ML engineer', 'Quantitative analyst', 'Research fellow'],
  },
  {
    slug: 'bba-business-administration',
    code: 'BBA',
    title: 'BBA Business Administration',
    level: 'undergraduate',
    faculty: 'business',
    facultyName: 'School of Business & Economics',
    duration: '3 years',
    units: 108,
    feesPerSemester: 72000,
    icon: Landmark,
    summary:
      'Management, finance and strategy taught through Kenyan case work, with a digital-business core throughout.',
    outcomes: [
      'Read and interpret financial statements to support a decision',
      'Build and defend a business case under uncertainty',
      'Apply marketing and operations frameworks to a live organisation',
      'Understand the regulatory environment for business in Kenya',
    ],
    entry: ['KCSE mean grade C+ or above', 'C+ in Mathematics', 'C+ in English or Kiswahili'],
    careers: ['Business analyst', 'Management trainee', 'Product manager', 'Entrepreneur'],
  },
  {
    slug: 'beng-electrical-electronic',
    code: 'BEng EEE',
    title: 'BEng Electrical & Electronic Engineering',
    level: 'undergraduate',
    faculty: 'engineering',
    facultyName: 'School of Engineering',
    duration: '5 years',
    units: 152,
    feesPerSemester: 96000,
    icon: Wrench,
    summary:
      'An Engineers Board of Kenya-aligned programme covering power systems, electronics, control and embedded design.',
    outcomes: [
      'Design and analyse analogue and digital electronic circuits',
      'Model and control dynamic systems',
      'Design power distribution to national and IEC standards',
      'Deliver an original capstone engineering project',
    ],
    entry: [
      'KCSE mean grade B- or above',
      'B in Mathematics and B- in Physics',
      'C+ in Chemistry and C+ in English or Kiswahili',
    ],
    careers: ['Electrical engineer', 'Embedded systems engineer', 'Power systems analyst', 'Controls engineer'],
  },
  {
    slug: 'msc-computer-science',
    code: 'MSc CS',
    title: 'MSc Computer Science',
    level: 'postgraduate',
    faculty: 'computing',
    facultyName: 'Postgraduate Studies',
    duration: '2 years',
    units: 64,
    feesPerSemester: 128000,
    icon: Microscope,
    summary:
      'Advanced coursework plus a supervised research thesis, with taught tracks in systems, security and intelligent systems.',
    outcomes: [
      'Conduct an independent literature review to publication standard',
      'Design and execute a defensible empirical evaluation',
      'Contribute original work to a peer-reviewed venue',
      'Supervise and review the technical work of others',
    ],
    entry: [
      'Second class honours (upper division) in a computing discipline',
      'Two academic references',
      'A research statement of 1,000–1,500 words',
    ],
    careers: ['Research scientist', 'Principal engineer', 'Lecturer', 'Doctoral candidate'],
  },
  {
    slug: 'msc-artificial-intelligence',
    code: 'MSc AI',
    title: 'MSc Artificial Intelligence',
    level: 'postgraduate',
    faculty: 'data',
    facultyName: 'Postgraduate Studies',
    duration: '2 years',
    units: 60,
    feesPerSemester: 136000,
    icon: Sparkles,
    summary:
      'Deep learning, natural language processing and AI safety, taught by the team behind the Swahili language-model programme.',
    outcomes: [
      'Train and fine-tune modern neural architectures',
      'Evaluate models for robustness, fairness and privacy',
      'Build language technology for low-resource African languages',
      'Reason about the societal impact of deployed AI systems',
    ],
    entry: [
      'Second class honours (upper division) in a quantitative discipline',
      'Demonstrated programming ability in Python',
      'A research statement of 1,000–1,500 words',
    ],
    careers: ['AI research engineer', 'NLP specialist', 'AI policy adviser', 'Doctoral candidate'],
  },
  {
    slug: 'dip-information-technology',
    code: 'Dip IT',
    title: 'Diploma in Information Technology',
    level: 'diploma',
    faculty: 'computing',
    facultyName: 'Faculty of Computing & IT',
    duration: '2 years',
    units: 56,
    feesPerSemester: 48000,
    icon: Network,
    summary:
      'A practical route into the technology industry, and a recognised credit-bearing pathway into the BSc programmes.',
    outcomes: [
      'Administer networks and end-user computing environments',
      'Build and deploy database-backed web applications',
      'Apply structured troubleshooting to live incidents',
      'Progress with credit transfer into a bachelor programme',
    ],
    entry: ['KCSE mean grade C- or above', 'D+ in Mathematics', 'C- in English or Kiswahili'],
    careers: ['IT support specialist', 'Network technician', 'Junior developer', 'Progression to BSc'],
  },
];

export const facultyLabels: Record<FacultyKey, string> = {
  computing: 'Computing & IT',
  business: 'Business & Economics',
  engineering: 'Engineering',
  data: 'Data Sciences',
};

export const levelLabels: Record<ProgrammeLevel, string> = {
  undergraduate: 'Undergraduate',
  postgraduate: 'Postgraduate',
  diploma: 'Diploma & Certificate',
};

// ─── Why Mema ─────────────────────────────────────────────────────────────────

export type Feature = { icon: LucideIcon; title: string; body: string };

export const advantages: ReadonlyArray<Feature> = [
  { icon: Building2, title: 'Modern research labs', body: 'High-performance computing clusters, IoT test benches and gigabit fibre across the campus.' },
  { icon: ShieldCheck, title: 'Industry attachment', body: 'A mandatory three-month supervised practicum with partner technology firms and multinationals.' },
  { icon: Wallet, title: 'Scholarships and HELB', body: 'Direct HELB integration, institutional bursaries and merit scholarships assessed each semester.' },
  { icon: Globe2, title: 'Global partnerships', body: 'Academic exchange with universities across Kenya, Europe and North America.' },
  { icon: Rocket, title: 'Innovation incubator', body: 'The Mema Startup Hub has launched fourteen student-led ventures since 2022.' },
  { icon: Award, title: 'Award-winning faculty', body: 'More than 300 academic staff, 40% of whom hold international research recognition.' },
];

// ─── Mission, vision, values ──────────────────────────────────────────────────

export const mission = {
  mission:
    'To produce graduates who are technically excellent, ethically grounded and immediately useful to the economies they enter — through teaching that is current, research that is rigorous, and partnership with the industries that employ our students.',
  vision:
    'To be the reference point for technology education in East Africa: the university that regional industry recruits from first and that regional government consults first.',
};

export const values: ReadonlyArray<Feature> = [
  { icon: Microscope, title: 'Rigour', body: 'We teach what is true and we teach students how to tell. Claims are evidenced; results are reproducible.' },
  { icon: HeartPulse, title: 'Integrity', body: 'Academic honesty is not a policy document here. It is the condition of holding a Mema qualification.' },
  { icon: Users, title: 'Inclusion', body: 'Admission is on merit and potential. Financial background is a problem we solve, not a filter we apply.' },
  { icon: Leaf, title: 'Responsibility', body: 'Our research is judged by whether it improves something real for people in this region.' },
];

export const milestones: ReadonlyArray<{ year: string; title: string; body: string }> = [
  { year: '2008', title: 'Foundation', body: 'Mema opens with 180 students and two departments, on a single Thika Road campus.' },
  { year: '2012', title: 'CUE charter', body: 'Full accreditation by the Commission for University Education across all offered programmes.' },
  { year: '2016', title: 'School of Engineering', body: 'Engineering opens with Engineers Board of Kenya-aligned curricula and a dedicated workshop block.' },
  { year: '2020', title: 'Remote delivery', body: 'The university moves 100% of teaching online within eleven days and retains a 94% completion rate.' },
  { year: '2022', title: 'Startup Hub', body: 'The innovation incubator opens; fourteen student ventures have since been founded there.' },
  { year: '2024', title: 'AI Research Centre', body: 'The AI & Machine Learning Research Centre opens with a dedicated GPU cluster.' },
];

// ─── Leadership ───────────────────────────────────────────────────────────────

export type Leader = { name: string; role: string; qualification: string };

export const leadership: ReadonlyArray<Leader> = [
  { name: 'Prof. Esther Nyambura', role: 'Vice-Chancellor', qualification: 'PhD Computer Science, DPhil' },
  { name: 'Prof. Daniel Mulinge', role: 'Deputy Vice-Chancellor, Academic Affairs', qualification: 'PhD Information Systems' },
  { name: 'Dr. Achieng Odhiambo', role: 'Dean, Faculty of Computing & IT', qualification: 'PhD Distributed Systems' },
  { name: 'Dr. Peter Kariuki', role: 'Dean, School of Engineering', qualification: 'PhD Electrical Engineering' },
  { name: 'Dr. Miriam Chebet', role: 'Dean, School of Business & Economics', qualification: 'PhD Development Economics' },
  { name: 'Mr. Joseph Otieno', role: 'University Registrar', qualification: 'MSc Education Management' },
  { name: 'Ms. Faith Nduta', role: 'Finance Director', qualification: 'MBA, CPA(K)' },
  { name: 'Dr. Samuel Kiptoo', role: 'Director, Research & Innovation', qualification: 'PhD Machine Learning' },
];

// ─── Research ─────────────────────────────────────────────────────────────────

export type ResearchArea = {
  slug: string;
  icon: LucideIcon;
  tag: string;
  title: string;
  body: string;
  projects: number;
  lead: string;
};

export const researchAreas: ReadonlyArray<ResearchArea> = [
  {
    slug: 'ai-ml',
    icon: BrainCircuit,
    tag: 'AI & ML',
    title: 'Artificial intelligence and machine learning',
    body: 'Deep learning, natural language processing for low-resource African languages, computer vision, and the ethics of deployed AI.',
    projects: 12,
    lead: 'Dr. Samuel Kiptoo',
  },
  {
    slug: 'cybersecurity',
    icon: ShieldCheck,
    tag: 'Security',
    title: 'Cybersecurity and network resilience',
    body: 'Threat intelligence, zero-trust architecture, cryptographic protocol analysis and national critical-infrastructure resilience.',
    projects: 8,
    lead: 'Dr. Achieng Odhiambo',
  },
  {
    slug: 'biomedical-informatics',
    icon: FlaskConical,
    tag: 'Health tech',
    title: 'Biomedical informatics',
    body: 'Applying data science to healthcare delivery, genomics and disease surveillance across East Africa.',
    projects: 6,
    lead: 'Prof. Esther Nyambura',
  },
  {
    slug: 'sustainable-computing',
    icon: Leaf,
    tag: 'Sustainability',
    title: 'Sustainable and off-grid computing',
    body: 'Low-power edge devices, solar-backed micro-datacentres and computing infrastructure for intermittent grids.',
    projects: 5,
    lead: 'Dr. Peter Kariuki',
  },
  {
    slug: 'fintech',
    icon: LineChart,
    tag: 'Fintech',
    title: 'Financial technology and inclusion',
    body: 'Mobile money infrastructure, credit scoring for thin-file borrowers and the economics of digital payments.',
    projects: 7,
    lead: 'Dr. Miriam Chebet',
  },
  {
    slug: 'iot',
    icon: Network,
    tag: 'IoT',
    title: 'Internet of things and smart infrastructure',
    body: 'Sensor networks for agriculture, water management and urban transport, designed for low-bandwidth environments.',
    projects: 9,
    lead: 'Dr. Peter Kariuki',
  },
];

export const researchCentres: ReadonlyArray<{ name: string; body: string; established: string }> = [
  { name: 'AI & Machine Learning Research Centre', body: 'A 96-GPU cluster supporting model training for the Swahili language-model programme and partner projects.', established: '2024' },
  { name: 'Cybersecurity Operations Lab', body: 'An isolated range for malware analysis, incident-response training and protocol testing.', established: '2021' },
  { name: 'IoT and Embedded Systems Workshop', body: 'Prototyping, PCB fabrication and environmental test facilities for field-deployed sensors.', established: '2019' },
  { name: 'Mema Startup Hub', body: 'Incubation space, seed funding and mentorship for student and alumni ventures.', established: '2022' },
];

export const publications: ReadonlyArray<{ title: string; venue: string; year: string; authors: string }> = [
  { title: 'Low-resource language modelling for Swahili: data, benchmarks and evaluation', venue: 'NeurIPS', year: '2026', authors: 'Kiptoo S., Nyambura E., Wanjiku A.' },
  { title: 'Zero-trust segmentation for university networks under constrained budgets', venue: 'IEEE Security & Privacy', year: '2025', authors: 'Odhiambo A., Otieno J.' },
  { title: 'Credit scoring for thin-file borrowers using mobile-money transaction graphs', venue: 'Journal of Development Economics', year: '2025', authors: 'Chebet M., Hassan F.' },
  { title: 'Solar-backed micro-datacentre design for intermittent grid environments', venue: 'ACM e-Energy', year: '2025', authors: 'Kariuki P., Mulinge D.' },
];

// ─── Admissions ───────────────────────────────────────────────────────────────

export const admissionSteps: ReadonlyArray<{ step: string; title: string; body: string }> = [
  { step: '01', title: 'Create your account', body: 'Register on the applicant portal with your national ID or birth certificate number and a working email address.' },
  { step: '02', title: 'Choose your programme', body: 'Select up to three programmes in order of preference. The portal shows you the entry requirements for each as you choose.' },
  { step: '03', title: 'Upload your documents', body: 'KCSE certificate or result slip, national ID, passport photograph, and transcripts for postgraduate or transfer applications.' },
  { step: '04', title: 'Pay the application fee', body: 'KES 2,000 for undergraduate and KES 3,500 for postgraduate, payable by M-Pesa directly in the portal.' },
  { step: '05', title: 'Track your application', body: 'The portal shows each stage as it completes. Decisions are issued within fifteen working days of a complete application.' },
  { step: '06', title: 'Accept and enrol', body: 'Accept your offer in the portal, pay the enrolment deposit, and your student account is created automatically.' },
];

export const feeSchedule: ReadonlyArray<{ level: string; tuition: string; other: string; total: string }> = [
  { level: 'Diploma', tuition: 'KES 48,000', other: 'KES 9,500', total: 'KES 57,500' },
  { level: 'Undergraduate — Business', tuition: 'KES 72,000', other: 'KES 12,000', total: 'KES 84,000' },
  { level: 'Undergraduate — Computing', tuition: 'KES 85,000', other: 'KES 12,000', total: 'KES 97,000' },
  { level: 'Undergraduate — Engineering', tuition: 'KES 96,000', other: 'KES 14,500', total: 'KES 110,500' },
  { level: 'Postgraduate — taught', tuition: 'KES 128,000', other: 'KES 15,000', total: 'KES 143,000' },
];

export const faqs: ReadonlyArray<{ question: string; answer: string }> = [
  {
    question: 'When does the September 2026 intake close?',
    answer: 'Applications close on 30 August 2026. Late applications are considered only where a programme still has capacity, and are assessed in the order received.',
  },
  {
    question: 'Do you accept KUCCPS placements?',
    answer: 'Yes. Government-sponsored students placed at Mema through KUCCPS are admitted directly and do not pay an application fee. You will still need to complete enrolment in the applicant portal so that your student record is created.',
  },
  {
    question: 'Can I apply with a result slip instead of a certificate?',
    answer: 'Yes. A KNEC result slip is accepted for the application. The original certificate must be presented at enrolment, and admission is provisional until it is verified.',
  },
  {
    question: 'How does HELB funding work at Mema?',
    answer: 'Mema is a HELB-approved institution. Once you have a Mema student number you can apply through the HELB portal, and disbursements are posted directly against your fee account. The finance office can confirm your status at any point in the semester.',
  },
  {
    question: 'Is there accommodation on campus?',
    answer: 'The main campus has 1,200 hostel beds allocated by application, with priority given to first-year students and students whose home county is outside Nairobi. The accommodation office maintains a vetted list of nearby private hostels for everyone else.',
  },
  {
    question: 'Can I transfer credit from another university?',
    answer: 'Credit transfer is assessed by the faculty against the Mema curriculum, unit by unit. Up to 50% of a programme may be granted as transfer credit where the content and assessment are judged equivalent. Apply with full transcripts and unit descriptions.',
  },
  {
    question: 'Are evening and weekend classes available?',
    answer: 'Yes. Business and computing programmes run evening cohorts from 5:30 PM and Saturday cohorts, both following the same curriculum and assessment as the day programme.',
  },
];

// ─── News and events ──────────────────────────────────────────────────────────

export type NewsItem = {
  slug: string;
  category: string;
  date: string;
  isoDate: string;
  title: string;
  excerpt: string;
  image: string;
  body: ReadonlyArray<string>;
};

export const news: ReadonlyArray<NewsItem> = [
  {
    slug: 'swahili-language-model-neurips',
    category: 'Research',
    date: '20 August 2026',
    isoDate: '2026-08-20',
    title: 'Mema AI Lab publishes breakthrough work on Swahili language models',
    excerpt:
      'The natural language processing team has published landmark research on large language models trained on African languages, accepted at NeurIPS 2026.',
    image: '/about-students.jpg',
    body: [
      'The Mema AI & Machine Learning Research Centre has had its work on Swahili language modelling accepted at NeurIPS 2026, one of the most selective venues in machine learning.',
      'The paper introduces an open benchmark for Swahili comprehension alongside a 3-billion-parameter model trained on a curated corpus assembled with partner institutions across Kenya and Tanzania. Both the benchmark and the evaluation harness are released publicly.',
      'The team argues that evaluation, rather than model size, is the binding constraint on progress for low-resource languages: without agreed benchmarks, improvements cannot be measured and therefore cannot be trusted.',
      'The work was led by Dr. Samuel Kiptoo with Prof. Esther Nyambura and doctoral candidate Amara Wanjiku, and was supported by the university research fund.',
    ],
  },
  {
    slug: 'record-applications-september-2026',
    category: 'Admissions',
    date: '15 August 2026',
    isoDate: '2026-08-15',
    title: 'September 2026 intake receives a record 12,000 applications',
    excerpt:
      'The university received its highest ever volume of applications for the coming academic intake, across every faculty.',
    image: '/hero-campus.jpg',
    body: [
      'Mema University has received 12,048 applications for the September 2026 intake, an increase of 31% on the previous year and the highest in the institution’s history.',
      'Computing and data science programmes account for just over half of all applications. The School of Engineering saw the sharpest proportional growth, at 44%.',
      'The Registrar’s office has extended assessment capacity to hold the published fifteen-working-day decision turnaround. Applicants can track each stage of assessment in the applicant portal.',
      'Applications close on 30 August 2026.',
    ],
  },
  {
    slug: 'industry-placement-mou',
    category: 'Industry',
    date: '10 August 2026',
    isoDate: '2026-08-10',
    title: 'Mema signs placement agreements with three leading technology firms',
    excerpt:
      'A partnership agreement will guarantee supervised industry attachment for more than 200 students each semester from 2027.',
    image: '/campus-life.jpg',
    body: [
      'The university has signed memoranda of understanding with three technology employers that together guarantee at least 200 supervised attachment places per semester beginning in the 2027 academic year.',
      'Each placement carries a named industry supervisor, a structured learning agreement and a joint assessment with the faculty — the same model the university has used for its existing partnerships.',
      'The agreements also establish a standing curriculum advisory group, which will review computing and engineering unit content annually against current industry practice.',
    ],
  },
  {
    slug: 'startup-hub-fourteenth-venture',
    category: 'Innovation',
    date: '2 August 2026',
    isoDate: '2026-08-02',
    title: 'Startup Hub incubates its fourteenth student venture',
    excerpt:
      'A final-year team’s agricultural sensor platform becomes the fourteenth company founded at the Mema Startup Hub since 2022.',
    image: '/ai-lab.jpg',
    body: [
      'A team of four final-year students has incorporated the fourteenth venture to come out of the Mema Startup Hub, building low-power soil-moisture sensors for smallholder irrigation.',
      'The company has completed a field trial with 40 farms in Kirinyaga county and is now raising a seed round with support from the hub’s mentor network.',
      'Of the fourteen ventures founded at the hub since 2022, eleven are still trading and together employ 63 people.',
    ],
  },
  {
    slug: 'graduation-2026',
    category: 'Campus',
    date: '25 July 2026',
    isoDate: '2026-07-25',
    title: '1,340 graduands conferred at the eighteenth graduation ceremony',
    excerpt:
      'The university conferred degrees, diplomas and postgraduate awards on 1,340 graduands at the main campus.',
    image: '/graduation.jpg',
    body: [
      'The eighteenth Mema University graduation ceremony conferred awards on 1,340 graduands, including 96 postgraduate degrees.',
      'The Vice-Chancellor’s address focused on the obligation that a publicly recognised qualification carries, and on the university’s responsibility to the students it did not admit as well as those it did.',
      'Graduate employment stands at 96% within six months of conferment, measured by the alumni office through direct survey.',
    ],
  },
];

export type EventItem = {
  day: string;
  month: string;
  isoDate: string;
  title: string;
  time: string;
  location: string;
  category: string;
  body: string;
};

export const events: ReadonlyArray<EventItem> = [
  {
    day: '28', month: 'AUG', isoDate: '2026-08-28',
    title: 'September 2026 intake open day',
    time: '9:00 AM – 4:00 PM',
    location: 'Main Campus, Nairobi',
    category: 'Admissions',
    body: 'Tour the campus, meet faculty from every school, and get help completing your application on the spot.',
  },
  {
    day: '05', month: 'SEP', isoDate: '2026-09-05',
    title: 'Annual hackathon: AI for Africa',
    time: '8:00 AM – 8:00 PM',
    location: 'Technology Innovation Hub',
    category: 'Competition',
    body: 'Twelve hours, open to all students and alumni. This year’s brief is language technology for underserved languages.',
  },
  {
    day: '12', month: 'SEP', isoDate: '2026-09-12',
    title: 'Public lecture: the future of fintech in East Africa',
    time: '2:00 PM – 5:00 PM',
    location: 'Auditorium A, Block C',
    category: 'Lecture',
    body: 'Dr. Miriam Chebet on what a decade of mobile money has and has not changed about financial inclusion.',
  },
  {
    day: '26', month: 'SEP', isoDate: '2026-09-26',
    title: 'Research and innovation showcase',
    time: '10:00 AM – 3:00 PM',
    location: 'Research Quadrangle',
    category: 'Research',
    body: 'Every active research project presents. Open to industry partners, prospective postgraduates and the public.',
  },
];

// ─── Testimonials ─────────────────────────────────────────────────────────────

export const testimonials: ReadonlyArray<{ name: string; programme: string; quote: string; image: string }> = [
  {
    name: 'Amara Wanjiku',
    programme: 'BSc Computer Science, class of 2025',
    quote:
      'Mema changed how I see technology. Access to the AI lab and a real industry attachment gave me experience that landed me a role at a Nairobi fintech before I had even graduated.',
    image: '/student-1.jpg',
  },
  {
    name: 'David Otieno',
    programme: 'MSc Software Engineering, class of 2024',
    quote:
      'The faculty genuinely invest in you. My research on distributed systems was co-published in an IEEE journal, and that happened because of the mentorship I had here.',
    image: '/student-2.jpg',
  },
  {
    name: 'Fatuma Hassan',
    programme: 'BSc Data Science & AI, year 3',
    quote:
      'Coming from Mombasa I was nervous about the city. The inclusive environment, HELB support and the strength of the data science programme have made this the best decision of my life.',
    image: '/student-3.jpg',
  },
];

// ─── Partners ─────────────────────────────────────────────────────────────────

export const partners: ReadonlyArray<{ name: string; abbr: string }> = [
  { name: 'Commission for University Education', abbr: 'CUE' },
  { name: 'Kenya Universities and Colleges Central Placement Service', abbr: 'KUCCPS' },
  { name: 'Higher Education Loans Board', abbr: 'HELB' },
  { name: 'Engineers Board of Kenya', abbr: 'EBK' },
  { name: 'Kenya ICT Authority', abbr: 'ICT Authority' },
  { name: 'IEEE Kenya Section', abbr: 'IEEE' },
  { name: 'ACM Kenya', abbr: 'ACM' },
  { name: 'Kenya Education Network', abbr: 'KENET' },
];

// ─── Gallery ──────────────────────────────────────────────────────────────────

export const gallery: ReadonlyArray<{ src: string; caption: string }> = [
  { src: '/hero-campus.jpg', caption: 'Main campus, Thika Road' },
  { src: '/ai-lab.jpg', caption: 'AI & Machine Learning Research Centre' },
  { src: '/library.jpg', caption: 'University library and study commons' },
  { src: '/campus-life.jpg', caption: 'Student centre and quadrangle' },
  { src: '/about-students.jpg', caption: 'Undergraduate teaching studio' },
  { src: '/graduation.jpg', caption: 'The eighteenth graduation ceremony' },
];

export const campusLife: ReadonlyArray<string> = [
  'Student innovation hub and maker space',
  'Annual technology expo and startup demo day',
  'Inter-university hackathon and competitive programming',
  'Sports, arts and cultural societies',
  'Alumni mentorship network of more than 8,000 professionals',
];
