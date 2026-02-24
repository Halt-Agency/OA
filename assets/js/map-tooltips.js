(function(window) {
    'use strict';

    var namespace = window.OAMapTooltips = window.OAMapTooltips || {};
    if (typeof namespace.onReady !== 'function') {
        return;
    }

    namespace.onReady(function() {
        if (typeof namespace.initLegacyIconTooltips === 'function') {
            namespace.initLegacyIconTooltips();
        }

        if (typeof namespace.initDynamicMapTooltips === 'function') {
            namespace.initDynamicMapTooltips();
        }
    });
})(window);
