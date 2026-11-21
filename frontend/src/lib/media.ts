import { asset } from './env';

/**
 * Every photograph and illustration used by the public site is registered
 * here. Each file lives in `/assets/img` and is resolved through the
 * PHP-injected asset base, so the same build works at a domain root or in a
 * sub-folder.
 *
 * The registry is deliberately curated: each page gets its own reel, every
 * service gets its own cover and the proof band rotates its photography by
 * route. Nothing is reused blindly just because it is available.
 */
export const MEDIA = {
  homepageHero: 'Homepage-hero.jpg',
  portfolioShowcase: 'Portfolio-showcase.jpg',
  administration: 'Administration-system.jpg',
  technology: 'Technology-graphics.jpg',
  engineering:
    'programming-software-interface-device-engineers-application-company-project-space-professional-solutions-systems-117127167.webp',
  design: 'ui-ux-designers-isometric-composition-with-small-people-creating-custom-design-web-site-3d-vector-illustration_1284-68939.webp',
  designAvif:
    'ui-ux-designers-isometric-composition-with-small-people-creating-custom-design-web-site-3d-vector-illustration_1284-68939.avif',
  code: 'code.jpg',
  languages: 'the-language-we-use.jpg',
  mobileCore: 'software-core-mobile-1024x1024.png',
  heroWeb: 'hero1.webp',
  heroSystems: 'hero2.webp',
  heroApps: 'hero3.webp',
  heroBilling: 'hero4.webp',
  heroCommerce: 'hero5.webp',
  heroProcess: 'hero6.webp',
  cover: 'cover.webp',
  codesBanner: 'codes-banner.webp',
  computerPortrait: 'computer-portrait.webp',
  founder: 'founder.webp',
  team: 'reagansoft-team.jpeg',
  bike: 'company-bike.jpeg',
  ceoIsrael: 'ceo-in-israel-with-his-friend.jpeg',
  funders: 'our funders-with-ceo.jpeg',
  officeVisitors: 'director-and-aliyah-and-joy-from-america-visiting-at-main-office-in-jinja.jpeg',
  founderGraduation:
    'director-in-gown-gradustion-in-idaho-usa-at-byu-university-image-with-bachelor-of-science-in-software-development.jpeg',
  remoteWork: 'LAMP-AND-LAPTOP-FOR-REMOTE-WORKERS.webp',
  hireDevelopers: 'hire-web-developers.webp',
  workspace: 'works-space.jpg',
  codingHome: 'coding-at-home-stockcake.jpg',
  everything: 'Software-Development-everything-you-need.png',
  costEstimation: 'Software-Development-Cost-Estimation-by-types-internationally.webp',
  developerClassroom: 'software-developer-classroom.jpg',
  developerServices: 'software-developer-services.jpg',
  sectors: 'software-developmentsectors.png',
  finance: 'the-role-of-it-and-software-development-for-the-finance-sector.png',
  keyRoles: 'key-roles-in-software-developements.jpg',
  topDevelopers: 'top-software-developers-graphics.jpeg',
  crossPlatform: 'perfect-developement-in-pc-mobile-views-engineering.png',
} as const;

export type MediaKey = keyof typeof MEDIA;

