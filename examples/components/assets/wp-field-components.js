/**
 * WP_Field Components — documentation page enhancement.
 * Sidebar search, active section tracking, smooth scroll.
 * No jQuery. No wp.* dependencies.
 */
(function () {
  'use strict';

  function init() {
    var root = document.getElementById('wfc-root');
    if (!root) return;

    var searchInput = document.getElementById('wfc-search');
    var sidebarLinks = document.querySelectorAll('.wfc-sidebar__link');
    var sections = document.querySelectorAll('.wfc-section');
    var cards = document.querySelectorAll('.wfc-card');

    // ── Sidebar active state on scroll ──
    if ('IntersectionObserver' in window) {
      var observer = new IntersectionObserver(function (entries) {
        for (var i = 0; i < entries.length; i++) {
          if (entries[i].isIntersecting) {
            var id = entries[i].target.id;
            sidebarLinks.forEach(function (link) {
              link.classList.toggle('is-active', link.dataset.section === id);
            });
          }
        }
      }, { rootMargin: '-80px 0px -60% 0px', threshold: 0 });

      sections.forEach(function (section) { observer.observe(section); });
    }

    // ── Smooth scroll on sidebar click ──
    sidebarLinks.forEach(function (link) {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        var href = link.getAttribute('href');
        var targetId = href ? href.replace('#', '') : '';
        var target = targetId ? document.getElementById(targetId) : null;
        if (target) {
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
          try { history.replaceState(null, '', '#' + targetId); } catch (_) {}
        }
      });
    });

    // ── Search / filter ──
    if (searchInput) {
      searchInput.addEventListener('input', function () {
        var query = searchInput.value.trim().toLowerCase();

        if (!query) {
          cards.forEach(function (c) { c.style.display = ''; });
          sections.forEach(function (s) { s.style.display = ''; });
          sidebarLinks.forEach(function (l) { l.style.display = ''; });
          return;
        }

        var visibleSections = {};
        cards.forEach(function (card) {
          var type = (card.dataset.type || '').toLowerCase();
          var h3 = card.querySelector('h3');
          var pEl = card.querySelector('.wfc-card__header p');
          var title = h3 ? h3.textContent.toLowerCase() : '';
          var desc = pEl ? pEl.textContent.toLowerCase() : '';
          var match = type.indexOf(query) !== -1 || title.indexOf(query) !== -1 || desc.indexOf(query) !== -1;
          card.style.display = match ? '' : 'none';
          if (match) {
            var section = card.closest('.wfc-section');
            if (section) visibleSections[section.id] = true;
          }
        });

        sections.forEach(function (section) {
          section.style.display = visibleSections[section.id] ? '' : 'none';
        });

        sidebarLinks.forEach(function (link) {
          link.style.display = visibleSections[link.dataset.section] ? '' : 'none';
        });
      });
    }

    // ── Restore scroll from URL hash ──
    if (window.location.hash) {
      var hashTarget = document.getElementById(window.location.hash.replace('#', ''));
      if (hashTarget) {
        requestAnimationFrame(function () {
          hashTarget.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
      }
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
