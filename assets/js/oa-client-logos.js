(function () {
  function buildCarousel(container, items, variant) {
    const wrapper = document.createElement('div');
    wrapper.className = 'oa-marquee-wrapper';

    const track = document.createElement('div');
    track.className = 'oa-marquee-track';

    const duplicate = document.createElement('div');
    duplicate.className = 'oa-marquee-track oa-marquee-track-duplicate';

    const createItem = function (item) {
      const itemEl = document.createElement('div');
      itemEl.className = 'oa-marquee-item';

      const img = document.createElement('img');
      img.src = item.url;
      img.alt = item.alt || '';
      if (item.title) {
        img.title = item.title;
      }

      itemEl.appendChild(img);
      return itemEl;
    };

    items.forEach(function (item) {
      track.appendChild(createItem(item));
      duplicate.appendChild(createItem(item));
    });

    wrapper.appendChild(track);
    wrapper.appendChild(duplicate);

    container.innerHTML = '';
    container.appendChild(wrapper);
    container.classList.add('oa-client-carousel');
    if (variant === 'colour') {
      container.classList.add('is-colour');
    }
  }

  function initContainer(container) {
    const data = window.oaClientLogos || [];
    if (!Array.isArray(data) || data.length === 0) {
      return;
    }

    const variant = container.classList.contains('client-carousel-colour') ? 'colour' : 'white';
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
      '.client-carousel-white, .client-carousel-colour'
    );
    containers.forEach(initContainer);
  });
})();
