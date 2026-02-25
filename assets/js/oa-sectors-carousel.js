(function() {
    'use strict';

    function resolveImageUrl(imageValue) {
        if (!imageValue) {
            return '';
        }
        if (typeof imageValue === 'string') {
            return imageValue;
        }
        if (typeof imageValue === 'object' && imageValue.url) {
            return imageValue.url;
        }
        return '';
    }

    function buildSectorsCarousel(container) {
        var data =
            window.dtACFData &&
            window.dtACFData.page_content &&
            Array.isArray(window.dtACFData.page_content.sectors)
                ? window.dtACFData.page_content.sectors
                : [];

        if (data.length === 0) {
            return;
        }

        container.innerHTML = '';

        var header = document.createElement('div');
        header.className = 'oa-sectors-carousel__header';

        var controls = document.createElement('div');
        controls.className = 'oa-sectors-carousel__controls';

        var prev = document.createElement('button');
        prev.type = 'button';
        prev.className = 'oa-sectors-carousel__nav oa-sectors-carousel__nav--prev';
        prev.setAttribute('aria-label', 'Previous sectors');
        prev.innerHTML = '<svg class="oa-sectors-carousel__icon" viewBox="0 0 185.343 185.343" aria-hidden="true"><path d="M51.707,185.343c-2.741,0-5.493-1.044-7.593-3.149c-4.194-4.194-4.194-10.981,0-15.175l74.352-74.347L44.114,18.32c-4.194-4.194-4.194-10.987,0-15.175c4.194-4.194,10.987-4.194,15.18,0l81.934,81.934c4.194,4.194,4.194,10.987,0,15.175l-81.934,81.939C57.201,184.293,54.454,185.343,51.707,185.343z"></path></svg>';

        var next = document.createElement('button');
        next.type = 'button';
        next.className = 'oa-sectors-carousel__nav oa-sectors-carousel__nav--next';
        next.setAttribute('aria-label', 'Next sectors');
        next.innerHTML = '<svg class="oa-sectors-carousel__icon" viewBox="0 0 185.343 185.343" aria-hidden="true"><path d="M51.707,185.343c-2.741,0-5.493-1.044-7.593-3.149c-4.194-4.194-4.194-10.981,0-15.175l74.352-74.347L44.114,18.32c-4.194-4.194-4.194-10.987,0-15.175c4.194-4.194,10.987-4.194,15.18,0l81.934,81.934c4.194,4.194,4.194,10.987,0,15.175l-81.934,81.939C57.201,184.293,54.454,185.343,51.707,185.343z"></path></svg>';

        controls.appendChild(prev);
        controls.appendChild(next);
        header.appendChild(controls);

        var frame = document.createElement('div');
        frame.className = 'oa-sectors-carousel__frame';

        var viewport = document.createElement('div');
        viewport.className = 'oa-sectors-carousel__viewport';

        var track = document.createElement('div');
        track.className = 'oa-sectors-carousel__track';

        data.forEach(function(sector) {
            var slide = document.createElement('article');
            slide.className = 'oa-sectors-carousel__card';

            var link = document.createElement('a');
            link.className = 'oa-sectors-carousel__link';
            link.href = (sector && sector.link) ? sector.link : '#';

            var media = document.createElement('div');
            media.className = 'oa-sectors-carousel__media';

            var imageUrl = resolveImageUrl(sector ? sector.image : '');
            if (imageUrl) {
                var img = document.createElement('img');
                img.src = imageUrl;
                img.alt = (sector && sector.title) ? sector.title : 'Sector image';
                img.loading = 'lazy';
                media.appendChild(img);
            }

            var title = document.createElement('h3');
            title.className = 'oa-sectors-carousel__title';
            title.textContent = (sector && sector.title) ? sector.title : '';

            link.appendChild(media);
            link.appendChild(title);
            slide.appendChild(link);
            track.appendChild(slide);
        });

        viewport.appendChild(track);
        frame.appendChild(viewport);

        container.appendChild(header);
        container.appendChild(frame);

        var currentIndex = 0;

        function getSlideStep() {
            var slide = track.querySelector('.oa-sectors-carousel__card');
            if (!slide) {
                return 0;
            }
            var style = window.getComputedStyle(track);
            var gap = parseFloat(style.columnGap || style.gap || '0');
            return slide.getBoundingClientRect().width + gap;
        }

        function getMaxIndex() {
            return Math.max(0, data.length - 1);
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
        });

        updateNavState();
        applyTransform();
    }

    document.addEventListener('DOMContentLoaded', function() {
        var containers = document.querySelectorAll('.oa-sectors-carousel');
        containers.forEach(buildSectorsCarousel);
    });
})();