export const MEDIA_ALT: Record<MediaKey, string> = {
  homepageHero: 'Reagansoft Innovation engineers working in a modern technology studio',
  portfolioShowcase: 'A curated showcase of digital products delivered by Reagansoft Innovation',
  administration: 'Reagansoft administration system dashboard with live operational records',
  technology: 'Technology graphics representing the Reagansoft engineering stack and infrastructure',
  engineering: 'Software engineers working together at modern development workstations',
  design: 'UI and UX designers composing a custom website design',
  designAvif: 'UI and UX designers composing a custom website design',
  code: 'Clean, well-structured application source code on screen',
  languages: 'The programming languages and frameworks used by the Reagansoft team',
  mobileCore: 'Reagansoft mobile application core running on a smartphone',
  heroWeb: 'A responsive business website designed and developed by Reagansoft Innovation',
  heroSystems: 'Management dashboards and workflows built around real operations',
  heroApps: 'Web and mobile application engineering for Android, iOS and the browser',
  heroBilling: 'Receipt and billing automation software for shops, schools and clinics',
  heroCommerce: 'Ecommerce storefront development with catalogue and checkout',
  heroProcess: 'The Reagansoft delivery process, from discovery through launch',
  cover: 'Reagansoft Innovation studio cover artwork',
  codesBanner: 'Clean application source code reviewed by the Reagansoft engineering team',
  computerPortrait: 'A developer at a workstation in the Reagansoft studio',
  founder: 'Reagansoft founder, CEO and lead developer Reagan Otema',
  team: 'The Reagansoft Innovation team at the Jinja studio',
  bike: 'The Reagansoft company motorcycle finished in the company brand colours',
  ceoIsrael: 'Reagansoft founder and CEO with a technology friend during a visit to Israel',
  funders: 'Reagansoft funders and partners photographed together with the CEO',
  officeVisitors: 'The Reagansoft director with visiting friends from America at the main office in Jinja',
  founderGraduation:
    'Reagansoft CEO Reagan Otema in graduation gown after earning a Bachelor of Science in Software Development at Brigham Young University in Idaho, USA',
  remoteWork: 'A lamp and laptop arranged for focused remote software development work',
  hireDevelopers: 'A professional team hiring and working with web developers',
  workspace: 'A bright, modern workspace where the engineering team plans and builds software',
  codingHome: 'A developer coding from home on a remote software project',
  everything: 'Everything needed to plan, design and deliver a modern software product',
  costEstimation: 'A guide to software development cost estimation by project type and country',
  developerClassroom: 'Software developers learning and collaborating in a classroom session',
  developerServices: 'A software developer providing professional development services',
  sectors: 'The sectors served by modern software development teams',
  finance: 'The role of IT and software development in the finance sector',
  keyRoles: 'The key roles inside a professional software development team',
  topDevelopers: 'Graphic representing the top developers behind quality software products',
  crossPlatform: 'Software engineered to work perfectly across desktop, tablet and mobile views',
};

export function media(key: MediaKey): string {
  return asset(MEDIA[key]);
}

export function mediaAlt(key: MediaKey): string {
  return MEDIA_ALT[key];
}

export interface StudioSlide {
  image: string;
  alt: string;
  label: string;
  caption: string;
}

function slide(key: MediaKey, label: string, caption: string): StudioSlide {
  return { image: MEDIA[key], alt: MEDIA_ALT[key], label, caption };
}

/**
 * A different reel per page. The old build showed one identical eight-image
 * reel on every route, which made the site feel repetitive; these sets are
 * curated to the subject of each page and never repeat an image within a set.
 */
export const REELS: Record<
  'about' | 'services' | 'service' | 'portfolio' | 'pricing' | 'process' | 'contact' | 'faq',
  StudioSlide[]
> = {
  about: [
    slide('heroWeb', 'The craft', 'Interfaces and code built by the same senior team.'),
    slide('heroCommerce', 'The range', 'From storefronts to operational platforms.'),
    slide('cover', 'The studio', 'A Jinja team working to international standards.'),
    slide('codesBanner', 'The discipline', 'Readable, tested and documented code on every release.'),
    slide('founderGraduation', 'The foundation', 'Software engineering trained to international standards.'),
    slide('officeVisitors', 'Open doors', 'Partners and clients are welcome at the Jinja studio.'),
  ],
  services: [
    slide('hireDevelopers', 'Your team, extended', 'Senior developers who join your project, not a rotating helpdesk.'),
    slide('remoteWork', 'Focused engineering', 'Deep work, clear milestones and weekly demos you can see.'),
    slide('workspace', 'Studio & remote', 'A hybrid team that stays close to your operation.'),
    slide('codingHome', 'Always moving', 'Progress continues between meetings, with visible updates.'),
  ],
  service: [
    slide('engineering', 'In the build', 'Design, architecture and engineering handled end to end.'),
    slide('sectors', 'Across sectors', 'The same discipline applied to schools, shops, clinics and offices.'),
    slide('developerServices', 'Professional service', 'A named lead and a delivery team you can talk to directly.'),
    slide('heroApps', 'Every screen', 'One product prepared for the browser, Android and iOS.'),
  ],
  portfolio: [
    slide('topDevelopers', 'The people', 'The developers behind the case studies you are reading.'),
    slide('codesBanner', 'The build', 'Clean, maintainable work that teams can take ownership of.'),
    slide('heroCommerce', 'The outcome', 'Commerce systems designed around real customers and payments.'),
    slide('crossPlatform', 'The reach', 'Products that look right and work well on every screen.'),
  ],
  pricing: [
    slide('costEstimation', 'Clear estimates', 'Costs explained by project type, scope and market.'),
    slide('everything', 'What is included', 'Discovery, design, engineering, launch support and handover.'),
    slide('heroBilling', 'Honest billing', 'A written quote and payments tied to accepted milestones.'),
    slide('administration', 'Long-term value', 'Systems that keep saving time long after the first release.'),
  ],
  process: [
    slide('keyRoles', 'The right people', 'A named lead, designers and engineers across the whole build.'),
    slide('languages', 'Proven tools', 'A practical stack chosen for reliability and handover.'),
    slide('heroProcess', 'Visible delivery', 'Weekly increments, demos and a calm, supervised launch.'),
    slide('codesBanner', 'Quality gates', 'Peer review and responsive checks before anything ships.'),
  ],
  contact: [
    slide('cover', 'Start here', 'A conversation about your operation, not a sales script.'),
    slide('team', 'Meet the team', 'The people who scope, build and support your project.'),
    slide('officeVisitors', 'Visit the studio', 'Come and see the team and workspace before you commit.'),
    slide('bike', 'We come to you', 'Client visits and site work across Jinja and beyond.'),
    slide('funders', 'Backed well', 'Partners who invest in long-term local engineering.'),
  ],
  faq: [
    slide('heroSystems', 'Straight answers', 'Clear scope, clear pricing and a named owner.'),
    slide('finance', 'Finance & payments', 'Billing, records and payment flows built into the system.'),
    slide('mobileCore', 'Any device', 'One product for the browser, Android and iOS.'),
    slide('design', 'Designed first', 'Interfaces mapped to real workflows before code begins.'),
  ],
};

