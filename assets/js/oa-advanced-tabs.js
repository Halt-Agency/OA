(function () {
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

  function buildButton(button) {
    if (!button) {
      return null;
    }

    var text = (button.button_1_text || button.button_2_text || '').trim();
    var link = button.button_1_link || button.button_2_link;
    if (!link || !link.url) {
      return null;
    }

    if (text === '') {
      text = link.title || '';
    }

    if (text === '') {
      return null;
    }

    var anchor = document.createElement('a');
    anchor.className = 'oa-adv-tabs__button';
    anchor.href = link.url;
    if (link.target) {
      anchor.target = link.target;
    }
    anchor.textContent = text;
    return anchor;
  }

  function renderTabs(container) {
    var sectors =
      window.dtACFData &&
      window.dtACFData.page_content &&
      window.dtACFData.page_content.sectors
        ? window.dtACFData.page_content.sectors
        : [];

    if (!Array.isArray(sectors) || sectors.length === 0) {
      return;
    }

    container.classList.add('oa-adv-tabs');
    container.innerHTML = '';

    var listWrap = document.createElement('div');
    listWrap.className = 'oa-adv-tabs__list-wrap';

    var prev = document.createElement('button');
    prev.className = 'oa-adv-tabs__nav oa-adv-tabs__nav--prev';
    prev.type = 'button';
    prev.setAttribute('aria-label', 'Previous tabs');
    prev.innerHTML = '&#8249;';

    var label = document.createElement('div');
    label.className = 'oa-adv-tabs__nav-label';

    var list = document.createElement('div');
    list.className = 'oa-adv-tabs__list';

    var next = document.createElement('button');
    next.className = 'oa-adv-tabs__nav oa-adv-tabs__nav--next';
    next.type = 'button';
    next.setAttribute('aria-label', 'Next tabs');
    next.innerHTML = '&#8250;';

    listWrap.appendChild(prev);
    listWrap.appendChild(label);
    listWrap.appendChild(list);
    listWrap.appendChild(next);

    var panelsWrap = document.createElement('div');
    panelsWrap.className = 'oa-adv-tabs__panels';

    var tabs = [];
    var panels = [];

    sectors.forEach(function (sector, index) {
      var title = sector && sector.sector_name ? sector.sector_name : 'Tab ' + (index + 1);
      var content = sector && sector.sector_copy ? sector.sector_copy : '';

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

      panel.appendChild(panelBody);

      var buttonsWrap = document.createElement('div');
      buttonsWrap.className = 'oa-adv-tabs__panel-actions';

      var buttons = document.createElement('div');
      buttons.className = 'oa-adv-tabs__buttons';

      if (sector && sector.sector_buttons) {
        var button1 = buildButton(sector.sector_buttons.button_1);
        var button2 = buildButton(sector.sector_buttons.button_2);

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
      updateLabel(label, tab);
      setPanelsHeight(container, panelsWrap, panels);
    };

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        activateTab(tab);
      });
    });

    prev.addEventListener('click', function () {
      var current = container.querySelector('.oa-adv-tabs__tab.is-active') || tabs[0];
      var idx = tabs.indexOf(current);
      var nextTab = tabs[Math.max(0, idx - 1)];
      activateTab(nextTab);
    });

    next.addEventListener('click', function () {
      var current = container.querySelector('.oa-adv-tabs__tab.is-active') || tabs[0];
      var idx = tabs.indexOf(current);
      var nextTab = tabs[Math.min(tabs.length - 1, idx + 1)];
      activateTab(nextTab);
    });

    updateLabel(label, container.querySelector('.oa-adv-tabs__tab.is-active') || tabs[0]);
    setPanelsHeight(container, panelsWrap, panels);
    setLabelWidth(container, label, tabs);

    window.addEventListener('resize', function () {
      setPanelsHeight(container, panelsWrap, panels);
      setLabelWidth(container, label, tabs);
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var containers = document.querySelectorAll('.adv-tabs');
    containers.forEach(renderTabs);
  });
})();
