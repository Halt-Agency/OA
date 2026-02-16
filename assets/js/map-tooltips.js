// Simple tooltip toggles for About page map icons.
(function() {
    'use strict';

    function initMapTooltips() {
        var icons = document.querySelectorAll('[id^="icon-"]');
        if (!icons.length) {
            return;
        }

        var tooltipMap = {};
        var groupMap = {};
        icons.forEach(function(icon) {
            var suffix = icon.id.replace(/^icon-/, '');
            var tooltipId = 'tooltip-' + suffix;
            var tooltip = document.getElementById(tooltipId);
            var group = icon.closest('[id^="group-"]') || document.getElementById('group-' + suffix);
            if (tooltip) {
                tooltipMap[icon.id] = tooltip;
                groupMap[icon.id] = group || null;
                tooltip.style.display = 'none';
                tooltip.setAttribute('aria-hidden', 'true');
            }
        });

        function hideAll() {
            Object.keys(tooltipMap).forEach(function(key) {
                var tip = tooltipMap[key];
                tip.style.display = 'none';
                tip.setAttribute('aria-hidden', 'true');
            });
        }

        function showFor(iconId) {
            hideAll();
            var tip = tooltipMap[iconId];
            if (tip) {
                tip.style.display = 'block';
                tip.setAttribute('aria-hidden', 'false');
            }
        }

        icons.forEach(function(icon) {
            if (!tooltipMap[icon.id]) {
                return;
            }

            icon.addEventListener('mouseenter', function() {
                showFor(icon.id);
            });
            icon.addEventListener('focus', function() {
                showFor(icon.id);
            });
            icon.addEventListener('click', function(event) {
                event.preventDefault();
                var tip = tooltipMap[icon.id];
                if (!tip) {
                    return;
                }
                var isHidden = tip.getAttribute('aria-hidden') === 'true';
                if (isHidden) {
                    showFor(icon.id);
                } else {
                    hideAll();
                }
            });

            var tip = tooltipMap[icon.id];
            if (tip) {
                tip.addEventListener('mouseleave', function() {
                    hideAll();
                });
            }

            var group = groupMap[icon.id];
            if (group) {
                group.addEventListener('mouseleave', hideAll);
                group.addEventListener('focusout', function(event) {
                    if (!group.contains(event.relatedTarget)) {
                        hideAll();
                    }
                });
            }
        });

        document.addEventListener('click', function(event) {
            var target = event.target;
            if (!target) {
                return;
            }
            var icon = target.closest && target.closest('[id^="icon-"]');
            var group = target.closest && target.closest('[id^="group-"]');
            if (!icon && !group) {
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
