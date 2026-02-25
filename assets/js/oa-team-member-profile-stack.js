(function () {
  function escapeHtml(value) {
    return String(value || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/\"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function decodeEntities(value) {
    var text = document.createElement('textarea');
    text.innerHTML = String(value || '');
    return text.value;
  }

  function renderList(items) {
    if (!Array.isArray(items) || !items.length) {
      return '<p class="oa-team-member-profile-stack__empty">None listed</p>';
    }

    return '<div class="oa-team-member-profile-stack__chips">' +
      items.map(function (item) {
        var label = '';
        var url = '';

        if (item && typeof item === 'object') {
          label = decodeEntities(item.label || '');
          url = String(item.url || '');
        } else {
          label = decodeEntities(item);
        }

        if (url) {
          return '<a class="oa-team-member-profile-stack__chip oa-team-member-profile-stack__chip--link" href="' + escapeHtml(url) + '">' + escapeHtml(label) + '</a>';
        }

        return '<span class="oa-team-member-profile-stack__chip">' + escapeHtml(label) + '</span>';
      }).join('') +
      '</div>';
  }

  function renderStack(container, data) {
    var image = data && data.image ? String(data.image) : '';
    var name = data && data.name ? decodeEntities(String(data.name)) : '';
    var role = data && data.job_title ? decodeEntities(String(data.job_title)) : '';
    var specialismsHeading = data && data.specialisms_heading ? decodeEntities(String(data.specialisms_heading)) : 'Specialisms';
    var solutionsHeading = data && data.solutions_heading ? decodeEntities(String(data.solutions_heading)) : 'Solutions';

    container.classList.add('oa-team-member-profile-stack');

    container.innerHTML =
      '<div class="oa-team-member-profile-stack__media">' +
        (image ? '<img src="' + escapeHtml(image) + '" alt="' + escapeHtml(name) + '" loading="lazy" />' : '') +
      '</div>' +
      '<h1 class="oa-team-member-profile-stack__name">' + escapeHtml(name) + '</h1>' +
      '<p class="oa-team-member-profile-stack__role">' + escapeHtml(role) + '</p>' +
      '<div class="oa-team-member-profile-stack__divider" aria-hidden="true"></div>' +
      '<div class="oa-team-member-profile-stack__group">' +
        '<h3 class="oa-team-member-profile-stack__label">' + escapeHtml(specialismsHeading) + '</h3>' +
        renderList(data ? data.specialisms : []) +
      '</div>' +
      '<div class="oa-team-member-profile-stack__group">' +
        '<h3 class="oa-team-member-profile-stack__label">' + escapeHtml(solutionsHeading) + '</h3>' +
        renderList(data ? data.solutions : []) +
      '</div>';
  }

  document.addEventListener('DOMContentLoaded', function () {
    var containers = document.querySelectorAll('[data-oa-team-member-profile-stack], .oa-team-member-profile-stack');
    if (!containers.length) {
      return;
    }

    var data = window.oaTeamMemberProfileStack || {};

    containers.forEach(function (container) {
      renderStack(container, data);
    });
  });
})();
