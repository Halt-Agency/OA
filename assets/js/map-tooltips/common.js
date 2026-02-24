(function(window) {
    'use strict';

    var namespace = window.OAMapTooltips = window.OAMapTooltips || {};

    namespace.toSuffix = function(value) {
        return String(value || '').toLowerCase().replace(/\s+/g, '-');
    };

    namespace.onReady = function(callback) {
        if (typeof callback !== 'function') {
            return;
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
            return;
        }

        callback();
    };
})(window);