/** Kept as a convenient default for callers that do not need a curated set. */
export const STUDIO_REEL: StudioSlide[] = REELS.about;

/**
 * Unique cover art for each of the fourteen service lines. The previous
 * keyword matcher collapsed most services onto the same three images.
 */
const SERVICE_ART: Record<string, MediaKey> = {
  'business-website': 'homepageHero',
  ecommerce: 'portfolioShowcase',
  'business-systems': 'administration',
  'software-dev': 'engineering',
  'web-mobile-apps': 'crossPlatform',
  'receipt-billing': 'finance',
  'custom-solutions': 'everything',
  branding: 'design',
  maintenance: 'code',
  'video-games': 'heroApps',
  'digital-books': 'computerPortrait',
  'chat-automation': 'technology',
  'system-integration': 'keyRoles',
  cybersecurity: 'codesBanner',
};

const SERVICE_ART_FALLBACK: MediaKey[] = [
  'topDevelopers',
  'developerServices',
  'workspace',
  'remoteWork',
  'hireDevelopers',
  'languages',
  'mobileCore',
  'heroWeb',
  'heroCommerce',
  'heroBilling',
  'heroProcess',
  'heroSystems',
  'sectors',
  'costEstimation',
];

export function serviceMedia(slug = '', icon = ''): string {
  const key = SERVICE_ART[slug] ?? SERVICE_ART_FALLBACK[Math.abs(hash(`${slug}${icon}`)) % SERVICE_ART_FALLBACK.length];
  return media(key);
}

function hash(value: string): number {
  let result = 0;
  for (let index = 0; index < value.length; index += 1) {
    result = (result << 5) - result + value.charCodeAt(index);
    result |= 0;
  }
  return result;
}

/** The hero opens with the newest cinematic artwork, then rotates by subject. */
export const HERO_SLIDES: MediaKey[] = [
  'remoteWork',
  'hireDevelopers',
  'workspace',
  'codingHome',
  'everything',
  'homepageHero',
];

export interface ProofCard {
  image: MediaKey;
  portrait?: boolean;
  overline: string;
  caption: string;
  title: string;
  body: string;
  proof: string;
  to: string;
  link: string;
}

