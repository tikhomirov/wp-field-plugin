/**
 * WP_Field Components — React enhancement for the documentation page.
 *
 * Enhances the PHP-rendered page with:
 *  - Sidebar search filtering
 *  - Active section tracking on scroll
 *  - Smooth scroll navigation
 *  - Card search/filter by type name
 *
 * No jQuery. No wp.* dependencies.
 */

function init() {
  const root = document.getElementById('wfc-root');
  if (!root) return;

  const tabButtons = root.querySelectorAll('[data-wfc-tab]');
  const panels = root.querySelectorAll('[data-wfc-panel]');
  const searchInput = document.getElementById('wfc-search');
  const sidebarLinks = document.querySelectorAll('.wfc-sidebar__link');
  const sections = document.querySelectorAll('.wfc-section');
  const cards = document.querySelectorAll('.wfc-card');

  tabButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const target = button.dataset.wfcTab;
      tabButtons.forEach((btn) => {
        const active = btn === button;
        btn.classList.toggle('is-active', active);
        btn.setAttribute('aria-selected', active ? 'true' : 'false');
      });
      panels.forEach((panel) => {
        const active = panel.dataset.wfcPanel === target;
        panel.classList.toggle('is-active', active);
        panel.hidden = !active;
      });
    });
  });

  // ── Sidebar active state on scroll ──
  const observerOptions = {
    rootMargin: '-80px 0px -60% 0px',
    threshold: 0,
  };

  const observer = new IntersectionObserver((entries) => {
    for (const entry of entries) {
      if (entry.isIntersecting) {
        const id = entry.target.id;
        sidebarLinks.forEach((link) => {
          link.classList.toggle('is-active', link.dataset.section === id);
        });
      }
    }
  }, observerOptions);

  sections.forEach((section) => observer.observe(section));

  // ── Smooth scroll on sidebar click ──
  sidebarLinks.forEach((link) => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const targetId = link.getAttribute('href')?.replace('#', '');
      const target = targetId ? document.getElementById(targetId) : null;
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        // Update URL hash without jump
        history.replaceState(null, '', `#${targetId}`);
      }
    });
  });

  // ── Search / filter ──
  if (searchInput) {
    searchInput.addEventListener('input', () => {
      const query = searchInput.value.trim().toLowerCase();

      if (!query) {
        // Show everything
        cards.forEach((card) => {
          card.style.display = '';
        });
        sections.forEach((section) => {
          section.style.display = '';
        });
        sidebarLinks.forEach((link) => {
          link.style.display = '';
        });
        return;
      }

      // Filter cards
      const visibleSections = new Set();
      cards.forEach((card) => {
        const type = (card.dataset.type || '').toLowerCase();
        const title = (
          card.querySelector('h3')?.textContent || ''
        ).toLowerCase();
        const desc = (
          card.querySelector('.wfc-card__header p')?.textContent || ''
        ).toLowerCase();
        const match =
          type.includes(query) || title.includes(query) || desc.includes(query);
        card.style.display = match ? '' : 'none';
        if (match) {
          const section = card.closest('.wfc-section');
          if (section) visibleSections.add(section.id);
        }
      });

      // Show/hide sections based on visible cards
      sections.forEach((section) => {
        section.style.display = visibleSections.has(section.id) ? '' : 'none';
      });

      // Dim sidebar links for hidden sections
      sidebarLinks.forEach((link) => {
        const sectionId = link.dataset.section;
        link.style.display = visibleSections.has(sectionId) ? '' : 'none';
      });
    });
  }

  // ── Restore scroll from URL hash ──
  if (window.location.hash) {
    const target = document.getElementById(
      window.location.hash.replace('#', '')
    );
    if (target) {
      requestAnimationFrame(() => {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    }
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}
