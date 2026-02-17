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

        var viewport = document.createElement('div');
        viewport.className = 'timeline-carousel__viewport';

        var track = document.createElement('div');
        track.className = 'timeline-carousel__track';
        var line = document.createElement('div');
        line.className = 'timeline-carousel__line';

        items.forEach(function(item, index) {
            var slide = document.createElement('div');
            slide.className = 'timeline-slide';
            slide.setAttribute('data-index', String(index));

            var card = document.createElement('article');
            card.className = 'timeline-card';

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

            var marker = document.createElement('div');
            marker.className = 'timeline-slide__marker';

            var dot = document.createElement('span');
            dot.className = 'timeline-slide__dot';
            dot.setAttribute('aria-hidden', 'true');
            marker.appendChild(dot);

            slide.appendChild(card);
            slide.appendChild(marker);
            track.appendChild(slide);
        });

        track.appendChild(line);
        viewport.appendChild(track);

        var controls = document.createElement('div');
        controls.className = 'timeline-carousel__controls';

        var prev = document.createElement('button');
        prev.type = 'button';
        prev.className = 'timeline-carousel__nav timeline-carousel__nav--prev';
        prev.setAttribute('aria-label', 'Previous timeline cards');
        prev.innerHTML = '<svg class="timeline-carousel__icon" viewBox="0 0 185.343 185.343" aria-hidden="true"><path d="M51.707,185.343c-2.741,0-5.493-1.044-7.593-3.149c-4.194-4.194-4.194-10.981,0-15.175l74.352-74.347L44.114,18.32c-4.194-4.194-4.194-10.987,0-15.175c4.194-4.194,10.987-4.194,15.18,0l81.934,81.934c4.194,4.194,4.194,10.987,0,15.175l-81.934,81.939C57.201,184.293,54.454,185.343,51.707,185.343z"></path></svg>';

        var next = document.createElement('button');
        next.type = 'button';
        next.className = 'timeline-carousel__nav timeline-carousel__nav--next';
        next.setAttribute('aria-label', 'Next timeline cards');
        next.innerHTML = '<svg class="timeline-carousel__icon" viewBox="0 0 185.343 185.343" aria-hidden="true"><path d="M51.707,185.343c-2.741,0-5.493-1.044-7.593-3.149c-4.194-4.194-4.194-10.981,0-15.175l74.352-74.347L44.114,18.32c-4.194-4.194-4.194-10.987,0-15.175c4.194-4.194,10.987-4.194,15.18,0l81.934,81.934c4.194,4.194,4.194,10.987,0,15.175l-81.934,81.939C57.201,184.293,54.454,185.343,51.707,185.343z"></path></svg>';

        controls.appendChild(prev);
        controls.appendChild(next);

        root.appendChild(viewport);
        root.appendChild(controls);

        var currentIndex = 0;

        function getSlideStep() {
            var slide = track.querySelector('.timeline-slide');
            if (!slide) {
                return 0;
            }
            var style = window.getComputedStyle(track);
            var gap = parseFloat(style.columnGap || style.gap || '0');
            return slide.getBoundingClientRect().width + gap;
        }

        function updateLine() {
            var slide = track.querySelector('.timeline-slide');
            var dot = track.querySelector('.timeline-slide__dot');
            if (!slide || !dot) {
                return;
            }
            var marker = slide.querySelector('.timeline-slide__marker');
            var slideWidth = slide.getBoundingClientRect().width;
            var dotWidth = dot.getBoundingClientRect().width;
            var markerStyle = window.getComputedStyle(marker);
            var markerPadding = parseFloat(markerStyle.paddingLeft || '0');
            var dotCenter = markerPadding + (dotWidth / 2);
            var totalWidth = track.scrollWidth;
            var lineWidth = totalWidth - slideWidth + (dotCenter * 2) - 5;
            line.style.left = dotCenter + 'px';
            line.style.width = Math.max(0, lineWidth) + 'px';
            var markerHeight = marker.getBoundingClientRect().height;
            var lineHeight = line.getBoundingClientRect().height || 2;
            var lineBottom = (markerHeight / 2) - (lineHeight / 2);
            line.style.bottom = Math.max(0, lineBottom) + 'px';
        }

        function getMaxIndex() {
            return Math.max(0, items.length - 1);
        }

        function updateNavState() {
            prev.disabled = currentIndex <= 0;
            next.disabled = currentIndex >= getMaxIndex();
        }

        function applyTransform() {
            var step = getSlideStep();
            if (!step) {
                return;
            }
            var offset = step * currentIndex * -1;
            track.style.transform = 'translateX(' + offset + 'px)';
        }

        function goToIndex(nextIndex) {
            var maxIndex = getMaxIndex();
            currentIndex = Math.max(0, Math.min(nextIndex, maxIndex));
            applyTransform();
            updateNavState();
        }

        prev.addEventListener('click', function() {
            goToIndex(currentIndex - 1);
        });

        next.addEventListener('click', function() {
            goToIndex(currentIndex + 1);
        });

        window.addEventListener('resize', function() {
            applyTransform();
            updateLine();
        });

        updateNavState();
        applyTransform();
        updateLine();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', buildTimelineCarousel);
    } else {
        buildTimelineCarousel();
    }
})();
