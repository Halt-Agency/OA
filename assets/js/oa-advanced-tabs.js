(function () {
  function log(message, data) {
    if (typeof console === 'undefined' || !console.log) {
      return;
    }
    console.log('[oa-adv-tabs] ' + message, data || {});
  }

  function setPanelsHeight(root, panelsWrap, panels) {
    if (!panelsWrap || !panels || panels.length === 0) {
      return;
    }
    if (window.innerWidth > 980) {
      panelsWrap.style.minHeight = '';
      return;
    }

    panelsWrap.style.minHeight = '';
    var maxHeight = 0;
    var activePanel = root.querySelector('.oa-adv-tabs__panel.is-active');
    panels.forEach(function (panel) {
      panel.classList.add('is-active');
      var height = panel.scrollHeight;
      maxHeight = Math.max(maxHeight, height);
      panel.classList.remove('is-active');
    });

    if (activePanel) {
      activePanel.classList.add('is-active');
    }

    if (maxHeight > 0) {
      panelsWrap.style.minHeight = maxHeight + 'px';
    }
  }

  function setLabelWidth(root, label, tabs) {
    if (!label || tabs.length === 0) {
      return;
    }
    var maxWidth = 0;
    tabs.forEach(function (tab) {
      maxWidth = Math.max(maxWidth, tab.scrollWidth);
    });

    if (maxWidth > 0) {
      label.style.minWidth = maxWidth + 'px';
      label.style.maxWidth = maxWidth + 'px';
    }
  }

  function updateLabel(label, tab) {
    if (label && tab) {
      label.textContent = tab.textContent || '';
    }
  }

  function buildButton(button, fallbackText) {
    if (!button) {
      log('buildButton: no button payload');
      return null;
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
      if (value.desktop && typeof value.desktop === 'object') {
        var desktopValue = extractMaybeString(value.desktop.value || value.desktop);
        if (desktopValue) {
          return desktopValue;
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
    }

    if (!href && typeof button.url === 'string') {
      href = button.url;
    }

    if (!href) {
      log('buildButton: missing href', { button: button });
      return null;
    }

    if (text === '' && link && typeof link === 'object') {
      text = link.title || '';
    }

    if (text === '' && typeof fallbackText === 'string') {
      text = fallbackText;
    }

    if (text === '') {
      log('buildButton: missing text', { button: button, href: href });
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
    log('renderTabs: start', {
      containerClass: container.className || '',
      containerId: container.id || ''
    });

    var pageContent =
      window.dtACFData &&
      window.dtACFData.page_content
        ? window.dtACFData.page_content
        : {};

    var sectors =
      pageContent &&
      pageContent.sectors
        ? pageContent.sectors
        : [];
    var sharedButtons =
      pageContent &&
      pageContent.sector_buttons
        ? pageContent.sector_buttons
        : (pageContent && pageContent.industry_buttons ? pageContent.industry_buttons : null);

    log('renderTabs: data snapshot', {
      hasDtACFData: !!window.dtACFData,
      pageContentKeys: pageContent && typeof pageContent === 'object' ? Object.keys(pageContent) : [],
      sectorsIsArray: Array.isArray(sectors),
      sectorsLength: Array.isArray(sectors) ? sectors.length : 0,
      hasSharedButtons: !!sharedButtons
    });

    if (!Array.isArray(sectors) || sectors.length === 0) {
      log('renderTabs: no sectors found, aborting');
      return;
    }

    container.classList.add('oa-adv-tabs');
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

    sectors.forEach(function (sector, index) {
      var title = sector && sector.sector_name ? sector.sector_name : 'Tab ' + (index + 1);
      var content = sector && sector.sector_copy ? sector.sector_copy : '';
      var roles = sector && Array.isArray(sector.industry_roles) ? sector.industry_roles : [];

      log('renderTabs: sector', {
        index: index,
        title: title,
        hasContent: !!content,
        rolesCount: roles.length,
        hasRowButtons: !!(sector && sector.sector_buttons)
      });

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

      if (content) {
        var contentEl = document.createElement('div');
        contentEl.className = 'oa-adv-tabs__content';
        contentEl.innerHTML = content;
        panelBody.appendChild(contentEl);
      }

      if (roles.length) {
        var rolesList = document.createElement('ul');
        rolesList.className = 'oa-adv-tabs__roles';

        roles.forEach(function (roleItem) {
          var roleText = '';
          if (typeof roleItem === 'string') {
            roleText = roleItem.trim();
          } else if (roleItem && typeof roleItem === 'object') {
            roleText = String(roleItem.role || '').trim();
          }

          if (!roleText) {
            return;
          }

          var roleEl = document.createElement('li');
          roleEl.className = 'oa-adv-tabs__role';
          roleEl.textContent = roleText;
          rolesList.appendChild(roleEl);
        });

        if (rolesList.children.length > 0) {
          panelBody.appendChild(rolesList);
        }
      }

      panel.appendChild(panelBody);

      var buttonsWrap = document.createElement('div');
      buttonsWrap.className = 'oa-adv-tabs__panel-actions';

      var buttons = document.createElement('div');
      buttons.className = 'oa-adv-tabs__buttons';

      var buttonSource = (sector && sector.sector_buttons) ? sector.sector_buttons : sharedButtons;
      if (buttonSource) {
        var button1 = buildButton(buttonSource.button_1, 'Search Opportunities');
        var button2 = buildButton(buttonSource.button_2, 'Send Us A Brief');

        if (button1) {
          buttons.appendChild(button1);
        }
        if (button2) {
          buttons.appendChild(button2);
        }
      } else {
        log('renderTabs: no button source for sector', { index: index, title: title });
      }

      if (buttons.children.length > 0) {
        buttonsWrap.appendChild(buttons);
        panel.appendChild(buttonsWrap);
      } else {
        log('renderTabs: no buttons rendered for sector', { index: index, title: title });
      }

      panelsWrap.appendChild(panel);
      panels.push(panel);
    });

    container.appendChild(listWrap);
    container.appendChild(panelsWrap);
    log('renderTabs: rendered complete', { tabs: tabs.length, panels: panels.length });

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
      setPanelsHeight(container, panelsWrap, panels);
    };

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        activateTab(tab);
      });
    });

    var goPrevious = function () {
      var current = container.querySelector('.oa-adv-tabs__tab.is-active') || tabs[0];
      var idx = tabs.indexOf(current);
      var nextTab = tabs[Math.max(0, idx - 1)];
      activateTab(nextTab);
    };

    var goNext = function () {
      var current = container.querySelector('.oa-adv-tabs__tab.is-active') || tabs[0];
      var idx = tabs.indexOf(current);
      var nextTab = tabs[Math.min(tabs.length - 1, idx + 1)];
      activateTab(nextTab);
    };

    mobilePrevButtons.forEach(function (button) {
      button.addEventListener('click', goPrevious);
    });

    mobileNextButtons.forEach(function (button) {
      button.addEventListener('click', goNext);
    });

    setPanelsHeight(container, panelsWrap, panels);

    window.addEventListener('resize', function () {
      setPanelsHeight(container, panelsWrap, panels);
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var containers = document.querySelectorAll('.adv-tabs');
    log('DOMContentLoaded: found .adv-tabs containers', { count: containers.length });
    containers.forEach(renderTabs);
  });
})();
