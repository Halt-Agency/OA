(function () {
  function isTargetPage() {
    if (!document.body) {
      return false;
    }

    var classes = document.body.classList;
    return (
      classes.contains('page-id-11') ||
      classes.contains('page-id-15') ||
      classes.contains('page-candidates') ||
      classes.contains('page-register-cv')
    );
  }

  function resolveJobsUrl() {
    var explicitLink = document.querySelector('a[href*="/jobs" i]');
    if (explicitLink && explicitLink.getAttribute('href')) {
      return explicitLink.getAttribute('href');
    }

    return '/jobs/';
  }

  function initJobSearchTab() {
    if (!isTargetPage()) {
      return;
    }

    if (document.querySelector('.oa-job-search-tab')) {
      return;
    }

    var tab = document.createElement('a');
    tab.className = 'oa-job-search-tab';
    tab.setAttribute('aria-label', 'Job search');
    tab.href = resolveJobsUrl();
    tab.textContent = 'Job Search';

    document.body.appendChild(tab);

    window.setTimeout(function () {
      tab.classList.add('is-visible');
    }, 700);
  }

  document.addEventListener('DOMContentLoaded', initJobSearchTab);
})();
