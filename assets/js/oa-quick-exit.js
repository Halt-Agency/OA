(function () {
  function initQuickExit() {
    if (!document.body) {
      return;
    }

    var isJobsPage = document.body.classList.contains('page-id-3569');
    var isSingleJob = document.body.classList.contains('single-oa_job');

    if (!isJobsPage && !isSingleJob) {
      return;
    }

    if (document.querySelector('.oa-quick-exit')) {
      return;
    }

    var button = document.createElement('button');
    button.type = 'button';
    button.className = 'oa-quick-exit';
    button.setAttribute('aria-label', 'Quick exit');
    button.textContent = 'Quick Exit';
    document.body.appendChild(button);

    window.setTimeout(function () {
      button.classList.add('is-visible');
    }, 700);

    button.addEventListener('click', function () {
      window.close();

      window.setTimeout(function () {
        window.location.replace('https://www.google.com');
      }, 120);
    });
  }

  document.addEventListener('DOMContentLoaded', initQuickExit);
})();
