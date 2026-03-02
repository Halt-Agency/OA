(function () {
  var DEBUG = false;

  function debugLog(eventName, payload) {
    if (!DEBUG || typeof console === 'undefined' || !console.log) {
      return;
    }
    console.log('[oa-header-menu]', eventName, payload || {});
  }

  function describeElement(el) {
    if (!el || !el.tagName) {
      return null;
    }
    var out = el.tagName.toLowerCase();
    if (el.id) {
      out += '#' + el.id;
    }
    if (el.className && typeof el.className === 'string') {
      out += '.' + el.className.trim().replace(/\s+/g, '.');
    }
    return out;
  }

  function closeAll(root) {
    debugLog('closeAll:start', { root: describeElement(root) });
    root.querySelectorAll('[data-oa-menu-item].is-open').forEach(function (item) {
      debugLog('closeAll:item', { item: describeElement(item) });
      item.classList.remove('is-open');
      var trigger = item.querySelector('[data-oa-menu-trigger]');
      if (trigger) {
        trigger.setAttribute('aria-expanded', 'false');
      }
    });
  }

  function setOpen(item, isOpen) {
    var trigger = item.querySelector('[data-oa-menu-trigger]');
    debugLog('setOpen', {
      item: describeElement(item),
      isOpen: isOpen
    });
    item.classList.toggle('is-open', isOpen);
    if (trigger) {
      trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }
  }

  function isMobile() {
    return window.matchMedia('(max-width: 980px)').matches;
  }

  function positionPanel(item) {
    if (!item || isMobile()) {
      return;
    }

    var panel = item.querySelector('[data-oa-menu-panel]');
    if (!panel) {
      return;
    }
    panel.classList.remove('oa-header-menu__panel--align-left', 'oa-header-menu__panel--align-right');

    var trigger = item.querySelector('[data-oa-menu-trigger]') || item.querySelector('.oa-header-menu__link');
    var itemRect = item.getBoundingClientRect();
    var triggerRect = trigger ? trigger.getBoundingClientRect() : itemRect;
    var viewportWidth = window.innerWidth || document.documentElement.clientWidth;
    var edgePadding = 8;

    panel.style.left = '';
    panel.style.right = '';
    panel.style.transform = '';

    if (panel.classList.contains('oa-header-menu__panel--mega')) {
      var megaRect = panel.getBoundingClientRect();
      var megaWidth = megaRect.width;
      var preferredLeft = triggerRect.left + (triggerRect.width / 2) - (megaWidth / 2);
      var clampedLeft = Math.max(edgePadding, Math.min(preferredLeft, viewportWidth - megaWidth - edgePadding));
      var relativeLeft = clampedLeft - itemRect.left;

      panel.style.left = relativeLeft + 'px';
      panel.style.right = 'auto';
      panel.style.transform = 'none';

      debugLog('positionPanel:mega', {
        item: describeElement(item),
        preferredLeft: preferredLeft,
        clampedLeft: clampedLeft
      });
      return;
    }

    var spaceLeft = triggerRect.left;
    var spaceRight = viewportWidth - triggerRect.right;

    if (spaceRight >= spaceLeft) {
      panel.style.left = '0px';
      panel.style.right = 'auto';
      panel.classList.add('oa-header-menu__panel--align-left');
    } else {
      panel.style.left = 'auto';
      panel.style.right = '0px';
      panel.classList.add('oa-header-menu__panel--align-right');
    }

    var rect = panel.getBoundingClientRect();
    if (rect.right > viewportWidth - edgePadding) {
      var overflowRight = rect.right - (viewportWidth - edgePadding);
      var currentLeft = parseFloat(panel.style.left);
      panel.style.left = (isNaN(currentLeft) ? 0 : currentLeft) - overflowRight + 'px';
      panel.style.right = 'auto';
      rect = panel.getBoundingClientRect();
    }

    if (rect.left < edgePadding) {
      var overflowLeft = edgePadding - rect.left;
      var currentLeftAfter = parseFloat(panel.style.left);
      panel.style.left = (isNaN(currentLeftAfter) ? 0 : currentLeftAfter) + overflowLeft + 'px';
      panel.style.right = 'auto';
    }

    debugLog('positionPanel:dropdown', {
      item: describeElement(item),
      spaceLeft: spaceLeft,
      spaceRight: spaceRight,
      panelLeft: panel.style.left,
      panelRight: panel.style.right
    });
  }

  function initHeaderMenu(root) {
    if (!root || root.dataset.oaHeaderMenuReady === '1') {
      return;
    }
    root.dataset.oaHeaderMenuReady = '1';

    var toggle = root.querySelector('[data-oa-header-toggle]');
    var items = root.querySelectorAll('[data-oa-menu-item]');

    if (toggle) {
      toggle.addEventListener('click', function () {
        var next = !root.classList.contains('is-mobile-open');
        root.classList.toggle('is-mobile-open', next);
        toggle.setAttribute('aria-expanded', next ? 'true' : 'false');
        if (!next) {
          closeAll(root);
        }
      });
    }

    items.forEach(function (item) {
      var trigger = item.querySelector('[data-oa-menu-trigger]');
      if (!trigger) {
        return;
      }

      trigger.addEventListener('click', function (event) {
        debugLog('trigger:click', {
          item: describeElement(item),
          mobile: isMobile(),
          target: describeElement(event.target)
        });
        if (!isMobile()) {
          event.preventDefault();
          return;
        }

        var shouldOpen = !item.classList.contains('is-open');
        closeAll(root);
        setOpen(item, shouldOpen);
      });

      item.addEventListener('mouseenter', function () {
        if (isMobile()) {
          return;
        }
        debugLog('item:mouseenter', { item: describeElement(item) });
        closeAll(root);
        // Reverted custom viewport-aware orientation; use default CSS orientation.
        // positionPanel(item);
        setOpen(item, true);
      });

      item.addEventListener('mouseleave', function () {
        if (isMobile()) {
          return;
        }
        debugLog('item:mouseleave', { item: describeElement(item) });
        setOpen(item, false);
      });
    });

    root.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        debugLog('root:escape', { root: describeElement(root) });
        closeAll(root);
        if (toggle) {
          root.classList.remove('is-mobile-open');
          toggle.setAttribute('aria-expanded', 'false');
          toggle.focus();
        }
      }
    });

    document.addEventListener('click', function (event) {
      if (!root.contains(event.target)) {
        debugLog('document:outside-click', {
          root: describeElement(root),
          target: describeElement(event.target)
        });
        closeAll(root);
      }
    });

    window.addEventListener('resize', function () {
      debugLog('window:resize', { mobile: isMobile() });
      if (!isMobile()) {
        root.classList.remove('is-mobile-open');
        if (toggle) {
          toggle.setAttribute('aria-expanded', 'false');
        }
      }
      closeAll(root);
    });
  }

  function mountMenu(mountNode) {
    if (!mountNode || mountNode.dataset.oaHeaderMenuMounted === '1') {
      return;
    }
    mountNode.dataset.oaHeaderMenuMounted = '1';

    fetch('/wp-json/oa/v1/header-menu', { credentials: 'same-origin' })
      .then(function (response) {
        return response.ok ? response.json() : null;
      })
      .then(function (payload) {
        if (!payload || typeof payload.html !== 'string' || payload.html.trim() === '') {
          return;
        }
        mountNode.innerHTML = payload.html;
        var menu = mountNode.querySelector('[data-oa-header-menu]');
        if (menu) {
          initHeaderMenu(menu);
        }
      })
      .catch(function () {
      });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-oa-header-menu]').forEach(initHeaderMenu);
    document.querySelectorAll('[data-oa-header-menu-mount]').forEach(mountMenu);
  });
})();
