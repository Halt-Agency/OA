(function () {
  function escapeHtml(value) {
    return String(value || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function renderJobsMicrositeLogos(container) {
    var logos = Array.isArray(window.oaJobsMicrositeLogos) ? window.oaJobsMicrositeLogos : [];
    if (!logos.length) {
      container.innerHTML = '';
      return;
    }

    container.classList.add('oa-jobs-microsite-logos');

    var items = logos.map(function (item) {
      var title = item && item.title ? item.title : '';
      var link = item && item.url ? item.url : '#';
      var logo = item && item.logo ? item.logo : {};
      var logoUrl = logo.url || '';
      var logoAlt = logo.alt || title;

      return ''
        + '<a class="oa-jobs-microsite-logos__item" href="' + escapeHtml(link) + '">'
        + '  <span class="oa-jobs-microsite-logos__media">'
        + '    <img src="' + escapeHtml(logoUrl) + '" alt="' + escapeHtml(logoAlt) + '" loading="lazy">'
        + '  </span>'
        + '</a>';
    }).join('');

    container.innerHTML = '<div class="oa-jobs-microsite-logos__track">' + items + '</div>';
  }

  document.addEventListener('DOMContentLoaded', function () {
    var containers = document.querySelectorAll('[data-oa-jobs-microsite-logos], .oa-jobs-microsite-logos');
    containers.forEach(renderJobsMicrositeLogos);
  });
})();
