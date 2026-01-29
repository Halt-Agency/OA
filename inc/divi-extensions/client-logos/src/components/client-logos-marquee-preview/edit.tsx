import React, { ReactElement, useEffect, useState } from 'react';

import { ModuleContainer } from '@divi/module';

import { ClientLogosMarqueeEditProps } from './types';
import { ModuleStyles } from './styles';
import { moduleClassnames } from './module-classnames';
import { ModuleScriptData } from './module-script-data';

type ClientLogoPreviewItem = {
  id: number;
  title: string;
  slug: string;
  url?: string;
  alt?: string;
};

export const ClientLogosMarqueeEdit = (props: ClientLogosMarqueeEditProps): ReactElement => {
  const {
    attrs,
    elements,
    id,
    name,
  } = props;
  const settingsValue =
    attrs?.settings?.innerContent?.desktop?.value ||
    (attrs?.settings as any)?.innerContent?.value ||
    attrs?.settings?.innerContent ||
    {};
  const grayscale = !!settingsValue?.grayscale;
  const mapSelectValue = (value: any, options: string[], fallback: string) => {
    if (typeof value === 'number') {
      return options[value] || fallback;
    }
    if (value === '0' || value === '1') {
      const idx = parseInt(value, 10);
      return options[idx] || fallback;
    }
    return (value ?? fallback).toString();
  };

  const logoVariant = mapSelectValue(settingsValue?.logoVariant, ['white', 'colour'], 'white');
  const filterMode = mapSelectValue(settingsValue?.filterMode, ['all', 'taxonomy'], 'all');
  const taxonomy = mapSelectValue(settingsValue?.taxonomy, ['client_category'], 'client_category');
  const taxonomyTermsRaw = (settingsValue?.taxonomyTerms || '').toString();
  const taxonomyTerms = taxonomyTermsRaw
    .split(',')
    .map((term) => term.trim())
    .filter(Boolean);
  const isTaxonomyFilter = filterMode === 'taxonomy' || taxonomyTerms.length > 0;
  const [items, setItems] = useState<ClientLogoPreviewItem[] | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let isMounted = true;
    const apiRoot = (window as any)?.wpApiSettings?.root || '/wp-json/';
    const taxonomyQuery =
      isTaxonomyFilter && taxonomy && taxonomyTerms.length
        ? `&${encodeURIComponent(taxonomy)}=${encodeURIComponent(taxonomyTerms.join(','))}`
        : '';
    const endpoint = `${apiRoot}wp/v2/clients?per_page=100&_fields=id,slug,title,acf,featured_media${taxonomyQuery}`;

    const fetchMedia = async (mediaId: number): Promise<{ url?: string; alt?: string } | null> => {
      if (!mediaId) {
        return null;
      }
      try {
        const res = await fetch(`${apiRoot}wp/v2/media/${mediaId}?_fields=source_url,alt_text,title`);
        if (!res.ok) {
          return null;
        }
        const data = await res.json();
        return {
          url: data?.source_url,
          alt: data?.alt_text || data?.title?.rendered || '',
        };
      } catch (err) {
        return null;
      }
    };

    const fetchClients = async () => {
      try {
        const res = await fetch(endpoint, { credentials: 'same-origin' });
        if (!res.ok) {
          throw new Error(`HTTP ${res.status}`);
        }
        const data = await res.json();
        const resolved = await Promise.all(
          (Array.isArray(data) ? data : []).map(async (item: any) => {
            const logoField = logoVariant === 'colour' ? 'client_logo_colour' : 'client_logo';
            const acfLogo = item?.acf?.[logoField] || item?.acf?.client_logo;
            if (acfLogo && typeof acfLogo === 'object' && acfLogo.url) {
              return {
                id: item.id,
                title: item?.title?.rendered || item?.slug || 'Client',
                slug: item?.slug || '',
                url: acfLogo.url,
                alt: acfLogo.alt || acfLogo.title || '',
              };
            }
            if (typeof acfLogo === 'number') {
              const media = await fetchMedia(acfLogo);
              return {
                id: item.id,
                title: item?.title?.rendered || item?.slug || 'Client',
                slug: item?.slug || '',
                url: media?.url,
                alt: media?.alt || '',
              };
            }
            if (item?.featured_media) {
              const media = await fetchMedia(item.featured_media);
              return {
                id: item.id,
                title: item?.title?.rendered || item?.slug || 'Client',
                slug: item?.slug || '',
                url: media?.url,
                alt: media?.alt || '',
              };
            }
            return {
              id: item.id,
              title: item?.title?.rendered || item?.slug || 'Client',
              slug: item?.slug || '',
            };
          })
        );

        const filtered = resolved.filter(Boolean) as ClientLogoPreviewItem[];
        if (isMounted) {
          setItems(filtered);
        }
      } catch (err: any) {
        if (isMounted) {
          setError(err?.message || 'Unable to load clients');
          setItems([]);
        }
      }
    };

    fetchClients();

    return () => {
      isMounted = false;
    };
  }, [filterMode, taxonomy, taxonomyTermsRaw, logoVariant]);

  return (
    <ModuleContainer
      attrs={attrs}
      elements={elements}
      id={id}
      name={name}
      stylesComponent={ModuleStyles}
      classnamesFunction={moduleClassnames}
      scriptDataComponent={ModuleScriptData}
    >
      {elements.styleComponents({
        attrName: 'module',
      })}
      <div className="oa_client_logos_marquee_preview__placeholder">
        <div className="oa_client_logos_marquee_preview__heading">Client Logos Marquee (Live Preview)</div>
        {error && <div className="oa_client_logos_marquee_preview__error">Preview error: {error}</div>}
        {!error && items === null && (
          <div className="oa_client_logos_marquee_preview__loading">Loading preview...</div>
        )}
        {!error && items !== null && items.length === 0 && (
          <div className="oa_client_logos_marquee_preview__empty">No client logos found.</div>
        )}
        {!error && items && items.length > 0 && (
          <div className="oa_client_logos_marquee_preview__grid">
            {items.map((item) => (
              <div key={item.id} className="oa_client_logos_marquee_preview__item">
                {item.url ? (
                  <img
                    src={item.url}
                    alt={item.alt || item.title}
                    className={grayscale ? 'oa_client_logos_marquee_preview__img--grayscale' : undefined}
                  />
                ) : (
                  <div className="oa_client_logos_marquee_preview__fallback">{item.title}</div>
                )}
              </div>
            ))}
          </div>
        )}
      </div>
    </ModuleContainer>
  );
};
