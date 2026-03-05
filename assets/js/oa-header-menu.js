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

  function alignMegaPanelToMenuRight(item, root) {
    if (!item || !root || isMobile()) {
      return;
    }

    var megaPanel = item.querySelector('.oa-header-menu__panel--mega');
    if (!megaPanel) {
      return;
    }

    var menuRect = root.getBoundingClientRect();
    var itemRect = item.getBoundingClientRect();
    var offsetToMenuRight = menuRect.right - itemRect.right;
    var menuWidth = Math.max(0, menuRect.width);
    var extraWidthAllowance = 120;

    megaPanel.style.left = 'auto';
    megaPanel.style.right = (-offsetToMenuRight) + 'px';
    megaPanel.style.transform = 'none';
    megaPanel.style.maxWidth = (menuWidth + extraWidthAllowance) + 'px';

    debugLog('alignMegaPanelToMenuRight', {
      item: describeElement(item),
      offsetToMenuRight: offsetToMenuRight,
      menuWidth: menuWidth,
      extraWidthAllowance: extraWidthAllowance
    });
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

  function getMenuItemLabel(item) {
    var labelNode = item.querySelector('.oa-header-menu__trigger > span:first-child')
      || item.querySelector('.oa-header-menu__link')
      || item.querySelector('.oa-header-menu__trigger');
    return labelNode ? labelNode.textContent.trim() : '';
  }

  function getMenuItemUrl(item) {
    var link = item.querySelector('.oa-header-menu__trigger-wrap > .oa-header-menu__link[href]');
    return link ? link.getAttribute('href') : '';
  }

  function extractSubmenuLinks(item) {
    var panel = item.querySelector('[data-oa-menu-panel]');
    if (!panel) {
      return [];
    }

    var links = [];
    panel.querySelectorAll('a[href]').forEach(function (anchor) {
      var href = anchor.getAttribute('href');
      var text = anchor.textContent ? anchor.textContent.trim() : '';
      if (!href || !text) {
        return;
      }
      links.push({ href: href, text: text });
    });

    var seen = {};
    return links.filter(function (row) {
      var key = row.href + '|' + row.text;
      if (seen[key]) {
        return false;
      }
      seen[key] = true;
      return true;
    });
  }

  function buildMobileMenuModel(root) {
    var dataNode = root.querySelector('[data-oa-mobile-menu-data]');
    if (dataNode) {
      try {
        var raw = JSON.parse(dataNode.textContent || '[]');
        if (Array.isArray(raw) && raw.length) {
          return raw.map(function (entry) {
            var label = entry && typeof entry.label === 'string' ? entry.label.trim() : '';
            var submenuTitle = entry && typeof entry.submenuTitle === 'string' && entry.submenuTitle.trim() !== ''
              ? entry.submenuTitle.trim()
              : label;
            var submenuLinksRaw = entry && Array.isArray(entry.submenuLinks) ? entry.submenuLinks : [];
            var submenuLinks = submenuLinksRaw
              .map(function (row) {
                if (!row || typeof row !== 'object') {
                  return null;
                }
                var href = typeof row.href === 'string' ? row.href : '';
                var text = typeof row.text === 'string' ? row.text.trim() : '';
                if (!href || !text) {
                  return null;
                }
                return { href: href, text: text };
              })
              .filter(Boolean);

            return {
              label: label,
              hasSubmenu: submenuLinks.length > 0,
              submenuTitle: submenuTitle,
              submenuLinks: submenuLinks
            };
          }).filter(function (entry) {
            return !!entry.label;
          });
        }
      } catch (err) {
        debugLog('mobileModel:json-parse-failed', { message: err && err.message ? err.message : 'unknown' });
      }
    }

    var model = [];
    root.querySelectorAll('[data-oa-menu-item]').forEach(function (item) {
      var label = getMenuItemLabel(item);
      if (!label) {
        return;
      }

      var topUrl = getMenuItemUrl(item);
      var submenuLinks = extractSubmenuLinks(item);
      var hasSubmenu = submenuLinks.length > 0;

      model.push({
        label: label,
        hasSubmenu: hasSubmenu,
        submenuTitle: label,
        submenuLinks: submenuLinks
      });
    });
    return model;
  }

  function renderMobileRootList(model) {
    var list = document.createElement('ul');
    list.className = 'oa-header-menu__mobile-list oa-header-menu__mobile-list--root';

    model.forEach(function (entry, index) {
      var item = document.createElement('li');
      item.className = 'oa-header-menu__mobile-item';
      var button = document.createElement('button');
      button.type = 'button';
      button.className = 'oa-header-menu__mobile-root-button';
      button.setAttribute('data-oa-mobile-open', String(index));
      button.textContent = entry.label;
      item.appendChild(button);

      list.appendChild(item);
    });

    return list;
  }

  function renderMobileSubmenu(modelEntry, childContent) {
    childContent.innerHTML = '';

    var backButton = document.createElement('button');
    backButton.type = 'button';
    backButton.className = 'oa-header-menu__mobile-back';
    backButton.setAttribute('data-oa-mobile-back', '1');
    backButton.textContent = '\u2039 Back';
    childContent.appendChild(backButton);

    var title = document.createElement('h3');
    title.className = 'oa-header-menu__mobile-title';
    title.textContent = modelEntry.submenuTitle || modelEntry.label;
    childContent.appendChild(title);

    var list = document.createElement('ul');
    list.className = 'oa-header-menu__mobile-list oa-header-menu__mobile-list--submenu';

    modelEntry.submenuLinks.forEach(function (row) {
      var li = document.createElement('li');
      li.className = 'oa-header-menu__mobile-item';
      var link = document.createElement('a');
      link.className = 'oa-header-menu__mobile-submenu-link';
      link.href = row.href;
      link.textContent = row.text;
      li.appendChild(link);
      list.appendChild(li);
    });

    childContent.appendChild(list);
  }

  function createMobileOverlay(root, model) {
    var overlay = document.createElement('div');
    overlay.className = 'oa-header-menu__mobile-overlay';
    overlay.setAttribute('data-oa-mobile-overlay', '1');
    overlay.setAttribute('aria-hidden', 'true');

    var rootScreen = document.createElement('div');
    rootScreen.className = 'oa-header-menu__mobile-screen is-active';
    rootScreen.setAttribute('data-oa-mobile-screen', 'root');
    rootScreen.appendChild(renderMobileRootList(model));
    overlay.appendChild(rootScreen);

    var childScreen = document.createElement('div');
    childScreen.className = 'oa-header-menu__mobile-screen';
    childScreen.setAttribute('data-oa-mobile-screen', 'child');
    var childContent = document.createElement('div');
    childContent.className = 'oa-header-menu__mobile-child-content';
    childScreen.appendChild(childContent);
    overlay.appendChild(childScreen);

    root.appendChild(overlay);

    return {
      node: overlay,
      rootScreen: rootScreen,
      childScreen: childScreen,
      childContent: childContent,
      model: model,
      currentScreen: 'root'
    };
  }

  function resetMobileScreenClasses(screen) {
    screen.classList.remove('is-enter-from-right', 'is-exit-right');
  }

  function setMobileScreen(state, screenName, direction) {
    if (!direction || (screenName === 'root' && direction !== 'backward')) {
      [state.rootScreen, state.childScreen].forEach(function (screen) {
        resetMobileScreenClasses(screen);
      });
      state.rootScreen.classList.add('is-active');
      state.childScreen.classList.remove('is-active');
      state.currentScreen = 'root';
      return;
    }

    if (screenName === 'child' && direction === 'forward') {
      resetMobileScreenClasses(state.childScreen);
      state.rootScreen.classList.add('is-active');
      state.childScreen.classList.add('is-active', 'is-enter-from-right');

      window.requestAnimationFrame(function () {
        state.childScreen.classList.remove('is-enter-from-right');
      });

      state.currentScreen = 'child';
      return;
    }

    if (screenName === 'root' && direction === 'backward') {
      resetMobileScreenClasses(state.childScreen);
      state.rootScreen.classList.add('is-active');
      state.childScreen.classList.add('is-active', 'is-exit-right');

      window.setTimeout(function () {
        state.childScreen.classList.remove('is-active', 'is-exit-right');
      }, 280);

      state.currentScreen = 'root';
    }
  }

  function openMobileOverlay(root, toggle, state) {
    root.classList.add('is-mobile-open');
    state.node.setAttribute('aria-hidden', 'false');
    setMobileScreen(state, 'root');
    toggle.setAttribute('aria-expanded', 'true');
    toggle.setAttribute('aria-label', 'Close menu');
    document.documentElement.classList.add('oa-mobile-menu-open');
    document.body.classList.add('oa-mobile-menu-open');
  }

  function closeMobileOverlay(root, toggle, state) {
    root.classList.remove('is-mobile-open');
    state.node.setAttribute('aria-hidden', 'true');
    setMobileScreen(state, 'root');
    toggle.setAttribute('aria-expanded', 'false');
    toggle.setAttribute('aria-label', 'Open menu');
    document.documentElement.classList.remove('oa-mobile-menu-open');
    document.body.classList.remove('oa-mobile-menu-open');
  }

  function initHeaderMenu(root) {
    if (!root || root.dataset.oaHeaderMenuReady === '1') {
      return;
    }
    root.dataset.oaHeaderMenuReady = '1';

    var toggle = root.querySelector('[data-oa-header-toggle]');
    var items = root.querySelectorAll('[data-oa-menu-item]');
    var mobileModel = buildMobileMenuModel(root);
    var mobileState = createMobileOverlay(root, mobileModel);

    if (toggle) {
      toggle.setAttribute('aria-label', 'Open menu');
    }

    if (toggle) {
      toggle.addEventListener('click', function () {
        if (isMobile()) {
          var shouldOpenMobile = !root.classList.contains('is-mobile-open');
          if (shouldOpenMobile) {
            openMobileOverlay(root, toggle, mobileState);
          } else {
            closeMobileOverlay(root, toggle, mobileState);
          }
          return;
        }

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
      });

      item.addEventListener('mouseenter', function () {
        if (isMobile()) {
          return;
        }
        debugLog('item:mouseenter', { item: describeElement(item) });
        closeAll(root);
        alignMegaPanelToMenuRight(item, root);
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
          if (isMobile()) {
            closeMobileOverlay(root, toggle, mobileState);
          } else {
            root.classList.remove('is-mobile-open');
            toggle.setAttribute('aria-expanded', 'false');
          }
          toggle.focus();
        }
      }
    });

    mobileState.node.addEventListener('click', function (event) {
      var backBtn = event.target.closest('[data-oa-mobile-back]');
      if (backBtn) {
        setMobileScreen(mobileState, 'root', 'backward');
        return;
      }

      var openBtn = event.target.closest('[data-oa-mobile-open]');
      if (!openBtn) {
        return;
      }

      var index = parseInt(openBtn.getAttribute('data-oa-mobile-open'), 10);
      var entry = mobileState.model[index];
      if (!entry) {
        return;
      }

      renderMobileSubmenu(entry, mobileState.childContent);
      setMobileScreen(mobileState, 'child', 'forward');
    });

    document.addEventListener('click', function (event) {
      if (!root.contains(event.target)) {
        debugLog('document:outside-click', {
          root: describeElement(root),
          target: describeElement(event.target)
        });
        closeAll(root);
        if (toggle && isMobile()) {
          closeMobileOverlay(root, toggle, mobileState);
        }
      }
    });

    window.addEventListener('resize', function () {
      debugLog('window:resize', { mobile: isMobile() });
      if (!isMobile()) {
        root.classList.remove('is-mobile-open');
        if (toggle) {
          toggle.setAttribute('aria-expanded', 'false');
        }
        if (mobileState && mobileState.node) {
          mobileState.node.setAttribute('aria-hidden', 'true');
        }
        document.documentElement.classList.remove('oa-mobile-menu-open');
        document.body.classList.remove('oa-mobile-menu-open');
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
