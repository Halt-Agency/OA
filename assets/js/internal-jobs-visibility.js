(function () {
    'use strict';

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', toggleInternalJobsModules);
    } else {
        toggleInternalJobsModules();
    }

    function toggleInternalJobsModules() {
        var carouselModule = document.getElementById('internal-job-carousel');
        var emptyModule = document.getElementById('no-internal-jobs');

        if (!carouselModule && !emptyModule) {
            return;
        }

        fetch('/wp-json/wp/v2/internal_job?per_page=1&_fields=id')
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Failed to fetch internal jobs');
                }
                return response.json();
            })
            .then(function (jobs) {
                var hasPublishedJobs = Array.isArray(jobs) && jobs.length > 0;
                if (hasPublishedJobs) {
                    return;
                }

                if (carouselModule) {
                    carouselModule.style.setProperty('display', 'none', 'important');
                }
                if (emptyModule) {
                    emptyModule.classList.add('is-visible');
                }
            })
            .catch(function () {
                // Fail silently to avoid breaking other front-end behavior.
            });
    }
})();
