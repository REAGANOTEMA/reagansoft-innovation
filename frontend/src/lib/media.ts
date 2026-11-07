import { asset } from './env';

/**
 * Studio artwork used across the public site. Every file lives in
 * `/assets/img` and is referenced through the PHP-injected asset base,
 * so the same build works at the domain root or in a sub-folder.
 */
export const MEDIA = {
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
  engineering: 'Reagansoft software engineers working at modern development workstations',
  design: 'UI and UX designers composing a custom website design',
  code: 'Clean, well-structured application source code on screen',
  languages: 'The programming languages and frameworks used by the Reagansoft team',
  mobileCore: 'Reagansoft mobile application core running on a smartphone',
};

export function media(key: MediaKey): string {
  return asset(MEDIA[key]);
}

/** Picks the most relevant studio artwork for a service or sector. */
export function serviceMedia(slug = '', icon = ''): string {
  const value = `${slug} ${icon}`.toLowerCase();
  if (/brand|design|palette|ui|ux/.test(value)) return media('design');
  if (/mobile|app|flutter|smartphone/.test(value)) return media('mobileCore');
  if (/integration|api|language|stack|data|cloud|system/.test(value)) return media('languages');
  if (/software|code|security|audit|maintenance|system/.test(value)) return media('code');
  return media('engineering');
}
