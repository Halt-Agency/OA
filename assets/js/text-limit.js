(function () {
    'use strict';

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', limitTextModules);
    } else {
        limitTextModules();
    }

    function limitTextModules() {
        var maxWords = 18;
        var nodes = document.querySelectorAll('.limit-text');

        nodes.forEach(function (node) {
            if (node.dataset.limitApplied === 'true') {
                return;
            }

            var text = (node.textContent || '').trim();
            if (!text) {
                return;
            }

            var words = text.split(/\s+/);
            if (words.length > maxWords) {
                node.textContent = words.slice(0, maxWords).join(' ') + '...';
            }

            node.dataset.limitApplied = 'true';
        });
    }
})();