export const PROOF_CARDS: Record<string, ProofCard> = {
  founderPortrait: {
    image: 'founder',
    portrait: true,
    overline: 'Founder · Reagansoft Innovation',
    caption: 'Reagan Otema, founder',
    title: 'A founder who still writes the code.',
    body: 'The person who scopes your project is the person who answers when you call. No account layer, no translated requirements, no mystery about who is responsible.',
    proof: 'Direct access · Real ownership',
    to: '/about',
    link: 'Our story',
  },
  graduation: {
    image: 'founderGraduation',
    overline: 'Founder & engineer',
    caption: 'CEO Reagan Otema at his Brigham Young University graduation',
    title: 'The technical foundation is real.',
    body: 'Reagan studied software development at Brigham Young University in Idaho, USA, and still works close to the code. That training shapes how we design systems, review decisions and explain the work in plain language.',
    proof: 'BSc Software Development / BYU in Idaho, USA',
    to: '/about',
    link: 'Our story',
  },
  visitors: {
    image: 'officeVisitors',
    overline: 'Visiting our Jinja studio',
    caption: 'The director with Aliyah and Joy',
    title: 'Visitors meet the people doing the work.',
    body: 'Guests sit with the designers, developers and project leads who build the system. Questions are answered in the room, decisions are made together, and the team you meet is the team that stays with the project.',
    proof: 'Open studio / Direct conversations',
    to: '/contact',
    link: 'Plan a visit',
  },
  director: {
    image: 'ceoIsrael',
    overline: 'Learning beyond borders',
    caption: 'The director with a technology partner',
    title: 'Global thinking, local delivery.',
    body: 'We keep learning from technology partners around the world and bring that thinking back to the studios and organisations we serve in Uganda.',
    proof: 'World-class practice · Local context',
    to: '/about',
    link: 'How we work',
  },
  team: {
    image: 'team',
    overline: 'One senior studio team',
    caption: 'The Reagansoft team in Jinja',
    title: 'One team from first call to final handover.',
    body: 'Design, engineering, deployment and support stay with the same people, so context is never lost between stages and no one has to start from scratch.',
    proof: 'Stable team · Long-term support',
    to: '/about',
    link: 'Meet the studio',
  },
  funders: {
    image: 'funders',
    overline: 'Backed for the long run',
    caption: 'Funders with the CEO',
    title: 'Built to be here after launch.',
    body: 'Our partners invest in durable local engineering, not one-off projects. That backing lets us keep standards high and relationships long term.',
    proof: 'Long-term backing · Local investment',
    to: '/about',
    link: 'Our partners',
  },
  workshop: {
    image: 'developerClassroom',
    overline: 'Training included',
    caption: 'A hands-on developer workshop',
    title: 'Your team is trained, not just handed a login.',
    body: 'Every delivery includes practical sessions, written guides and a walkthrough your team can revisit. The system should make sense to the people who use it.',
    proof: 'Training · Documentation · Confidence',
    to: '/process',
    link: 'See the process',
  },
  studio: {
    image: 'workspace',
    overline: 'Inside the studio',
    caption: 'Where the work gets done',
    title: 'A real workspace behind every release.',
    body: 'Our Jinja studio is where designs are reviewed, code is paired on and launches are checked. You are welcome to see it before you commit.',
    proof: 'Visible process · Open studio',
    to: '/contact',
    link: 'Visit the studio',
  },
  onSite: {
    image: 'bike',
    overline: 'On the road with the CEO',
    caption: 'Reagan and the company Yamaha',
    title: 'We come to you, not just to meetings.',
    body: 'Some things only make sense when you see them in person: the queue at the counter, the stockroom, the way a team actually works. Reagan takes the company Yamaha out for site visits, training and support, so the system gets built around the real place it has to serve.',
    proof: 'Site visits · Training · Local support',
    to: '/contact',
    link: 'Talk to us',
  },
  portrait: {
    image: 'computerPortrait',
    overline: 'At the workstation',
    caption: 'Engineering in the studio',
    title: 'Senior people doing the actual work.',
    body: 'You meet the developers who design, build and maintain your system. That continuity is the difference between a project and a partner.',
    proof: 'Hands-on · Accountable',
    to: '/services',
    link: 'Explore services',
  },
};

const PROOF_BY_ROUTE: Record<string, [keyof typeof PROOF_CARDS, keyof typeof PROOF_CARDS]> = {
  '/': ['team', 'founderPortrait'],
  '/about': ['founderPortrait', 'director'],
  '/services': ['funders', 'workshop'],
  '/portfolio': ['onSite', 'founderPortrait'],
  '/pricing': ['portrait', 'funders'],
  '/process': ['workshop', 'team'],
  '/contact': ['studio', 'onSite'],
  '/faq': ['portrait', 'studio'],
};

export function proofForPath(pathname: string): ProofCard[] {
  if (pathname.startsWith('/services/')) return ['graduation', 'visitors'].map((key) => PROOF_CARDS[key]);
  const exact = PROOF_BY_ROUTE[pathname.replace(/\/$/, '') || '/'];
  const keys = exact ?? PROOF_BY_ROUTE['/'];
  return keys.map((key) => PROOF_CARDS[key]);
}
