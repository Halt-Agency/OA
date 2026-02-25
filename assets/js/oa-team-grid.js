(function () {
  'use strict';

  function createOption(value, label, selected) {
    var option = document.createElement('option');
    option.value = value;
    option.textContent = decodeEntities(label);
    if (selected) {
      option.selected = true;
    }
    return option;
  }

  function decodeEntities(value) {
    var text = value == null ? '' : String(value);
    if (text.indexOf('&') === -1) {
      return text;
    }
    var parser = document.createElement('textarea');
    parser.innerHTML = text;
    return parser.value;
  }

  function formatPageNumber(page) {
    return page < 10 ? '0' + page : String(page);
  }

  function buildTeamGrid(container) {
    var source = window.oaTeamDirectory;
    if (!source || !Array.isArray(source.members) || source.members.length === 0) {
      return;
    }

    var members = source.members.slice();
    var filters = source.filters || {};
    var settings = source.settings || {};
    var cardsPerPage = Number(settings.cards_per_page || 16);

    var state = {
      query: '',
      location: '',
      sector: '',
      page: 1,
      filtered: members.slice(),
    };

    container.innerHTML = '';
    container.classList.add('oa-team-grid');

    var panel = document.createElement('section');
    panel.className = 'oa-team-grid__panel';

    var heading = document.createElement('h2');
    heading.className = 'oa-team-grid__heading';
    heading.textContent = settings.heading || 'Search the team';
    panel.appendChild(heading);

    var form = document.createElement('div');
    form.className = 'oa-team-grid__filters';

    var queryInput = document.createElement('input');
    queryInput.className = 'oa-team-grid__input';
    queryInput.type = 'text';
    queryInput.placeholder = settings.placeholder_name || 'Name/Job Title';
    form.appendChild(queryInput);

    var locationSelect = document.createElement('select');
    locationSelect.className = 'oa-team-grid__select';
    locationSelect.appendChild(
      createOption('', settings.placeholder_location || 'Location', true)
    );
    (filters.locations || []).forEach(function (term) {
      locationSelect.appendChild(createOption(term.slug, term.label, false));
    });
    form.appendChild(locationSelect);

    var sectorSelect = document.createElement('select');
    sectorSelect.className = 'oa-team-grid__select';
    sectorSelect.appendChild(
      createOption('', settings.placeholder_sector || 'Select Sector', true)
    );
    (filters.sectors || []).forEach(function (term) {
      sectorSelect.appendChild(createOption(term.slug, term.label, false));
    });
    form.appendChild(sectorSelect);

    var searchBtn = document.createElement('button');
    searchBtn.className = 'oa-team-grid__search-btn';
    searchBtn.type = 'button';
    searchBtn.textContent = settings.search_button_text || 'Search';
    form.appendChild(searchBtn);

    panel.appendChild(form);

    var grid = document.createElement('div');
    grid.className = 'oa-team-grid__cards';
    panel.appendChild(grid);

    var emptyState = document.createElement('p');
    emptyState.className = 'oa-team-grid__empty';
    emptyState.textContent = settings.empty_state_text || 'No team members found.';
    panel.appendChild(emptyState);

    var pagination = document.createElement('div');
    pagination.className = 'oa-team-grid__pagination';

    var prevBtn = document.createElement('button');
    prevBtn.className = 'oa-team-grid__page-btn';
    prevBtn.type = 'button';
    prevBtn.textContent = settings.pagination_prev_text || 'Previous';
    pagination.appendChild(prevBtn);

    var pagesWrap = document.createElement('div');
    pagesWrap.className = 'oa-team-grid__pages';
    pagination.appendChild(pagesWrap);

    var nextBtn = document.createElement('button');
    nextBtn.className = 'oa-team-grid__page-btn';
    nextBtn.type = 'button';
    nextBtn.textContent = settings.pagination_next_text || 'Next';
    pagination.appendChild(nextBtn);

    panel.appendChild(pagination);
    container.appendChild(panel);

    function getTotalPages() {
      return Math.max(1, Math.ceil(state.filtered.length / cardsPerPage));
    }

    function applyFilters() {
      var query = state.query.trim().toLowerCase();
      var location = state.location;
      var sector = state.sector;

      state.filtered = members.filter(function (member) {
        var nameMatch = true;
        if (query !== '') {
          var haystack = (member.name + ' ' + (member.job_title || '')).toLowerCase();
          nameMatch = haystack.indexOf(query) !== -1;
        }

        var locationMatch = true;
        if (location !== '') {
          var memberLocations = Array.isArray(member.locations) ? member.locations : [];
          locationMatch = memberLocations.some(function (term) {
            return term.slug === location;
          });
        }

        var sectorMatch = true;
        if (sector !== '') {
          var memberSectors = Array.isArray(member.sectors) ? member.sectors : [];
          sectorMatch = memberSectors.some(function (term) {
            return term.slug === sector;
          });
        }

        return nameMatch && locationMatch && sectorMatch;
      });

      state.page = 1;
    }

    function renderCards() {
      grid.innerHTML = '';

      if (state.filtered.length === 0) {
        emptyState.style.display = 'block';
        grid.style.minHeight = '0px';
        return;
      }

      emptyState.style.display = 'none';
      var start = (state.page - 1) * cardsPerPage;
      var end = start + cardsPerPage;
      var visible = state.filtered.slice(start, end);

      visible.forEach(function (member) {
        var card = document.createElement('article');
        card.className = 'oa-team-grid__card';

        var link = document.createElement('a');
        link.className = 'oa-team-grid__card-link';
        link.href = member.link || '#';

        var media = document.createElement('div');
        media.className = 'oa-team-grid__media';
        if (member.image) {
          var img = document.createElement('img');
          img.src = member.image;
          img.alt = member.name || 'Team member';
          img.loading = 'lazy';
          media.appendChild(img);
        }

        var name = document.createElement('h3');
        name.className = 'oa-team-grid__name';
        name.textContent = member.name || '';

        var role = document.createElement('p');
        role.className = 'oa-team-grid__role';
        role.textContent = member.job_title || '';

        link.appendChild(media);
        link.appendChild(name);
        link.appendChild(role);
        card.appendChild(link);
        grid.appendChild(card);
      });

      updateGridMinHeight();
    }

    function getGridColumnCount() {
      var style = window.getComputedStyle(grid);
      var columns = style.gridTemplateColumns || '';
      if (!columns) {
        return 1;
      }
      return Math.max(1, columns.split(' ').filter(Boolean).length);
    }

    function updateGridMinHeight() {
      var firstCard = grid.querySelector('.oa-team-grid__card');
      if (!firstCard) {
        grid.style.minHeight = '0px';
        return;
      }

      var columns = getGridColumnCount();
      var rows = Math.ceil(Math.min(cardsPerPage, state.filtered.length) / columns);
      var cardHeight = firstCard.getBoundingClientRect().height;
      var style = window.getComputedStyle(grid);
      var rowGap = parseFloat(style.rowGap || style.gap || '0');
      var minHeight = (cardHeight * rows) + (Math.max(0, rows - 1) * rowGap);

      grid.style.minHeight = minHeight + 'px';
    }

    function renderPagination() {
      var totalPages = getTotalPages();
      pagesWrap.innerHTML = '';

      if (state.filtered.length === 0 || totalPages <= 1) {
        pagination.style.display = 'none';
        return;
      }

      pagination.style.display = 'flex';
      prevBtn.disabled = state.page <= 1;
      nextBtn.disabled = state.page >= totalPages;

      for (var page = 1; page <= totalPages; page += 1) {
        var pageBtn = document.createElement('button');
        pageBtn.type = 'button';
        pageBtn.className = 'oa-team-grid__page-number' + (page === state.page ? ' is-active' : '');
        pageBtn.textContent = formatPageNumber(page);
        pageBtn.setAttribute('data-page', String(page));
        pagesWrap.appendChild(pageBtn);
      }
    }

    function render() {
      var totalPages = getTotalPages();
      if (state.page > totalPages) {
        state.page = totalPages;
      }
      renderCards();
      renderPagination();
    }

    function renderWithTransition() {
      grid.classList.add('is-fading');
      window.setTimeout(function () {
        render();
        requestAnimationFrame(function () {
          grid.classList.remove('is-fading');
        });
      }, 120);
    }

    searchBtn.addEventListener('click', function () {
      state.query = queryInput.value || '';
      state.location = locationSelect.value || '';
      state.sector = sectorSelect.value || '';
      applyFilters();
      renderWithTransition();
    });

    queryInput.addEventListener('keydown', function (event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        searchBtn.click();
      }
    });

    prevBtn.addEventListener('click', function () {
      if (state.page > 1) {
        state.page -= 1;
        renderWithTransition();
      }
    });

    nextBtn.addEventListener('click', function () {
      if (state.page < getTotalPages()) {
        state.page += 1;
        renderWithTransition();
      }
    });

    pagesWrap.addEventListener('click', function (event) {
      var target = event.target;
      if (!target || !target.matches('.oa-team-grid__page-number')) {
        return;
      }
      var nextPage = parseInt(target.getAttribute('data-page'), 10);
      if (!isNaN(nextPage)) {
        state.page = nextPage;
        renderWithTransition();
      }
    });

    window.addEventListener('resize', function () {
      updateGridMinHeight();
    });

    render();
  }

  document.addEventListener('DOMContentLoaded', function () {
    var containers = document.querySelectorAll('[data-oa-team-grid], .oa-team-grid');
    containers.forEach(buildTeamGrid);
  });
})();
