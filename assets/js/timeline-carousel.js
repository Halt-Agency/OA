(function() {
    'use strict';

    function buildTimelineCarousel() {
        var root = document.getElementById('timeline-carousel');
        if (!root) {
            return;
        }

        var data = window.dtACFData && window.dtACFData.page_content;
        if (!data || !Array.isArray(data.timeline_items)) {
            return;
        }

        var items = data.timeline_items;
        if (!items.length) {
            return;
        }

        root.innerHTML = '';

        var track = document.createElement('div');
        track.className = 'timeline-carousel__track';

        items.forEach(function(item, index) {
            var card = document.createElement('article');
            card.className = 'timeline-card';
            card.setAttribute('data-index', String(index));

            var media = document.createElement('div');
            media.className = 'timeline-card__media';

            if (item.image) {
                var img = document.createElement('img');
                img.src = item.image;
                img.alt = item.title || 'Timeline image';
                img.loading = 'lazy';
                media.appendChild(img);
            }

            var year = document.createElement('div');
            year.className = 'timeline-card__year';
            year.textContent = item.year || '';
            media.appendChild(year);

            var title = document.createElement('h3');
            title.className = 'timeline-card__title';
            title.textContent = item.title || '';

            var copy = document.createElement('div');
            copy.className = 'timeline-card__copy';
            copy.innerHTML = item.copy || '';

            card.appendChild(media);
            card.appendChild(title);
            card.appendChild(copy);

            track.appendChild(card);
        });

        var controls = document.createElement('div');
        controls.className = 'timeline-carousel__controls';

        var prev = document.createElement('button');
        prev.type = 'button';
        prev.className = 'timeline-carousel__nav timeline-carousel__nav--prev';
        prev.setAttribute('aria-label', 'Previous timeline cards');
        prev.innerHTML = '&larr;';

        var next = document.createElement('button');
        next.type = 'button';
        next.className = 'timeline-carousel__nav timeline-carousel__nav--next';
        next.setAttribute('aria-label', 'Next timeline cards');
        next.innerHTML = '&rarr;';

        controls.appendChild(prev);
        controls.appendChild(next);

        root.appendChild(track);
        root.appendChild(controls);

        function getCardWidth() {
            var card = track.querySelector('.timeline-card');
            if (!card) {
                return 0;
            }
            var style = window.getComputedStyle(track);
            var gap = parseFloat(style.columnGap || style.gap || '0');
            return card.getBoundingClientRect().width + gap;
        }

        function scrollByCards(dir) {
            var amount = getCardWidth();
            if (!amount) {
                return;
            }
            track.scrollBy({ left: dir * amount, behavior: 'smooth' });
        }

        prev.addEventListener('click', function() {
            scrollByCards(-1);
        });
        next.addEventListener('click', function() {
            scrollByCards(1);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', buildTimelineCarousel);
    } else {
        buildTimelineCarousel();
    }
})();
