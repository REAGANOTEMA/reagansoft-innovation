import { useEffect } from 'react';

export function usePageMeta(title: string, description?: string) {
  useEffect(() => {
    const previousTitle = document.title;
    document.title = title;
    let tag: HTMLMetaElement | null = null;
    let previousDescription: string | null = null;
    if (description) {
      tag = document.querySelector('meta[name="description"]');
      if (!tag) {
        tag = document.createElement('meta');
        tag.setAttribute('name', 'description');
        document.head.appendChild(tag);
      }
      previousDescription = tag.getAttribute('content');
      tag.setAttribute('content', description);
    }
    return () => {
      document.title = previousTitle;
      if (tag && previousDescription !== null) tag.setAttribute('content', previousDescription);
    };
  }, [title, description]);
}
