(function(window) {
    'use strict';

    var namespace = window.OAMapTooltips = window.OAMapTooltips || {};
    var toSuffix = typeof namespace.toSuffix === 'function'
        ? namespace.toSuffix
        : function(value) { return String(value || '').toLowerCase().replace(/\s+/g, '-'); };

    namespace.initDynamicMapTooltips = function() {
        var buttons = document.querySelectorAll('.map-tooltip-button[data-tooltip]');
        var cards = document.querySelectorAll('[id^="contact-info-"]');
        if (!buttons.length) {
            return;
        }

        var mapCardData = (
            window.oaUkCoverageContacts &&
            typeof window.oaUkCoverageContacts === 'object'
        ) ? window.oaUkCoverageContacts : null;

        function resolveHostForButton(button) {
            var container = button && button.closest ? button.closest('.map-tooltip-buttons') : null;
            var targetSelector = (
                (button && button.getAttribute && button.getAttribute('data-tooltip-target')) ||
                (container && container.getAttribute && container.getAttribute('data-tooltip-target')) ||
                ''
            ).trim();

            if (targetSelector) {
                return document.querySelector(targetSelector);
            }

            return document.getElementById('map-tooltip-card-host') || document.querySelector('[data-map-tooltip-host]');
        }

        var hasAnyDynamicHost = Array.prototype.some.call(buttons, function(button) {
            return !!resolveHostForButton(button);
        });
        var activeHost = null;
        var useDynamicCards = !!mapCardData && Object.keys(mapCardData).length > 0 && hasAnyDynamicHost;
        var locationLinkByKey = {
            bedfordshire: '/locations/bedfordshire',
            buckinghamshire: '/locations/buckinghamshire',
            cambridgeshire: '/locations/cambridgeshire',
            hertfordshire: '/locations/hertfordshire',
            north_london: '/locations/north-london'
        };

        if (!useDynamicCards && !cards.length) {
            return;
        }

        var cardsBySuffix = {};
        var buttonBySuffix = {};
        var activeSuffix = null;

        function setCardVisible(card, visible) {
            if (!card) {
                return;
            }

            card.setAttribute('data-map-card-visible', visible ? 'true' : 'false');

            if (visible) {
                card.setAttribute('aria-hidden', 'false');
                card.classList.add('is-visible');
                return;
            }

            card.classList.remove('is-visible');
            card.setAttribute('aria-hidden', 'true');
        }

        function setButtonActive(currentActiveSuffix) {
            Object.keys(buttonBySuffix).forEach(function(key) {
                var button = buttonBySuffix[key];
                var isActive = key === currentActiveSuffix;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
        }

        function hideAll() {
            if (useDynamicCards && activeHost) {
                activeHost.innerHTML = '';
                activeHost.classList.remove('is-visible');
                activeHost.setAttribute('aria-hidden', 'true');
                activeHost = null;
                activeSuffix = null;
            }
            Object.keys(cardsBySuffix).forEach(function(suffix) {
                setCardVisible(cardsBySuffix[suffix], false);
            });
            setButtonActive(null);
        }

        function showForSuffix(suffix) {
            var showAll = suffix === 'view-all';
            Object.keys(cardsBySuffix).forEach(function(key) {
                var shouldShow = showAll || key === suffix;
                setCardVisible(cardsBySuffix[key], shouldShow);
            });
            setButtonActive(suffix);
        }

        function isSuffixVisible(suffix, button) {
            if (useDynamicCards) {
                var resolvedHost = resolveHostForButton(button);
                return activeSuffix === suffix && !!resolvedHost && activeHost === resolvedHost;
            }
            if (suffix === 'view-all') {
                return Object.keys(cardsBySuffix).every(function(key) {
                    return cardsBySuffix[key].getAttribute('data-map-card-visible') === 'true';
                });
            }
            var card = cardsBySuffix[suffix];
            if (!card) {
                return false;
            }
            return card.getAttribute('data-map-card-visible') === 'true';
        }

        if (!useDynamicCards) {
            cards.forEach(function(card) {
                var suffix = card.id.replace(/^contact-info-/, '');
                if (!suffix) {
                    return;
                }
                cardsBySuffix[suffix] = card;
                // Keep each group's native display mode (e.g. flex) instead of forcing block.
                card.style.display = '';
                card.classList.add('map-contact-info-card');
                card.setAttribute('data-map-card-visible', 'false');
                card.setAttribute('aria-hidden', 'true');
            });
        }

        function labelFromSuffix(suffix) {
            return String(suffix || '')
                .replace(/-/g, ' ')
                .replace(/\b\w/g, function(chr) {
                    return chr.toUpperCase();
                });
        }

        function extractImageUrl(value) {
            if (typeof value === 'string') {
                return value;
            }
            if (value && typeof value === 'object' && value.url) {
                return value.url;
            }
            return '';
        }

        function renderDynamicCard(suffix, button) {
            var host = resolveHostForButton(button);
            if (!useDynamicCards || !host) {
                return false;
            }
            var locationKey = suffix.replace(/-/g, '_');
            var locationData = mapCardData[locationKey];
            if (!locationData || typeof locationData !== 'object') {
                return false;
            }

            var memberName = locationData.team_member_name || '';
            var memberEmail = locationData.team_member_email || '';
            var imageUrl = extractImageUrl(locationData.team_member_image);

            var title = button.getAttribute('data-location-title') || locationData.location_title || button.textContent || labelFromSuffix(suffix);
            var linkText = button.getAttribute('data-location-link-text') || locationData.link_text || 'Find out more';
            var linkUrl = button.getAttribute('data-location-link') || locationData.link_url || locationLinkByKey[locationKey] || (memberEmail ? 'mailto:' + memberEmail : '#');

            var card = document.createElement('article');
            card.className = 'map-tooltip-card';

            var media = document.createElement('div');
            media.className = 'map-tooltip-card__media';
            if (imageUrl) {
                var img = document.createElement('img');
                img.className = 'map-tooltip-card__image';
                img.src = imageUrl;
                img.alt = memberName || title;
                img.loading = 'lazy';
                media.appendChild(img);
            }

            var content = document.createElement('div');
            content.className = 'map-tooltip-card__content';

            var heading = document.createElement('h3');
            heading.className = 'map-tooltip-card__location h3-zalando';
            heading.textContent = title;

            var name = document.createElement('p');
            name.className = 'map-tooltip-card__name';
            name.textContent = memberName;

            var email = document.createElement('p');
            email.className = 'map-tooltip-card__email';
            email.textContent = memberEmail;

            var person = document.createElement('div');
            person.className = 'map-tooltip-card__person';

            var cta = document.createElement('a');
            cta.className = 'map-tooltip-card__link';
            cta.href = linkUrl;
            cta.textContent = linkText + ' >';
            cta.addEventListener('click', function(event) {
                var href = cta.getAttribute('href') || '';
                var isModifierClick = event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button === 1;

                // Keep standard browser behavior for modified/new-tab clicks.
                if (isModifierClick) {
                    event.stopPropagation();
                    return;
                }

                // Force navigation for normal clicks in case external listeners interfere.
                event.preventDefault();
                event.stopPropagation();
                if (href && href !== '#') {
                    window.location.assign(href);
                }
            });

            content.appendChild(heading);
            if (memberName) {
                person.appendChild(name);
            }
            if (memberEmail) {
                person.appendChild(email);
            }
            if (person.children.length) {
                content.appendChild(person);
            }
            content.appendChild(cta);

            card.appendChild(media);
            card.appendChild(content);

            host.innerHTML = '';
            host.appendChild(card);
            host.classList.add('is-visible');
            host.setAttribute('aria-hidden', 'false');
            activeHost = host;
            activeSuffix = suffix;
            return true;
        }

        function resolveLocationLinkForSuffix(suffix, button) {
            if (suffix === 'view-all') {
                return '/locations';
            }

            var locationKey = suffix.replace(/-/g, '_');
            var locationData = mapCardData && typeof mapCardData === 'object' ? mapCardData[locationKey] : null;

            return (
                (button && button.getAttribute && button.getAttribute('data-location-link')) ||
                (locationData && locationData.link_url) ||
                locationLinkByKey[locationKey] ||
                ''
            );
        }

        function shouldUseDirectLinkMode() {
            return !!(window.matchMedia && window.matchMedia('(max-width: 980px)').matches);
        }

        function isWithinActiveHostBounds(event) {
            if (!activeHost || typeof event.clientX !== 'number' || typeof event.clientY !== 'number') {
                return false;
            }
            var rect = activeHost.getBoundingClientRect();
            return (
                event.clientX >= rect.left &&
                event.clientX <= rect.right &&
                event.clientY >= rect.top &&
                event.clientY <= rect.bottom
            );
        }

        buttons.forEach(function(button) {
            var suffix = toSuffix(button.getAttribute('data-tooltip'));
            if (!suffix) {
                return;
            }
            buttonBySuffix[suffix] = button;
            button.setAttribute('aria-pressed', 'false');

            button.addEventListener('click', function(event) {
                event.preventDefault();
                if (suffix === 'view-all') {
                    window.location.assign('/locations');
                    return;
                }

                if (shouldUseDirectLinkMode()) {
                    var directLink = resolveLocationLinkForSuffix(suffix, button);
                    if (directLink) {
                        window.location.assign(directLink);
                        return;
                    }
                }

                if (isSuffixVisible(suffix, button)) {
                    hideAll();
                    return;
                }
                if (useDynamicCards) {
                    if (renderDynamicCard(suffix, button)) {
                        setButtonActive(suffix);
                        return;
                    }
                    hideAll();
                    return;
                }
                showForSuffix(suffix);
            });
        });

        document.addEventListener('click', function(event) {
            var target = event.target;
            if (!target) {
                return;
            }

            var button = target.closest && target.closest('.map-tooltip-button');
            var isInsideResolvedHost = !!(activeHost && activeHost.contains && activeHost.contains(target));
            var isWithinHostBounds = isWithinActiveHostBounds(event);
            var card = target.closest && (
                target.closest('[id^="contact-info-"]') ||
                target.closest('#map-tooltip-card-host') ||
                target.closest('[data-map-tooltip-host]')
            );
            if (!button && !card && !isInsideResolvedHost && !isWithinHostBounds) {
                hideAll();
            }
        });
    };
})(window);
