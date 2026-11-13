import { asset } from './env';

/**
 * Studio artwork used across the public site. Every file lives in
 * `/assets/img` and is referenced through the PHP-injected asset base,
 * so the same build works at the domain root or in a sub-folder.
 *
 * Photography is only used where it genuinely represents the section:
 * product/marketing art for services, real team and leadership photos
 * for the company pages, and hands-on studio imagery for the work pages.
 */
export const MEDIA = {
  homepageHero: 'Homepage-hero.jpg',
  portfolioShowcase: 'Portfolio-showcase.jpg',
  administration: 'Administration-system.jpg',
  technology: 'Technology-graphics.jpg',
  team: 'reagansoft-team.jpeg',
  bike: 'company-bike.jpeg',
  ceoIsrael: 'ceo-in-israel-with-his-friend.jpeg',
  funders: 'our funders-with-ceo.jpeg',
  engineering:
    'programming-software-interface-device-engineers-application-company-project-space-professional-solutions-systems-117127167.webp',
  design:
    'ui-ux-designers-isometric-composition-with-small-people-creating-custom-design-web-site-3d-vector-illustration_1284-68939.webp',
  code: 'code.jpg',
  languages: 'the-language-we-use.jpg',
  mobileCore: 'software-core-mobile-1024x1024.png',
} as const;

export type MediaKey = keyof typeof MEDIA;

export const MEDIA_ALT: Record<MediaKey, string> = {
  homepageHero: 'Reagansoft Innovation software engineers working in a modern technology studio',
  portfolioShowcase: 'A curated showcase of digital products delivered by Reagansoft Innovation',
  administration: 'Reagansoft administration system dashboard with live operational records',
  technology: 'Technology graphics representing the Reagansoft engineering stack and infrastructure',
  team: 'The Reagansoft Innovation team at the Jinja studio',
  bike: 'The Reagansoft company Yamaha motorcycle finished in the company brand colours',
  ceoIsrael: 'Reagansoft founder and CEO meeting a friend during a technology visit to Israel',
  funders: 'Reagansoft funders and partners photographed together with the CEO',
  engineering: 'Reagansoft software engineers working at modern development workstations',
  design: 'UI and UX designers composing a custom website design',
  code: 'Clean, well-structured application source code on screen',
  languages: 'The programming languages and frameworks used by the Reagansoft team',
  mobileCore: 'Reagansoft mobile application core running on a smartphone',
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

export const STUDIO_REEL: StudioSlide[] = [
  {
    image: MEDIA.homepageHero,
    alt: MEDIA_ALT.homepageHero,
    label: 'Engineering studio',
    caption: 'Design, engineering and deployment under one accountable team in Jinja.',
  },
  {
    image: MEDIA.administration,
    alt: MEDIA_ALT.administration,
    label: 'Management systems',
    caption: 'Live dashboards, roles and workflows built around real operations.',
  },
  {
    image: MEDIA.portfolioShowcase,
    alt: MEDIA_ALT.portfolioShowcase,
    label: 'Delivered work',
    caption: 'Websites, commerce and internal platforms serving organisations across Uganda.',
  },
  {
    image: MEDIA.technology,
    alt: MEDIA_ALT.technology,
    label: 'Systems & integrations',
    caption: 'Payments, messaging, APIs and data flows connected securely.',
  },
  {
    image: MEDIA.team,
    alt: MEDIA_ALT.team,
    label: 'The team',
    caption: 'A local team you can meet, call and build with over the long term.',
  },
  {
    image: MEDIA.bike,
    alt: MEDIA_ALT.bike,
    label: 'On the ground',
    caption: 'Company transport in our colours, ready for client visits and site work.',
  },
  {
    image: MEDIA.ceoIsrael,
    alt: MEDIA_ALT.ceoIsrael,
    label: 'Partnerships',
    caption: 'Learning from technology partners beyond Uganda and bringing it home.',
  },
  {
    image: MEDIA.funders,
    alt: MEDIA_ALT.funders,
    label: 'Backing the vision',
    caption: 'Funders and partners photographed with the founder at the studio.',
  },
];


/** Picks the most relevant studio artwork for a service or sector. */
export function serviceMedia(slug = '', icon = ''): string {
  const value = `${slug} ${icon}`.toLowerCase();
  if (/admin|dashboard|portal|management|record|system|erp|school|clinic|billing|invoice|receipt|finance|account/.test(value)) {
    return media('administration');
  }
  if (/brand|design|palette|ui|ux|graphic/.test(value)) return media('design');
  if (/mobile|app|flutter|smartphone/.test(value)) return media('mobileCore');
  if (/ecommerce|commerce|store|shop|retail/.test(value)) return media('portfolioShowcase');
  if (/website|web-design|business-website/.test(value)) return media('homepageHero');
  if (/integration|api|language|stack|data|cloud|hosting|devops|automation|chat/.test(value)) return media('technology');
  if (/software|code|security|audit|maintenance/.test(value)) return media('code');
  return media('engineering');
}
