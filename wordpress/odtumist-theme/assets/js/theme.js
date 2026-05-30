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
    var filterRoot = document.querySelector('[data-events-filter]');
    if (!filterRoot) return;

    var cards = Array.prototype.slice.call(document.querySelectorAll('.event-card[data-event-category]'));
    var buttons = Array.prototype.slice.call(filterRoot.querySelectorAll('[data-event-filter]'));

    if (!cards.length || !buttons.length) return;

    buttons.forEach(function (button) {
      button.addEventListener('click', function () {
        var selected = button.getAttribute('data-event-filter') || 'all';

        buttons.forEach(function (b) {
          b.classList.remove('is-active');
        });
        button.classList.add('is-active');

        cards.forEach(function (card) {
          var category = card.getAttribute('data-event-category');
          var show = selected === 'all' || category === selected;
          card.style.display = show ? '' : 'none';
        });
      });
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
    initMobileMenu();
    initHeroSlider();
    initCarousels();
    initElementorHomeRowArrows();
    initEventFilter();
    initAboutTabs();
    initMembershipTabs();
    initAboutPagination();
    initSolidarityInitialAnchor();
    initChildSlugSectionFocus();
  });
})();
