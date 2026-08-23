/**
 * Single source of truth for everything about the site that changes between environments
 * or between marketing revisions.
 *
 * Portal URLs come from the environment because they must. Hard-coded `http://localhost:3001`
 * links in JSX look harmless in development and then ship to production, where every "Apply
 * Now" button on the university's public homepage points at a machine that does not exist.
 * NEXT_PUBLIC_ is required: these are read during render in the browser.
 */

const portal = (envValue: string | undefined, devPort: number): string =>
  envValue ?? `http://localhost:${devPort}`;

export const portals = {
  applicant: portal(process.env.NEXT_PUBLIC_APPLICANT_URL, 3001),
  student: portal(process.env.NEXT_PUBLIC_STUDENT_URL, 3002),
  lecturer: portal(process.env.NEXT_PUBLIC_LECTURER_URL, 3003),
  staff: portal(process.env.NEXT_PUBLIC_STAFF_URL, 3005),
} as const;

export const site = {
  name: 'Mema University',
  tagline: 'Excellence in Research & Technology',
  established: 2008,
  description:
    'Mema University is a CUE-accredited technology university in Nairobi, Kenya, offering ' +
    'undergraduate and postgraduate programmes in computing, engineering, business and data science.',
  url: process.env.NEXT_PUBLIC_SITE_URL ?? 'http://localhost:3000',
  contact: {
    address: 'Main Campus, Thika Road, Nairobi, Kenya',
    postal: 'P.O. Box 47201–00100, Nairobi',
    phone: '+254 20 892 000',
    phoneHref: 'tel:+254208920000',
    admissions: '+254 700 892 000',
    admissionsHref: 'tel:+254700892000',
    email: 'info@mema.ac.ke',
    admissionsEmail: 'admissions@mema.ac.ke',
    hours: 'Monday – Friday, 8:00 AM – 5:00 PM',
  },
} as const;

export type NavItem = {
  label: string;
  href: string;
  children?: ReadonlyArray<{ label: string; href: string; description?: string }>;
};

/**
 * The primary navigation. Two levels only — a third would need a different interaction model
 * on touch devices, and nothing here justifies one.
 */
export const primaryNav: ReadonlyArray<NavItem> = [
  { label: 'Home', href: '/' },
  {
    label: 'About',
    href: '/about',
    children: [
      { label: 'Our Story', href: '/about', description: 'Sixteen years of academic growth' },
      { label: 'Mission & Vision', href: '/about#mission', description: 'What we exist to do' },
      { label: 'Leadership', href: '/about#leadership', description: 'Council and management' },
      { label: 'Campus Gallery', href: '/about#gallery', description: 'A look around Mema' },
    ],
  },
  {
    label: 'Programmes',
    href: '/programmes',
    children: [
      { label: 'All Programmes', href: '/programmes', description: 'The full accredited catalogue' },
      { label: 'Undergraduate', href: '/programmes?level=undergraduate', description: 'Bachelor degrees' },
      { label: 'Postgraduate', href: '/programmes?level=postgraduate', description: 'Masters and doctoral' },
      { label: 'Diploma & Certificate', href: '/programmes?level=diploma', description: 'Short qualifications' },
    ],
  },
  {
    label: 'Research',
    href: '/research',
    children: [
      { label: 'Research Areas', href: '/research', description: 'Where our work is focused' },
      { label: 'Centres & Labs', href: '/research#centres', description: 'Facilities and equipment' },
      { label: 'Publications', href: '/research#publications', description: 'Recent peer-reviewed output' },
    ],
  },
  {
    label: 'Admissions',
    href: '/admissions',
    children: [
      { label: 'How to Apply', href: '/admissions', description: 'Step by step' },
      { label: 'Entry Requirements', href: '/admissions#requirements', description: 'KCSE and equivalents' },
      { label: 'Fees & Funding', href: '/admissions#fees', description: 'Costs, HELB and bursaries' },
      { label: 'Admissions FAQ', href: '/admissions#faq', description: 'Common questions answered' },
    ],
  },
  { label: 'News & Events', href: '/news' },
  { label: 'Contact', href: '/contact' },
];

export const footerNav = {
  academics: [
    { label: 'Faculty of Computing & IT', href: '/programmes?faculty=computing' },
    { label: 'School of Business & Economics', href: '/programmes?faculty=business' },
    { label: 'School of Engineering', href: '/programmes?faculty=engineering' },
    { label: 'Postgraduate Studies', href: '/programmes?level=postgraduate' },
  ],
  discover: [
    { label: 'About Mema', href: '/about' },
    { label: 'Research & Innovation', href: '/research' },
    { label: 'News & Events', href: '/news' },
    { label: 'Contact Us', href: '/contact' },
  ],
  portals: [
    { label: 'Applicant Portal', href: portals.applicant },
    { label: 'Student Portal', href: portals.student },
    { label: 'Lecturer Portal', href: portals.lecturer },
    { label: 'Staff & ERP Portal', href: portals.staff },
  ],
} as const;
