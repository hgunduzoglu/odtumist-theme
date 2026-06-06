(function () {
  function initStickyHeader() {
    var header = document.getElementById('site-header');
    if (!header) return;

    function applyState() {
      header.classList.toggle('is-scrolled', window.scrollY > 20);
    }

    applyState();
    window.addEventListener('scroll', applyState, { passive: true });
  }

  function initTopBannerHeaderOffset() {
    var body = document.body;
    var header = document.getElementById('site-header');
    var main = document.querySelector('.site-main');
    if (!body || !header || !main) return;

    var isSolidarityRoot = body.classList.contains('odt-page-dayanisma') || /\/dayanisma\/?$/.test(window.location.pathname);
    var hasHomeHero = body.classList.contains('home') && (
      document.getElementById('hero-slider') ||
      document.querySelector('.odt-el-home-hero, .odt-el-home-slider')
    );
    var isAboutRoot = body.classList.contains('odt-page-hakkimizda') ||
      /\/(?:hakkimizda|about)\/?$/.test(window.location.pathname) ||
      document.querySelector('.odt-el-about-hero.odt-el-banner-section');
    if (!isSolidarityRoot && !hasHomeHero && !isAboutRoot) return;

    function applyOffset() {
      var headerRect = header.getBoundingClientRect();
      var headerHeight = Math.ceil(header.offsetHeight || headerRect.height || 0);
      if (!headerHeight || headerHeight < 0) {
        headerHeight = 0;
      }

      if (headerHeight > 0) {
        main.style.setProperty('--odt-top-banner-header-offset', headerHeight + 'px');
        main.style.setProperty('padding-top', headerHeight + 'px', 'important');
      }
    }

    applyOffset();
    window.addEventListener('resize', applyOffset);
    window.addEventListener('orientationchange', applyOffset);

    if ('ResizeObserver' in window) {
      new ResizeObserver(applyOffset).observe(header);
    }

    window.setTimeout(applyOffset, 250);
    window.setTimeout(applyOffset, 1000);
  }

  function parseCssColor(value) {
    if (!value || typeof value !== 'string') return null;

    var color = value.trim();
    var hex = color.match(/^#([0-9a-f]{3}|[0-9a-f]{6})$/i);
    if (hex) {
      var raw = hex[1];
      if (raw.length === 3) {
        raw = raw.split('').map(function (part) { return part + part; }).join('');
      }

      return {
        r: parseInt(raw.slice(0, 2), 16),
        g: parseInt(raw.slice(2, 4), 16),
        b: parseInt(raw.slice(4, 6), 16),
        a: 1
      };
    }

    var rgb = color.match(/^rgba?\(([^)]+)\)$/i);
    if (!rgb) return null;

    var parts = rgb[1].split(',').map(function (part) { return part.trim(); });
    if (parts.length < 3) return null;

    return {
      r: Number(parts[0]),
      g: Number(parts[1]),
      b: Number(parts[2]),
      a: parts.length >= 4 ? Number(parts[3]) : 1
    };
  }

  function getRelativeLuminance(color) {
    function channel(value) {
      var normalized = Math.max(0, Math.min(255, Number(value) || 0)) / 255;
      return normalized <= 0.03928
        ? normalized / 12.92
        : Math.pow((normalized + 0.055) / 1.055, 2.4);
    }

    return (0.2126 * channel(color.r)) + (0.7152 * channel(color.g)) + (0.0722 * channel(color.b));
  }

  function getEffectiveBackgroundColor(element) {
    var current = element;
    while (current && current !== document.documentElement) {
      var style = window.getComputedStyle(current);
      var color = parseCssColor(style.backgroundColor);
      if (color && color.a > 0.05) {
        return color;
      }

      var solidarityBg = parseCssColor(style.getPropertyValue('--odt-solidarity-bg'));
      if (solidarityBg && solidarityBg.a > 0.05) {
        return solidarityBg;
      }

      current = current.parentElement;
    }

    return null;
  }

  function initSolidarityAutoContrast() {
    var body = document.body;
    if (!body || !body.classList.contains('odt-page-dayanisma')) return;

    var panels = Array.prototype.slice.call(document.querySelectorAll('.odt-el-solidarity-panel-layout'));
    Array.prototype.slice.call(document.querySelectorAll('.elementor-top-section')).forEach(function (section) {
      if (
        section.classList.contains('odt-el-solidarity-hero') ||
        section.classList.contains('odt-el-solidarity-final-cta') ||
        section.classList.contains('odt-el-banner-section') ||
        section.classList.contains('odt-no-auto-strip')
      ) {
        return;
      }

      if (!section.classList.contains('odt-el-solidarity-panel-layout') && section.querySelector('.odt-el-title, .odt-el-richtext')) {
        panels.push(section);
      }
    });

    panels = panels.filter(function (panel, index) {
      return panel && panels.indexOf(panel) === index;
    });
    if (!panels.length) return;

    function applyContrast(panel) {
      var bg = getEffectiveBackgroundColor(panel);
      if (!bg) return;

      var isLight = getRelativeLuminance(bg) > 0.48;
      panel.classList.toggle('odt-on-light', isLight);
      panel.classList.toggle('odt-on-dark', !isLight);
    }

    function applyAll() {
      panels.forEach(applyContrast);
    }

    applyAll();
    window.setTimeout(applyAll, 250);
    window.setTimeout(applyAll, 1000);

    if ('MutationObserver' in window) {
      var observer = new MutationObserver(applyAll);
      panels.forEach(function (panel) {
        observer.observe(panel, { attributes: true, attributeFilter: ['class', 'style'] });
      });
    }
  }

  function initMobileMenu() {
    var toggle = document.getElementById('mobile-toggle');
    var panel = document.getElementById('mobile-panel');

    if (!toggle || !panel) return;

    toggle.addEventListener('click', function () {
      var isOpen = toggle.getAttribute('aria-expanded') === 'true';
      toggle.setAttribute('aria-expanded', String(!isOpen));
      panel.hidden = isOpen;
    });
  }

  function initHeroSlider() {
    var root = document.getElementById('hero-slider');
    if (!root) return;

    var slides = Array.prototype.slice.call(root.querySelectorAll('.hero-slide'));
    var dots = Array.prototype.slice.call(root.querySelectorAll('[data-hero-dot]'));
    var prev = root.querySelector('[data-hero-prev]');
    var next = root.querySelector('[data-hero-next]');

    if (!slides.length) return;

    var current = 0;
    var timer = null;

    function activate(index) {
      current = (index + slides.length) % slides.length;
      slides.forEach(function (slide, i) {
        slide.classList.toggle('is-active', i === current);
      });
      dots.forEach(function (dot, i) {
        dot.classList.toggle('is-active', i === current);
      });
    }

    function startAuto() {
      if (timer) clearInterval(timer);
      timer = setInterval(function () {
        activate(current + 1);
      }, 7000);
    }

    if (prev) {
      prev.addEventListener('click', function () {
        activate(current - 1);
        startAuto();
      });
    }

    if (next) {
      next.addEventListener('click', function () {
        activate(current + 1);
        startAuto();
      });
    }

    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        activate(Number(dot.getAttribute('data-hero-dot')) || 0);
        startAuto();
      });
    });

    activate(0);
    startAuto();
  }

  function initCarousels() {
    var carousels = Array.prototype.slice.call(document.querySelectorAll('[data-carousel]'));
    carousels.forEach(function (carousel) {
      var key = carousel.getAttribute('data-carousel');
      var prev = document.querySelector('[data-carousel-prev="' + key + '"]');
      var next = document.querySelector('[data-carousel-next="' + key + '"]');

      if (prev) {
        prev.addEventListener('click', function () {
          carousel.scrollBy({ left: -carousel.clientWidth * 0.85, behavior: 'smooth' });
        });
      }

      if (next) {
        next.addEventListener('click', function () {
          carousel.scrollBy({ left: carousel.clientWidth * 0.85, behavior: 'smooth' });
        });
      }
    });
  }

  function initElementorHomeRowArrows() {
    var tracks = Array.prototype.slice.call(
      document.querySelectorAll(
        '.elementor .odt-el-home-events-row > .elementor-container, .elementor .odt-el-home-groups-row > .elementor-container'
      )
    );
    if (!tracks.length) return;

    tracks.forEach(function (track) {
      if (track.getAttribute('data-odt-arrows-ready') === '1') return;

      var section = track.closest('.elementor-section');
      if (!section) return;

      section.classList.add('odt-el-scrollable-row-host');

      var prev = document.createElement('button');
      prev.type = 'button';
      prev.className = 'odt-el-scroll-arrow odt-el-scroll-arrow-prev';
      prev.setAttribute('aria-label', 'Önceki kartlar');
      prev.innerHTML = '&#8249;';

      var next = document.createElement('button');
      next.type = 'button';
      next.className = 'odt-el-scroll-arrow odt-el-scroll-arrow-next';
      next.setAttribute('aria-label', 'Sonraki kartlar');
      next.innerHTML = '&#8250;';

      section.appendChild(prev);
      section.appendChild(next);

      function stepSize() {
        return Math.max(280, track.clientWidth * 0.86);
      }

      function syncArrows() {
        var maxScroll = Math.max(0, track.scrollWidth - track.clientWidth);
        var noOverflow = maxScroll <= 10;
        var isStart = track.scrollLeft <= 4;
        var isEnd = track.scrollLeft >= maxScroll - 4;

        section.classList.toggle('odt-el-scroll-arrows-hidden', noOverflow);
        prev.disabled = noOverflow || isStart;
        next.disabled = noOverflow || isEnd;
      }

      prev.addEventListener('click', function () {
        track.scrollBy({ left: -stepSize(), behavior: 'smooth' });
      });

      next.addEventListener('click', function () {
        track.scrollBy({ left: stepSize(), behavior: 'smooth' });
      });

      track.addEventListener('scroll', syncArrows, { passive: true });
      window.addEventListener('resize', syncArrows);

      window.requestAnimationFrame(syncArrows);
      window.setTimeout(syncArrows, 180);
      track.setAttribute('data-odt-arrows-ready', '1');
    });
  }

  function initEventFilter() {
    var filterRoots = Array.prototype.slice.call(document.querySelectorAll('[data-events-filter]'));
    if (!filterRoots.length) return;

    function normalizeSlug(value) {
      var slug = String(value || '').trim().toLowerCase();
      if (!slug) return '';

      if (typeof slug.normalize === 'function') {
        slug = slug.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
      }

      slug = slug
        .replace(/[\s_]+/g, '-')
        .replace(/[^a-z0-9-]+/g, '')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');

      return slug;
    }

    function parseCategories(node) {
      var raw = (node.getAttribute('data-event-category') || '').trim();
      if (!raw) return [];

      return raw.split(/[\s,|]+/).map(function (slug) {
        return normalizeSlug(slug);
      }).filter(Boolean);
    }

    filterRoots.forEach(function (filterRoot) {
      if (!filterRoot || filterRoot.getAttribute('data-events-filter-ready') === '1') return;

      var scope = filterRoot.closest('.events-page-grid, .events-section, .elementor-section');
      var searchRoot = scope || document;
      var cards = Array.prototype.slice.call(
        searchRoot.querySelectorAll('.event-card[data-event-category], .event-list-card[data-event-category]')
      );
      var buttons = Array.prototype.slice.call(filterRoot.querySelectorAll('[data-event-filter]'));

      if (!cards.length || !buttons.length) return;

      function setActiveButton(selected) {
        buttons.forEach(function (btn) {
          var buttonSlug = normalizeSlug(btn.getAttribute('data-event-filter') || 'all') || 'all';
          var isActive = buttonSlug === selected;
          btn.classList.toggle('is-active', isActive);
          btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
      }

      function applyFilter(selected) {
        var filterSlug = normalizeSlug(selected || 'all') || 'all';
        setActiveButton(filterSlug);

        cards.forEach(function (card) {
          var cats = parseCategories(card);
          var show = filterSlug === 'all' || cats.indexOf(filterSlug) !== -1;
          card.style.display = show ? '' : 'none';
        });
      }

      buttons.forEach(function (button) {
        button.addEventListener('click', function () {
          applyFilter(button.getAttribute('data-event-filter') || 'all');
        });
      });

      var activeBtn = filterRoot.querySelector('[data-event-filter].is-active');
      var initial = activeBtn ? activeBtn.getAttribute('data-event-filter') : (buttons[0].getAttribute('data-event-filter') || 'all');
      applyFilter(initial || 'all');
      filterRoot.setAttribute('data-events-filter-ready', '1');
    });
  }

  function initWorkingGroupFilters() {
    var filterRoots = Array.prototype.slice.call(document.querySelectorAll('[data-working-groups-filter]'));
    if (!filterRoots.length) return;

    function unique(values) {
      var seen = {};
      return values.filter(function (value) {
        if (!value || seen[value]) return false;
        seen[value] = true;
        return true;
      });
    }

    function parseCategorySlugs(node) {
      if (!node) return [];

      var slugs = [];
      var dataCats = (node.getAttribute('data-group-cats') || '').trim();
      if (dataCats) {
        dataCats.split(/[\s,|]+/).forEach(function (slug) {
          var clean = String(slug || '').trim();
          if (clean) slugs.push(clean);
        });
      }

      var classTokens = String(node.className || '').split(/\s+/);
      classTokens.forEach(function (token) {
        if (token.indexOf('odt-team-cat-') !== 0) return;
        var slug = token.replace(/^odt-team-cat-/, '').trim();
        if (slug) slugs.push(slug);
      });

      return unique(slugs);
    }

    function getFilterTargets(root) {
      var panel = root.closest('[data-about-panel="groups"]');
      var scope = panel || document;
      var cards = Array.prototype.slice.call(scope.querySelectorAll('.about-groups-grid .about-group-card'));
      if (cards.length) {
        return {
          mode: 'classic',
          items: cards
        };
      }

      var inElementor = !!root.closest('.elementor');
      if (inElementor) {
        var columns = Array.prototype.slice.call(
          document.querySelectorAll('.elementor .odt-el-about-groups-row .elementor-column')
        ).filter(function (column) {
          return !!column.querySelector('.odt-el-group-card');
        });
        return {
          mode: 'elementor',
          items: columns
        };
      }

      return {
        mode: 'classic',
        items: cards
      };
    }

    function ensureButtons(root, items) {
      var buttons = Array.prototype.slice.call(root.querySelectorAll('[data-group-filter]'));
      if (buttons.length) return buttons;

      var slugs = [];
      items.forEach(function (item) {
        slugs = slugs.concat(parseCategorySlugs(item));
      });
      slugs = unique(slugs);
      if (!slugs.length) return [];

      var fragment = document.createDocumentFragment();

      var allBtn = document.createElement('button');
      allBtn.type = 'button';
      allBtn.className = 'odt-group-filter is-active';
      allBtn.setAttribute('data-group-filter', 'all');
      allBtn.textContent = 'Tümü';
      fragment.appendChild(allBtn);

      slugs.forEach(function (slug) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'odt-group-filter';
        btn.setAttribute('data-group-filter', slug);
        btn.textContent = slug.replace(/-/g, ' ');
        fragment.appendChild(btn);
      });

      root.appendChild(fragment);
      return Array.prototype.slice.call(root.querySelectorAll('[data-group-filter]'));
    }

    filterRoots.forEach(function (root) {
      if (root.getAttribute('data-groups-filter-ready') === '1') return;

      var targets = getFilterTargets(root);
      if (!targets.items.length) return;

      var buttons = ensureButtons(root, targets.items);
      if (!buttons.length) return;

      function setActiveButton(activeSlug) {
        buttons.forEach(function (button) {
          var isActive = (button.getAttribute('data-group-filter') || 'all') === activeSlug;
          button.classList.toggle('is-active', isActive);
          button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
      }

      function applyFilter(selectedSlug) {
        var normalized = (selectedSlug || 'all').trim();
        setActiveButton(normalized);

        targets.items.forEach(function (item) {
          var slugs = parseCategorySlugs(item);
          var isVisible = normalized === 'all' || slugs.indexOf(normalized) !== -1;
          if (targets.mode === 'elementor') {
            item.style.display = isVisible ? '' : 'none';
          } else {
            item.style.display = isVisible ? '' : 'none';
          }
        });
      }

      buttons.forEach(function (button) {
        button.addEventListener('click', function () {
          var selected = button.getAttribute('data-group-filter') || 'all';
          applyFilter(selected);
        });
      });

      var activeBtn = root.querySelector('[data-group-filter].is-active');
      var initial = activeBtn ? activeBtn.getAttribute('data-group-filter') : (buttons[0].getAttribute('data-group-filter') || 'all');
      applyFilter(initial || 'all');
      root.setAttribute('data-groups-filter-ready', '1');
    });
  }

  function initAboutTabs() {
    var navRoot = document.querySelector('[data-about-nav]');

    // Classic about template mode (template-parts/page/about-layout.php)
    if (navRoot) {
      var tabs = Array.prototype.slice.call(navRoot.querySelectorAll('[data-about-tab]'));
      var panels = Array.prototype.slice.call(document.querySelectorAll('[data-about-panel]'));
      if (!tabs.length || !panels.length) return;
      var initialTab = (navRoot.getAttribute('data-about-initial') || '').trim();

      function setActiveClassic(tabId, shouldUpdateHash) {
        tabs.forEach(function (tab) {
          var isActive = tab.getAttribute('data-about-tab') === tabId;
          tab.classList.toggle('is-active', isActive);
          tab.setAttribute('aria-selected', String(isActive));
        });

        var activePanel = null;
        panels.forEach(function (panel) {
          var isActive = panel.getAttribute('data-about-panel') === tabId;
          panel.classList.toggle('is-active', isActive);
          if (isActive) activePanel = panel;
        });

        if (shouldUpdateHash && activePanel) {
          var anchors = (activePanel.getAttribute('data-about-anchors') || '').split('|').filter(Boolean);
          if (anchors.length) {
            if (history.replaceState) {
              history.replaceState(null, '', '#' + anchors[0]);
            } else {
              window.location.hash = anchors[0];
            }
          }
        }
      }

      function resolveClassicTabByAnchor(anchor) {
        if (!anchor) return null;
        for (var i = 0; i < panels.length; i++) {
          var panel = panels[i];
          var anchors = (panel.getAttribute('data-about-anchors') || '').split('|').filter(Boolean);
          if (anchors.indexOf(anchor) !== -1) {
            return panel.getAttribute('data-about-panel');
          }
        }
        return null;
      }

      function activateClassicFromHash() {
        var anchor = (window.location.hash || '').replace(/^#/, '').trim();
        if (!anchor) return;

        var tabId = resolveClassicTabByAnchor(anchor);
        if (!tabId) return;

        setActiveClassic(tabId, false);
      }

      tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
          var tabId = tab.getAttribute('data-about-tab');
          if (!tabId) return;
          setActiveClassic(tabId, true);
        });
      });

      if (initialTab) {
        setActiveClassic(initialTab, false);
      }
      activateClassicFromHash();
      window.addEventListener('hashchange', activateClassicFromHash);
      return;
    }

    // Elementor full mode: keep sections as tabs instead of long stacked flow.
    var elementorNav = document.querySelector('.elementor .odt-el-about-nav');
    if (!elementorNav) return;

    var anchorToTab = {
      'neler-yapiyoruz': 'doing',
      'calisma-gruplarimiz': 'groups',
      'calisma-gruplari': 'groups',
      'tarihce': 'history',
      'yonetim': 'management'
    };
    var tabToAnchor = {
      doing: 'neler-yapiyoruz',
      groups: 'calisma-gruplarimiz',
      history: 'tarihce',
      management: 'yonetim'
    };

    function collectSections(selectors) {
      var unique = [];
      (selectors || []).forEach(function (selector) {
        var found = Array.prototype.slice.call(document.querySelectorAll(selector));
        found.forEach(function (el) {
          if (unique.indexOf(el) === -1) unique.push(el);
        });
      });
      return unique;
    }

    var sectionsByTab = {
      doing: collectSections([
        '.elementor .odt-el-about-panel-neler-yapiyoruz',
        '.elementor .odt-el-about-pagination-doing'
      ]),
      groups: collectSections([
        '.elementor .odt-el-about-panel-calisma-gruplarimiz',
        '.elementor .odt-el-about-groups-filter',
        '.elementor .odt-el-about-groups-intro',
        '.elementor .odt-el-about-groups-row',
        '.elementor .odt-el-about-pagination-groups'
      ]),
      history: collectSections([
        '.elementor .odt-el-about-panel-tarihce',
        '.elementor .odt-el-about-history-intro',
        '.elementor .odt-el-about-history-row',
        '.elementor .odt-el-about-pagination-history'
      ]),
      management: collectSections([
        '.elementor .odt-el-about-panel-yonetim',
        '.elementor .odt-el-about-management-row',
        '.elementor .odt-el-about-pagination-management'
      ])
    };

    var allSections = [];
    Object.keys(sectionsByTab).forEach(function (tabId) {
      sectionsByTab[tabId].forEach(function (el) {
        if (allSections.indexOf(el) === -1) allSections.push(el);
      });
    });
    if (!allSections.length) return;

    var navLinks = Array.prototype.slice.call(elementorNav.querySelectorAll('a[href*="#"]')).filter(function (link) {
      var href = link.getAttribute('href') || '';
      var hash = href.indexOf('#') > -1 ? href.split('#').pop() : '';
      return !!anchorToTab[hash];
    });
    if (!navLinks.length) return;

    var paginationLinks = Array.prototype.slice.call(
      document.querySelectorAll('.elementor .odt-el-about-pagination a[href*="#"]')
    ).filter(function (link) {
      var href = link.getAttribute('href') || '';
      var hash = href.indexOf('#') > -1 ? href.split('#').pop() : '';
      return !!anchorToTab[hash];
    });

    function getInitialTab() {
      var hash = (window.location.hash || '').replace(/^#/, '').trim();
      if (hash && anchorToTab[hash]) return anchorToTab[hash];

      var slug = window.location.pathname.replace(/\/+$/, '').split('/').pop();
      if (slug && anchorToTab[slug]) return anchorToTab[slug];

      return 'doing';
    }

    function setActiveElementorTab(tabId, shouldUpdateHash) {
      if (!sectionsByTab[tabId] || !sectionsByTab[tabId].length) return;

      navLinks.forEach(function (link) {
        var href = link.getAttribute('href') || '';
        var hash = href.indexOf('#') > -1 ? href.split('#').pop() : '';
        var isActive = anchorToTab[hash] === tabId;
        link.classList.toggle('is-active', isActive);
        link.setAttribute('aria-current', isActive ? 'page' : 'false');

        var col = link.closest('.elementor-column');
        if (col) col.classList.toggle('is-active', isActive);
      });

      allSections.forEach(function (section) {
        section.classList.remove('is-active');
        section.style.display = 'none';
      });

      sectionsByTab[tabId].forEach(function (section) {
        section.classList.add('is-active');
        section.style.display = '';
      });

      if (shouldUpdateHash && tabToAnchor[tabId]) {
        if (history.replaceState) {
          history.replaceState(null, '', '#' + tabToAnchor[tabId]);
        } else {
          window.location.hash = tabToAnchor[tabId];
        }
      }
    }

    function bindAnchorAsTab(link) {
      link.addEventListener('click', function (e) {
        var href = link.getAttribute('href') || '';
        var hash = href.indexOf('#') > -1 ? href.split('#').pop() : '';
        var tabId = anchorToTab[hash];
        if (!tabId) return;
        e.preventDefault();
        setActiveElementorTab(tabId, true);
        elementorNav.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    }

    navLinks.forEach(bindAnchorAsTab);
    paginationLinks.forEach(bindAnchorAsTab);

    function activateElementorFromHash() {
      var hash = (window.location.hash || '').replace(/^#/, '').trim();
      var tabId = anchorToTab[hash];
      if (!tabId) return;
      setActiveElementorTab(tabId, false);
    }

    setActiveElementorTab(getInitialTab(), false);
    window.addEventListener('hashchange', activateElementorFromHash);
  }

  function initMembershipTabs() {
    var navRoot = document.querySelector('[data-membership-nav]');
    if (!navRoot) return;

    var tabs = Array.prototype.slice.call(navRoot.querySelectorAll('[data-membership-tab]'));
    var panels = Array.prototype.slice.call(document.querySelectorAll('[data-membership-panel]'));
    if (!tabs.length || !panels.length) return;
    var initialTab = (navRoot.getAttribute('data-membership-initial') || '').trim();

    function setActive(tabId, shouldUpdateHash) {
      tabs.forEach(function (tab) {
        var isActive = tab.getAttribute('data-membership-tab') === tabId;
        tab.classList.toggle('is-active', isActive);
        tab.setAttribute('aria-selected', String(isActive));
      });

      var activePanel = null;
      panels.forEach(function (panel) {
        var isActive = panel.getAttribute('data-membership-panel') === tabId;
        panel.classList.toggle('is-active', isActive);
        if (isActive) activePanel = panel;
      });

      if (shouldUpdateHash && activePanel) {
        var anchors = (activePanel.getAttribute('data-membership-anchors') || '').split('|').filter(Boolean);
        if (anchors.length) {
          if (history.replaceState) {
            history.replaceState(null, '', '#' + anchors[0]);
          } else {
            window.location.hash = anchors[0];
          }
        }
      }
    }

    function resolveTabByAnchor(anchor) {
      if (!anchor) return null;
      for (var i = 0; i < panels.length; i++) {
        var panel = panels[i];
        var anchors = (panel.getAttribute('data-membership-anchors') || '').split('|').filter(Boolean);
        if (anchors.indexOf(anchor) !== -1) {
          return panel.getAttribute('data-membership-panel');
        }
      }
      return null;
    }

    function activateFromHash() {
      var anchor = (window.location.hash || '').replace(/^#/, '').trim();
      if (!anchor) return;
      var tabId = resolveTabByAnchor(anchor);
      if (!tabId) return;
      setActive(tabId, false);
    }

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        var tabId = tab.getAttribute('data-membership-tab');
        if (!tabId) return;
        setActive(tabId, true);
      });
    });

    if (initialTab) {
      setActive(initialTab, false);
    }
    activateFromHash();
    window.addEventListener('hashchange', activateFromHash);
  }

  function initSolidarityInitialAnchor() {
    var sectionRoot = document.querySelector('[data-solidarity-initial]');
    if (!sectionRoot) return;

    // URL hash varsa kullanici secimini bozma.
    if ((window.location.hash || '').trim() !== '') return;

    var anchor = (sectionRoot.getAttribute('data-solidarity-initial') || '').trim();
    if (!anchor) return;

    var target = document.getElementById(anchor);
    if (!target) return;

    // Header sabit oldugu icin hedefe kucuk offset ile kaydir.
    var headerOffset = 110;
    var targetTop = target.getBoundingClientRect().top + window.pageYOffset - headerOffset;
    window.scrollTo({ top: targetTop, behavior: 'smooth' });
  }

  function odtumistSlugify(value) {
    var map = {
      'ı': 'i',
      'ğ': 'g',
      'ü': 'u',
      'ş': 's',
      'ö': 'o',
      'ç': 'c',
      'İ': 'i',
      'I': 'i',
      'Ğ': 'g',
      'Ü': 'u',
      'Ş': 's',
      'Ö': 'o',
      'Ç': 'c'
    };

    var normalized = String(value || '')
      .replace(/[ıİIğĞüÜşŞöÖçÇ]/g, function (letter) { return map[letter] || letter; })
      .toLowerCase();

    if (typeof normalized.normalize === 'function') {
      normalized = normalized.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    return normalized
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  function getMembershipPanelTitle(panel) {
    if (!panel) return '';
    var titleEl = panel.querySelector('.odt-el-title .elementor-heading-title, .odt-el-title h1, .odt-el-title h2, .odt-el-title h3, .odt-el-title');
    return titleEl ? titleEl.textContent.trim() : '';
  }

  function getMembershipDynamicTargets() {
    var panels = Array.prototype.slice.call(document.querySelectorAll('.odt-el-membership-panel'));
    var used = {};
    var targets = {};

    panels.forEach(function (panel) {
      if (
        panel.classList.contains('odt-el-membership-actions') ||
        panel.classList.contains('odt-el-membership-hero') ||
        panel.classList.contains('odt-el-membership-panel-aside')
      ) {
        return;
      }

      var slug = odtumistSlugify(getMembershipPanelTitle(panel));
      if (!slug) return;

      var baseSlug = slug;
      var suffix = 2;
      while (used[slug]) {
        slug = baseSlug + '-' + suffix;
        suffix += 1;
      }
      used[slug] = true;
      panel.setAttribute('data-odt-membership-anchor', slug);
      targets[slug] = panel;

      if (!panel.id && !document.getElementById(slug)) {
        panel.id = slug;
      }
    });

    return targets;
  }

  function scrollToMembershipDynamicTarget(anchor, behavior) {
    var targets = getMembershipDynamicTargets();
    var target = targets[anchor] || document.getElementById(anchor);
    if (!target) return false;

    var headerOffset = 110;
    var targetTop = target.getBoundingClientRect().top + window.pageYOffset - headerOffset;
    window.scrollTo({ top: targetTop, behavior: behavior || 'smooth' });
    return true;
  }

  function initMembershipDynamicHashTargets() {
    if (!document.querySelector('.odt-el-membership-panel')) return;

    getMembershipDynamicTargets();

    function focusFromHash(behavior) {
      var anchor = (window.location.hash || '').replace(/^#/, '').trim();
      if (!anchor) return;
      scrollToMembershipDynamicTarget(anchor, behavior);
    }

    focusFromHash('auto');
    window.addEventListener('hashchange', function () {
      focusFromHash('smooth');
    });
  }

  function initChildSlugSectionFocus() {
    // Hash varsa kullanıcının explicit hedefini bozmayalım.
    if ((window.location.hash || '').trim() !== '') return;

    var slug = window.location.pathname.replace(/\/+$/, '').split('/').pop();
    if (!slug) return;

    // Child URL'lerde parent sayfanın ilgili bölümüne otomatik odaklan.
    var slugToAnchor = {
      'neden-uye-olmaliyim': 'neden-uye-olmaliyim',
      'bilgi-guncelleme': 'bilgi-guncelleme',
      'aidat-odeme': 'aidat-odeme',
      'uyelik-avantajlari': 'uyelik-avantajlari',
      'nasil-uye-olabilirsiniz': 'nasil-uye-olabilirsiniz',
      'yeni-mezunlar-icin-uyelik': 'yeni-mezunlar-icin-uyelik',
      'uyelik-sss': 'uyelik-sss',
      'sen-de-katil': 'sen-de-katil',
      'burs': 'burs',
      'maraton': 'maraton',
      'mentorluk': 'mentorluk',
      'gonulluluk': 'gonulluluk',
      'gonulluler': 'gonulluluk',
      'genclik-iletisim': 'genclik-iletisim',
      'bagiscilar-paydaslar': 'bagiscilar-paydaslar',
      'bagiscilar': 'bagiscilar-paydaslar',
      'paydaslar': 'bagiscilar-paydaslar',
      'bursiyerler': 'bursiyerler',
      'networking': 'networking'
    };

    if (!slugToAnchor[slug]) return;

    var anchor = slugToAnchor[slug];
    var target = document.getElementById(anchor);
    if (!target) return;

    var headerOffset = 110;
    var targetTop = target.getBoundingClientRect().top + window.pageYOffset - headerOffset;
    window.scrollTo({ top: targetTop, behavior: 'smooth' });
  }

  function initAboutPagination() {
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('.about-pag-btn[data-about-tab]');
      if (!btn) return;

      var tabId = btn.getAttribute('data-about-tab');
      if (!tabId) return;

      var navBtn = document.querySelector('[data-about-nav] [data-about-tab="' + tabId + '"]');
      if (navBtn) {
        navBtn.click();
        var navEl = document.querySelector('[data-about-nav]');
        if (navEl) {
          navEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initStickyHeader();
    initTopBannerHeaderOffset();
    initSolidarityAutoContrast();
    initMobileMenu();
    initHeroSlider();
    initCarousels();
    initElementorHomeRowArrows();
    initEventFilter();
    initWorkingGroupFilters();
    initAboutTabs();
    initMembershipTabs();
    initAboutPagination();
    initSolidarityInitialAnchor();
    initMembershipDynamicHashTargets();
    initChildSlugSectionFocus();
  });
})();
