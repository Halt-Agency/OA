(function () {
  var LOG_PREFIX = '[oa-adv-tabs-solutions]';

  function log(msg, data) {
    if (typeof console !== 'undefined' && console.log) {
      console.log(LOG_PREFIX + ' ' + msg, data !== undefined ? data : '');
    }
  }

  function extractMaybeString(value) {
    if (typeof value === 'string') {
      return value;
    }
    if (!value || typeof value !== 'object') {
      return '';
    }
    if (typeof value.url === 'string') {
      return value.url;
    }
    if (value.url && typeof value.url === 'object' && typeof value.url.url === 'string') {
      return value.url.url;
    }
    if (typeof value.value === 'string') {
      return value.value;
    }
    if (value.value && typeof value.value === 'object') {
      var nested = extractMaybeString(value.value);
      if (nested) {
        return nested;
      }
    }
    return '';
  }

  function extractMaybeText(value) {
    if (typeof value === 'string') {
      return value;
    }
    if (!value || typeof value !== 'object') {
      return '';
    }
    if (typeof value.value === 'string') {
      return value.value;
    }
    if (value.desktop && typeof value.desktop === 'object' && typeof value.desktop.value === 'string') {
      return value.desktop.value;
    }
    return '';
  }

  function buildButton(button) {
    if (!button || typeof button !== 'object') {
      return null;
    }

    var text = (
      extractMaybeText(button.button_1_text) ||
      extractMaybeText(button.button_2_text) ||
      extractMaybeText(button.text) ||
      ''
    ).trim();

    var link = button.button_1_link || button.button_2_link || button.link || '';
    var href = '';
    var target = '';

    if (typeof link === 'string') {
      href = link;
    } else if (link && typeof link === 'object') {
      href = extractMaybeString(link);
      target = extractMaybeText(link.target);
      if (!text && typeof link.title === 'string') {
        text = link.title;
      }
    }

    if (!href || !text) {
      return null;
    }

    var anchor = document.createElement('a');
    anchor.className = 'oa-adv-tabs__button';
    anchor.href = href;
    if (target) {
      anchor.target = target;
    }
    anchor.textContent = text;
    return anchor;
  }

  function renderTabs(container) {
    var pageContent =
      window.dtACFData &&
      window.dtACFData.page_content
        ? window.dtACFData.page_content
        : {};

    var services = Array.isArray(pageContent.services) ? pageContent.services : [];
    if (services.length === 0) {
      return;
    }

    log('renderTabs: start', { servicesCount: services.length });
    container.classList.add('oa-adv-tabs', 'oa-adv-tabs--solutions');
    container.innerHTML = '';

    var listWrap = document.createElement('div');
    listWrap.className = 'oa-adv-tabs__list-wrap';

    var list = document.createElement('div');
    list.className = 'oa-adv-tabs__list';
    listWrap.appendChild(list);

    var panelsWrap = document.createElement('div');
    panelsWrap.className = 'oa-adv-tabs__panels';

    var tabs = [];
    var panels = [];
    var mobilePrevButtons = [];
    var mobileNextButtons = [];

    services.forEach(function (service, index) {
      var title = service && service.service_name ? String(service.service_name).trim() : 'Service ' + (index + 1);
      var copy = service && service.service_copy ? String(service.service_copy) : '';
      var featuresHeading = service && service.service_features_heading ? String(service.service_features_heading).trim() : '';
      var features = service && Array.isArray(service.service_features) ? service.service_features : [];
      var serviceButtons = service && service.service_buttons ? service.service_buttons : null;

      var tab = document.createElement('button');
      tab.className = 'oa-adv-tabs__tab' + (index === 0 ? ' is-active' : '');
      tab.type = 'button';
      tab.dataset.tab = index;
      tab.textContent = title;
      list.appendChild(tab);
      tabs.push(tab);

      var panel = document.createElement('div');
      panel.className = 'oa-adv-tabs__panel' + (index === 0 ? ' is-active' : '');
      panel.dataset.tab = index;

      var panelNav = document.createElement('div');
      panelNav.className = 'oa-adv-tabs__panel-nav';

      var panelPrev = document.createElement('button');
      panelPrev.className = 'oa-adv-tabs__nav oa-adv-tabs__nav--prev';
      panelPrev.type = 'button';
      panelPrev.setAttribute('aria-label', 'Previous tabs');
      panelPrev.innerHTML = '&#8249;';
      panelNav.appendChild(panelPrev);
      mobilePrevButtons.push(panelPrev);

      var panelNext = document.createElement('button');
      panelNext.className = 'oa-adv-tabs__nav oa-adv-tabs__nav--next';
      panelNext.type = 'button';
      panelNext.setAttribute('aria-label', 'Next tabs');
      panelNext.innerHTML = '&#8250;';
      panelNav.appendChild(panelNext);
      mobileNextButtons.push(panelNext);

      panel.appendChild(panelNav);

      var panelBody = document.createElement('div');
      panelBody.className = 'oa-adv-tabs__panel-body';

      var heading = document.createElement('h3');
      heading.textContent = title;
      panelBody.appendChild(heading);

      if (copy) {
        var contentEl = document.createElement('div');
        contentEl.className = 'oa-adv-tabs__content';
        contentEl.innerHTML = copy;
        panelBody.appendChild(contentEl);
      }

      if (featuresHeading) {
        var featuresHeadingEl = document.createElement('h4');
        featuresHeadingEl.className = 'oa-adv-tabs__section-heading';
        featuresHeadingEl.textContent = featuresHeading;
        panelBody.appendChild(featuresHeadingEl);
      }

      if (features.length) {
        var featuresList = document.createElement('ul');
        featuresList.className = 'oa-adv-tabs__roles';

        features.forEach(function (featureItem) {
          var featureText = '';
          if (typeof featureItem === 'string') {
            featureText = featureItem.trim();
          } else if (featureItem && typeof featureItem === 'object') {
            featureText = String(featureItem.feature || '').trim();
          }

          if (!featureText) {
            return;
          }

          var featureEl = document.createElement('li');
          featureEl.className = 'oa-adv-tabs__role';
          featureEl.textContent = featureText;
          featuresList.appendChild(featureEl);
        });

        if (featuresList.children.length > 0) {
          panelBody.appendChild(featuresList);
        }
      }

      panel.appendChild(panelBody);

      var buttonsWrap = document.createElement('div');
      buttonsWrap.className = 'oa-adv-tabs__panel-actions';

      var buttons = document.createElement('div');
      buttons.className = 'oa-adv-tabs__buttons';

      if (serviceButtons) {
        var button1 = buildButton(serviceButtons.button_1);
        var button2 = buildButton(serviceButtons.button_2);

        if (button1) {
          buttons.appendChild(button1);
        }
        if (button2) {
          buttons.appendChild(button2);
        }
      }

      if (buttons.children.length > 0) {
        buttonsWrap.appendChild(buttons);
        panel.appendChild(buttonsWrap);
      }

      panelsWrap.appendChild(panel);
      panels.push(panel);
    });

    container.appendChild(listWrap);
    container.appendChild(panelsWrap);

    var activateTab = function (tab) {
      if (!tab) {
        return;
      }
      var idx = tabs.indexOf(tab);
      if (idx < 0) {
        return;
      }

      tabs.forEach(function (t) {
        t.classList.remove('is-active');
      });
      panels.forEach(function (panel) {
        panel.classList.remove('is-active');
      });

      tab.classList.add('is-active');
      if (panels[idx]) {
        panels[idx].classList.add('is-active');
      }
    };

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        activateTab(tab);
      });
    });

    var goPrevious = function () {
      var current = container.querySelector('.oa-adv-tabs__tab.is-active') || tabs[0];
      var idx = tabs.indexOf(current);
      activateTab(tabs[Math.max(0, idx - 1)]);
    };

    var goNext = function () {
      var current = container.querySelector('.oa-adv-tabs__tab.is-active') || tabs[0];
      var idx = tabs.indexOf(current);
      activateTab(tabs[Math.min(tabs.length - 1, idx + 1)]);
    };

    mobilePrevButtons.forEach(function (button) {
      button.addEventListener('click', goPrevious);
    });
    mobileNextButtons.forEach(function (button) {
      button.addEventListener('click', goNext);
    });

    log('renderTabs: done', { tabCount: tabs.length });

    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          log('viewport', {
            isIntersecting: entry.isIntersecting,
            intersectionRatio: entry.intersectionRatio,
            boundingRect: {
              top: entry.boundingClientRect.top,
              height: entry.boundingClientRect.height
            }
          });
        });
      },
      { threshold: [0, 0.25, 0.5, 0.75, 1] }
    );
    io.observe(container);

    var recoverScheduled = false;
    function scheduleRecover() {
      if (recoverScheduled) return;
      recoverScheduled = true;
      requestAnimationFrame(function () {
        recoverScheduled = false;
        var target = document.body.contains(container)
          ? container
          : document.querySelector('.adv-tabs-solutions');
        if (!target) return;
        var hasContent = target.querySelector('.oa-adv-tabs__list-wrap');
        if (!hasContent) {
          log('recover: container emptied by Divi, re-rendering');
          io.disconnect();
          mo.disconnect();
          renderTabs(target);
        }
      });
    }

    var mo = new MutationObserver(function (mutations) {
      mutations.forEach(function (m) {
        if (m.type === 'childList' && m.removedNodes.length) {
          var removed = Array.prototype.slice.call(m.removedNodes);
          var oursRemoved = removed.some(function (n) {
            return n === container || (n.nodeType === 1 && container.contains(n));
          });
          if (oursRemoved || (m.target === container && m.removedNodes.length)) {
            scheduleRecover();
          }
        }
      });
    });
    mo.observe(container.parentNode || document.body, {
      childList: true,
      subtree: true
    });

    var lastScrollLog = 0;
    window.addEventListener('scroll', function () {
      if (!document.body.contains(container)) {
        log('scroll: container NO LONGER IN DOM');
        return;
      }
      var rect = container.getBoundingClientRect();
      var inView = rect.top < window.innerHeight && rect.bottom > 0;
      if (inView) {
        var cs = window.getComputedStyle(container);
        if (cs.display === 'none' || cs.visibility === 'hidden' || parseFloat(cs.opacity) === 0) {
          var now = Date.now();
          if (now - lastScrollLog > 500) {
            lastScrollLog = now;
            var parentChain = [];
            var p = container.parentElement;
            while (p && parentChain.length < 6) {
              var ps = window.getComputedStyle(p);
              parentChain.push({
                tag: p.tagName,
                class: p.className ? p.className.slice(0, 50) : '',
                display: ps.display,
                visibility: ps.visibility,
                opacity: ps.opacity
              });
              p = p.parentElement;
            }
            log('scroll: container IN VIEW but hidden', {
              display: cs.display,
              visibility: cs.visibility,
              opacity: cs.opacity,
              parentChain: parentChain
            });
          }
        }
      }
    }, { passive: true });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var containers = document.querySelectorAll('.adv-tabs-solutions');
    log('DOMContentLoaded: found containers', { count: containers.length });
    containers.forEach(renderTabs);
  });
})();
