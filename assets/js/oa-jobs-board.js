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

  function asArray(value) {
    if (Array.isArray(value)) {
      return value;
    }
    return value ? [value] : [];
  }

  function valueMapToOptions(map) {
    if (!map || typeof map !== 'object') {
      return [];
    }

    return Object.keys(map).map(function (key) {
      return {
        value: String(key),
        label: decodeEntities(String(map[key]))
      };
    });
  }

  function buildCard(item) {
    var title = decodeEntities(item.title || 'Untitled Job');
    var reference = item.reference ? '(' + decodeEntities(item.reference) + ')' : '';
    var location = decodeEntities(item.location || '');
    var hours = decodeEntities(item.hours || '');
    var contractType = decodeEntities(item.contract_type || '');
    var salary = decodeEntities(item.salary_hour || item.salary_annum || '');
    var excerpt = decodeEntities(item.excerpt || '');
    var permalink = item.permalink || '#';

    var metaTop = [reference, location].filter(Boolean).join('<br>');
    var metaBottom = [hours, contractType, salary].filter(Boolean).join(' | ');

    return '<article class="oa-jobs-board__card">'
      + '<a class="oa-jobs-board__card-link" href="' + escapeHtml(permalink) + '">'
      + '<h3 class="oa-jobs-board__card-title">' + escapeHtml(title) + '</h3>'
      + (metaTop ? '<p class="oa-jobs-board__card-meta">' + metaTop + '</p>' : '')
      + (metaBottom ? '<p class="oa-jobs-board__card-meta">' + escapeHtml(metaBottom) + '</p>' : '')
      + (excerpt ? '<p class="oa-jobs-board__card-copy">' + escapeHtml(excerpt) + '</p>' : '')
      + '</a>'
      + '</article>';
  }

  function buildCheckboxGroup(label, name, options, selectedValues) {
    return '<fieldset class="oa-jobs-board__fieldset">'
      + '<legend class="oa-jobs-board__legend">' + escapeHtml(label) + '</legend>'
      + options.map(function (opt) {
        var checked = selectedValues.indexOf(opt.value) > -1 ? ' checked' : '';
        return '<label class="oa-jobs-board__check">'
          + '<input type="checkbox" name="' + escapeHtml(name) + '" value="' + escapeHtml(opt.value) + '"' + checked + '>'
          + '<span>' + escapeHtml(opt.label) + '</span>'
          + '</label>';
      }).join('')
      + '</fieldset>';
  }

  function initBoard(container) {
    var pageContent = (window.dtACFData && window.dtACFData.page_content) ? window.dtACFData.page_content : {};
    var settings = {
      filtersHeading: decodeEntities(pageContent.filters_heading || 'Filter search'),
      keywordPlaceholder: decodeEntities(pageContent.keyword_placeholder || 'Job title or keyword'),
      cityPlaceholder: decodeEntities(pageContent.location_placeholder || 'Select city'),
      sectorLabel: decodeEntities(pageContent.sector_label || 'Sector'),
      contractTypeLabel: decodeEntities(pageContent.contract_type_label || 'Contract type'),
      searchButtonText: decodeEntities(pageContent.search_button_text || 'Search jobs'),
      emptyResultsText: decodeEntities(pageContent.empty_results_text || 'No jobs found.'),
      paginationPrevText: decodeEntities(pageContent.pagination_prev_text || 'Previous'),
      paginationNextText: decodeEntities(pageContent.pagination_next_text || 'Next')
    };

    var state = {
      keyword: '',
      city: '',
      sectors: [],
      contractTypes: [],
      page: 1,
      perPage: 12,
      options: {
        cities: [],
        sectors: [],
        contractTypes: []
      }
    };

    container.classList.add('oa-jobs-board');
    container.innerHTML = ''
      + '<div class="oa-jobs-board__layout">'
      + '  <aside class="oa-jobs-board__sidebar">'
      + '    <form class="oa-jobs-board__form">'
      + '      <h2 class="oa-jobs-board__filter-heading"></h2>'
      + '      <input class="oa-jobs-board__input" type="text" name="keyword" autocomplete="off">'
      + '      <select class="oa-jobs-board__input oa-jobs-board__select" name="city"></select>'
      + '      <div class="oa-jobs-board__group-sector"></div>'
      + '      <div class="oa-jobs-board__group-contract"></div>'
      + '      <button class="oa-jobs-board__search" type="submit"></button>'
      + '    </form>'
      + '  </aside>'
      + '  <div class="oa-jobs-board__content">'
      + '    <div class="oa-jobs-board__cards" aria-live="polite"></div>'
      + '    <nav class="oa-jobs-board__pagination" aria-label="Jobs pagination"></nav>'
      + '  </div>'
      + '</div>';

    var form = container.querySelector('.oa-jobs-board__form');
    var heading = container.querySelector('.oa-jobs-board__filter-heading');
    var keywordInput = container.querySelector('input[name="keyword"]');
    var citySelect = container.querySelector('select[name="city"]');
    var sectorGroup = container.querySelector('.oa-jobs-board__group-sector');
    var contractGroup = container.querySelector('.oa-jobs-board__group-contract');
    var cards = container.querySelector('.oa-jobs-board__cards');
    var pagination = container.querySelector('.oa-jobs-board__pagination');
    var searchButton = container.querySelector('.oa-jobs-board__search');

    heading.textContent = settings.filtersHeading;
    keywordInput.placeholder = settings.keywordPlaceholder;
    citySelect.innerHTML = '<option value="">' + escapeHtml(settings.cityPlaceholder) + '</option>';
    searchButton.textContent = settings.searchButtonText;

    function renderFilterGroups() {
      citySelect.innerHTML = '<option value="">' + escapeHtml(settings.cityPlaceholder) + '</option>'
        + state.options.cities.map(function (city) {
          var selected = city === state.city ? ' selected' : '';
          return '<option value="' + escapeHtml(city) + '"' + selected + '>' + escapeHtml(city) + '</option>';
        }).join('');

      sectorGroup.innerHTML = buildCheckboxGroup(settings.sectorLabel, 'sectors', state.options.sectors, state.sectors);
      contractGroup.innerHTML = buildCheckboxGroup(settings.contractTypeLabel, 'contract_types', state.options.contractTypes, state.contractTypes);
    }

    function renderCards(items) {
      if (!items.length) {
        cards.innerHTML = '<p class="oa-jobs-board__empty">' + escapeHtml(settings.emptyResultsText) + '</p>';
        return;
      }

      cards.innerHTML = items.map(buildCard).join('');
    }

    function renderPagination(meta) {
      if (!meta || meta.total_pages <= 1) {
        pagination.innerHTML = '';
        return;
      }

      var html = '';
      html += '<button type="button" class="oa-jobs-board__page-btn" data-page="' + (meta.page - 1) + '"' + (meta.page <= 1 ? ' disabled' : '') + '>' + escapeHtml(settings.paginationPrevText) + '</button>';

      for (var i = 1; i <= meta.total_pages; i += 1) {
        var active = i === meta.page ? ' aria-current="page"' : '';
        html += '<button type="button" class="oa-jobs-board__page-number" data-page="' + i + '"' + active + '>' + (i < 10 ? '0' + i : i) + '</button>';
      }

      html += '<button type="button" class="oa-jobs-board__page-btn" data-page="' + (meta.page + 1) + '"' + (meta.page >= meta.total_pages ? ' disabled' : '') + '>' + escapeHtml(settings.paginationNextText) + '</button>';

      pagination.innerHTML = html;
    }

    function readCheckboxValues(name) {
      return Array.prototype.slice.call(form.querySelectorAll('input[name="' + name + '"]:checked')).map(function (input) {
        return input.value;
      });
    }

    function fetchJobs() {
      cards.classList.add('is-loading');

      var payload = new URLSearchParams();
      payload.set('action', 'oa_filter_jobs');
      payload.set('keyword', state.keyword);
      payload.set('city', state.city);
      payload.set('sectors', state.sectors.join(','));
      payload.set('contract_types', state.contractTypes.join(','));
      payload.set('page', String(state.page));
      payload.set('per_page', String(state.perPage));

      return fetch(window.dtAjaxUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: payload.toString()
      })
        .then(function (response) { return response.json(); })
        .then(function (json) {
          if (!json || !json.success || !json.data) {
            throw new Error('Invalid jobs response');
          }

          var data = json.data;

          if (!state.options.sectors.length && data.available_filters) {
            state.options.cities = asArray(data.available_filters.cities);
            state.options.sectors = valueMapToOptions(data.available_filters.sectors);
            state.options.contractTypes = valueMapToOptions(data.available_filters.contract_types);
            renderFilterGroups();
          }

          renderCards(asArray(data.items));
          renderPagination(data.pagination || {});
        })
        .catch(function () {
          cards.innerHTML = '<p class="oa-jobs-board__empty">Failed to load jobs.</p>';
          pagination.innerHTML = '';
        })
        .finally(function () {
          cards.classList.remove('is-loading');
        });
    }

    form.addEventListener('submit', function (event) {
      event.preventDefault();
      state.keyword = keywordInput.value.trim();
      state.city = citySelect.value.trim();
      state.sectors = readCheckboxValues('sectors');
      state.contractTypes = readCheckboxValues('contract_types');
      state.page = 1;
      fetchJobs();
    });

    pagination.addEventListener('click', function (event) {
      var button = event.target.closest('button[data-page]');
      if (!button || button.disabled) {
        return;
      }
      state.page = parseInt(button.getAttribute('data-page'), 10) || 1;
      fetchJobs();
    });

    fetchJobs();
  }

  document.addEventListener('DOMContentLoaded', function () {
    var boards = document.querySelectorAll('[data-oa-jobs-board], .oa-jobs-board');
    boards.forEach(initBoard);
  });
})();
