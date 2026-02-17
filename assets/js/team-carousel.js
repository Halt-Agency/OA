(function() {
    'use strict';

    function buildTeamCarousel(container) {
        var data = window.oaTeamCarousel;
        if (!Array.isArray(data) || data.length === 0) {
            return;
        }

        container.innerHTML = '';

        var frame = document.createElement('div');
        frame.className = 'team-carousel__frame';

        var viewport = document.createElement('div');
        viewport.className = 'team-carousel__viewport';

        var track = document.createElement('div');
        track.className = 'team-carousel__track';

        data.forEach(function(member) {
            var slide = document.createElement('article');
            slide.className = 'team-carousel__card';

            var link = document.createElement('a');
            link.className = 'team-carousel__link';
            link.href = member.link || '#';

            var media = document.createElement('div');
            media.className = 'team-carousel__media';

            if (member.image) {
                var img = document.createElement('img');
                img.src = member.image;
                img.alt = member.name || 'Team member';
                img.loading = 'lazy';
                media.appendChild(img);
            }

            var name = document.createElement('h3');
            name.className = 'team-carousel__name';
            name.textContent = member.name || '';

            var title = document.createElement('p');
            title.className = 'team-carousel__title';
            title.textContent = member.job_title || '';

            link.appendChild(media);
            link.appendChild(name);
            link.appendChild(title);
            slide.appendChild(link);
            track.appendChild(slide);
        });

        viewport.appendChild(track);

        var controls = document.createElement('div');
        controls.className = 'team-carousel__controls';

        var prev = document.createElement('button');
        prev.type = 'button';
        prev.className = 'team-carousel__nav team-carousel__nav--prev';
        prev.setAttribute('aria-label', 'Previous team members');
        prev.innerHTML = '<svg class="team-carousel__icon" viewBox="0 0 185.343 185.343" aria-hidden="true"><path d="M51.707,185.343c-2.741,0-5.493-1.044-7.593-3.149c-4.194-4.194-4.194-10.981,0-15.175l74.352-74.347L44.114,18.32c-4.194-4.194-4.194-10.987,0-15.175c4.194-4.194,10.987-4.194,15.18,0l81.934,81.934c4.194,4.194,4.194,10.987,0,15.175l-81.934,81.939C57.201,184.293,54.454,185.343,51.707,185.343z"></path></svg>';

        var next = document.createElement('button');
        next.type = 'button';
        next.className = 'team-carousel__nav team-carousel__nav--next';
        next.setAttribute('aria-label', 'Next team members');
        next.innerHTML = '<svg class="team-carousel__icon" viewBox="0 0 185.343 185.343" aria-hidden="true"><path d="M51.707,185.343c-2.741,0-5.493-1.044-7.593-3.149c-4.194-4.194-4.194-10.981,0-15.175l74.352-74.347L44.114,18.32c-4.194-4.194-4.194-10.987,0-15.175c4.194-4.194,10.987-4.194,15.18,0l81.934,81.934c4.194,4.194,4.194,10.987,0,15.175l-81.934,81.939C57.201,184.293,54.454,185.343,51.707,185.343z"></path></svg>';

        controls.appendChild(prev);
        controls.appendChild(next);

        frame.appendChild(prev);
        frame.appendChild(viewport);
        frame.appendChild(next);

        container.appendChild(frame);

        var currentIndex = 0;

        function getSlideStep() {
            var slide = track.querySelector('.team-carousel__card');
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
        var containers = document.querySelectorAll('.team-carousel');
        containers.forEach(buildTeamCarousel);
    });
})();
