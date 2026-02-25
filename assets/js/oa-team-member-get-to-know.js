(function () {
  function decodeEntities(value) {
    var text = document.createElement('textarea');
    text.innerHTML = String(value || '');
    return text.value;
  }

  function buildText(data) {
    var heading = decodeEntities(data && data.heading ? data.heading : 'Get to know').trim();
    var firstName = decodeEntities(data && data.first_name ? data.first_name : '').trim();

    if (!firstName) {
      return heading;
    }

    return heading + ' ' + firstName;
  }

  document.addEventListener('DOMContentLoaded', function () {
    var containers = document.querySelectorAll('[data-oa-team-member-get-to-know], .oa-team-member-get-to-know');
    if (!containers.length) {
      return;
    }

    var text = buildText(window.oaTeamMemberGetToKnow || {});

    containers.forEach(function (container) {
      container.textContent = text;
    });
  });
})();
