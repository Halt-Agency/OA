// Simple tooltip toggles for About page map icons.
(function() {
    'use strict';

    function initMapTooltips() {
        var icons = document.querySelectorAll('[id^="icon-"]');
        var buttons = document.querySelectorAll('.map-tooltip-button[data-tooltip]');
        if (!icons.length && !buttons.length) {
            return;
        }

        var tooltipBySuffix = {};
        var buttonBySuffix = {};
        var iconBySuffix = {};

        function toSuffix(value) {
            return String(value || '').toLowerCase().replace(/\s+/g, '-');
        }

        icons.forEach(function(icon) {
            var suffix = icon.id.replace(/^icon-/, '');
            var tooltipId = 'tooltip-' + suffix;
            var tooltip = document.getElementById(tooltipId);
            if (tooltip) {
                tooltipBySuffix[suffix] = tooltip;
                iconBySuffix[suffix] = icon;
                tooltip.style.display = 'none';
                tooltip.setAttribute('aria-hidden', 'true');
            }
        });

        buttons.forEach(function(button) {
            var suffix = toSuffix(button.getAttribute('data-tooltip'));
            if (!suffix) {
                return;
            }
            buttonBySuffix[suffix] = button;
            button.setAttribute('aria-pressed', 'false');
        });

        function deactivateButtons() {
            Object.keys(buttonBySuffix).forEach(function(key) {
                var btn = buttonBySuffix[key];
                btn.classList.remove('is-active');
                btn.setAttribute('aria-pressed', 'false');
            });
        }

        function deactivateIcons() {
            Object.keys(iconBySuffix).forEach(function(key) {
                var icon = iconBySuffix[key];
                icon.classList.remove('is-active');
            });
        }

        function hideAll() {
            Object.keys(tooltipBySuffix).forEach(function(key) {
                var tip = tooltipBySuffix[key];
                tip.style.display = 'none';
                tip.setAttribute('aria-hidden', 'true');
            });
            deactivateButtons();
            deactivateIcons();
        }

        function showForSuffix(suffix) {
            hideAll();
            var tip = tooltipBySuffix[suffix];
            if (tip) {
                tip.style.display = 'block';
                tip.setAttribute('aria-hidden', 'false');
            }
            var btn = buttonBySuffix[suffix];
            if (btn) {
                btn.classList.add('is-active');
                btn.setAttribute('aria-pressed', 'true');
            }
            var icon = iconBySuffix[suffix];
            if (icon) {
                icon.classList.add('is-active');
            }
        }

        icons.forEach(function(icon) {
            var suffix = icon.id.replace(/^icon-/, '');
            if (!tooltipBySuffix[suffix]) {
                return;
            }
            icon.addEventListener('click', function(event) {
                event.preventDefault();
                var tip = tooltipBySuffix[suffix];
                if (!tip) {
                    return;
                }
                var isHidden = tip.getAttribute('aria-hidden') === 'true';
                if (isHidden) {
                    showForSuffix(suffix);
                } else {
                    hideAll();
                }
            });
        });

        buttons.forEach(function(button) {
            var suffix = toSuffix(button.getAttribute('data-tooltip'));
            if (!tooltipBySuffix[suffix]) {
                return;
            }
            button.addEventListener('click', function(event) {
                event.preventDefault();
                var tip = tooltipBySuffix[suffix];
                if (!tip) {
                    return;
                }
                var isHidden = tip.getAttribute('aria-hidden') === 'true';
                if (isHidden) {
                    showForSuffix(suffix);
                } else {
                    hideAll();
                }
            });
        });

        document.addEventListener('click', function(event) {
            var target = event.target;
            if (!target) {
                return;
            }
            var button = target.closest && target.closest('.map-tooltip-button');
            var icon = target.closest && target.closest('[id^="icon-"]');
            var tooltip = target.closest && target.closest('[id^="tooltip-"]');
            if (!button && !icon && !tooltip) {
                hideAll();
            }
        });
    }

    function initMapContactInfoToggle() {
        var buttons = document.querySelectorAll('.map-tooltip-button[data-tooltip]');
        var cards = document.querySelectorAll('[id^="contact-info-"]');
        if (!buttons.length || !cards.length) {
            return;
        }

        var cardsBySuffix = {};
        var buttonBySuffix = {};

        function toSuffix(value) {
            return String(value || '').toLowerCase().replace(/\s+/g, '-');
        }

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

        function setButtonActive(activeSuffix) {
            Object.keys(buttonBySuffix).forEach(function(key) {
                var button = buttonBySuffix[key];
                var isActive = key === activeSuffix;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
        }

        function hideAll() {
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

        function isSuffixVisible(suffix) {
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
                if (isSuffixVisible(suffix)) {
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
            var card = target.closest && target.closest('[id^="contact-info-"]');
            if (!button && !card) {
                hideAll();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initMapTooltips();
            initMapContactInfoToggle();
        });
    } else {
        initMapTooltips();
        initMapContactInfoToggle();
    }
})();
