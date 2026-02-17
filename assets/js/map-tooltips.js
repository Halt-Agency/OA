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

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMapTooltips);
    } else {
        initMapTooltips();
    }
})();
