(function () {
  function populateTrack(track, items, repeatCount) {
    track.innerHTML = '';
    for (let i = 0; i < repeatCount; i += 1) {
      items.forEach(function (item) {
        const itemEl = document.createElement('div');
        itemEl.className = 'oa-marquee-item';

        const img = document.createElement('img');
        img.src = item.url;
        img.alt = item.alt || '';
        if (item.title) {
          img.title = item.title;
        }

        itemEl.appendChild(img);
        track.appendChild(itemEl);
      });
    }
  }

  function buildCarousel(container, items, variant) {
    const wrapper = document.createElement('div');
    wrapper.className = 'oa-marquee-wrapper';

    const track = document.createElement('div');
    track.className = 'oa-marquee-track';

    const duplicate = document.createElement('div');
    duplicate.className = 'oa-marquee-track oa-marquee-track-duplicate';

    wrapper.appendChild(track);
    wrapper.appendChild(duplicate);

    container.innerHTML = '';
    container.appendChild(wrapper);
    container.classList.add('oa-client-carousel');
    if (variant === 'colour') {
      container.classList.add('is-colour');
    } else {
      container.classList.remove('is-colour');
    }

    // Ensure each half-track is wide enough to avoid visible gaps.
    populateTrack(track, items, 1);
    const baseWidth = track.scrollWidth || 0;
    const containerWidth = container.clientWidth || 0;
    const minTargetWidth = containerWidth * 1.2;
    const repeatCount =
      baseWidth > 0 ? Math.max(1, Math.ceil(minTargetWidth / baseWidth)) : 1;

    populateTrack(track, items, repeatCount);
    populateTrack(duplicate, items, repeatCount);
  }

  function initContainer(container) {
    const source = (container.dataset.source || 'global').toLowerCase();
    const data =
      source === 'trusted-by'
        ? (window.oaTrustedByLogos || [])
        : (window.oaClientLogos || []);
    if (!Array.isArray(data) || data.length === 0) {
      return;
    }

    const requestedVariant = (container.dataset.variant || '').toLowerCase();
    const trustedByVariant = (window.oaTrustedByLogoVariant || '').toLowerCase();
    const variant =
      requestedVariant === 'white' || requestedVariant === 'colour'
        ? requestedVariant
        : (source === 'trusted-by' && (trustedByVariant === 'white' || trustedByVariant === 'colour'))
          ? trustedByVariant
          : (container.classList.contains('client-carousel-colour') ? 'colour' : 'white');
    const speed = parseFloat(container.dataset.speed) || 30;
    const direction = container.dataset.direction === 'right' ? 'reverse' : 'normal';
    const grayscale = container.dataset.grayscale === 'false' ? false : true;
    const filterMode = (container.dataset.filterMode || 'all').toLowerCase();
    const terms = (container.dataset.terms || '')
      .split(',')
      .map(function (term) {
        return term.trim();
      })
      .filter(Boolean);

    const items = data
      .filter(function (item) {
        if (filterMode !== 'taxonomy' || terms.length === 0) {
          return true;
        }
        if (!Array.isArray(item.terms)) {
          return false;
        }
        return item.terms.some(function (term) {
          return terms.indexOf(term) !== -1;
        });
      })
      .map(function (item) {
        const url =
          variant === 'colour'
            ? item.colour_url || item.white_url
            : item.white_url || item.colour_url;
        if (!url) {
          return null;
        }
        return {
          url: url,
          alt: item.alt || '',
          title: item.title || '',
        };
      })
      .filter(Boolean);

    if (items.length === 0) {
      return;
    }

    if (!grayscale) {
      container.classList.add('is-colour');
    }

    container.style.setProperty('--oa-marquee-duration', speed + 's');
    container.style.setProperty('--oa-marquee-direction', direction);

    buildCarousel(container, items, variant);
  }

  document.addEventListener('DOMContentLoaded', function () {
    const containers = document.querySelectorAll(
      '.client-carousel-white, .client-carousel-colour, .client-carousel-trusted-by'
    );
    containers.forEach(initContainer);

    let resizeTimeout = null;
    window.addEventListener('resize', function () {
      if (resizeTimeout) {
        window.clearTimeout(resizeTimeout);
      }
      resizeTimeout = window.setTimeout(function () {
        containers.forEach(initContainer);
      }, 150);
    });
  });
})();
