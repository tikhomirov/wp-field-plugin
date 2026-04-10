import React, { useState, useEffect, useMemo } from 'react';
import { createRoot, createPortal } from 'react-dom/client';
import SidebarNav from './components/SidebarNav.jsx';
import TabBar from './components/TabBar.jsx';

/**
 * Collects all leaf items from a nav tree (depth ≤ 2).
 */
function collectLeaves(nav) {
  const leaves = [];
  for (const item of nav) {
    if (item.children?.length) {
      for (const child of item.children) {
        leaves.push(child);
      }
    } else {
      leaves.push(item);
    }
  }
  return leaves;
}

function findLeaf(nav, id) {
  return collectLeaves(nav).find((l) => l.id === id) ?? null;
}

/**
 * WP Field Admin Shell React app.
 *
 * Manages:
 *  - Active segment (sidebar leaf) + active panel (tab within leaf).
 *  - Visibility of PHP-rendered .wp-field-shell-section-panel elements.
 *  - Hidden inputs #wp-field-shell-active-segment and #wp-field-shell-active-panel.
 *  - Breadcrumb text #wp-field-shell-header-section.
 *  - URL params (replaceState, key names from data attributes).
 */
function AdminShellApp({
  nav,
  initialSegment,
  initialPanel,
  title,
  sectionKey,
  tabKey,
}) {
  const leaves = useMemo(() => collectLeaves(nav), [nav]);

  const [activeSegment, setActiveSegment] = useState(() =>
    findLeaf(nav, initialSegment) ? initialSegment : (leaves[0]?.id ?? '')
  );
  const [activePanel, setActivePanel] = useState(() => {
    const leaf = findLeaf(nav, initialSegment);
    const panels = leaf?.panels ?? [];
    if (initialPanel && panels.some((p) => p.id === initialPanel)) {
      return initialPanel;
    }
    return panels[0]?.id ?? '';
  });

  const activeLeaf = useMemo(
    () => findLeaf(nav, activeSegment),
    [nav, activeSegment]
  );
  const panels = useMemo(() => activeLeaf?.panels ?? [], [activeLeaf]);

  // When segment changes, reset panel to first panel of new leaf
  const handleSegmentChange = (segId) => {
    setActiveSegment(segId);
    const leaf = findLeaf(nav, segId);
    setActivePanel(leaf?.panels?.[0]?.id ?? '');
  };

  const handlePanelChange = (panelId) => {
    setActivePanel(panelId);
  };

  useEffect(() => {
    // Sync hidden inputs
    const segInput = document.getElementById('wp-field-shell-active-segment');
    if (segInput) segInput.value = activeSegment;

    const panInput = document.getElementById('wp-field-shell-active-panel');
    if (panInput) panInput.value = activePanel;

    // Update breadcrumb
    const breadcrumb = document.getElementById('wp-field-shell-header-section');
    if (breadcrumb && activeLeaf) {
      breadcrumb.textContent = activeLeaf.label;
    }

    // Toggle panel visibility
    document.querySelectorAll('.wp-field-shell-section-panel').forEach((el) => {
      const seg = el.getAttribute('data-segment');
      const pan = el.getAttribute('data-panel');

      let visible = seg === activeSegment;
      if (visible && panels.length > 1) {
        // Multi-panel leaf: also match the panel
        const effectivePanel = activePanel || (panels[0]?.id ?? '');
        visible = pan === effectivePanel;
      }
      el.style.display = visible ? '' : 'none';
    });

    // Sync URL
    try {
      const url = new URL(window.location.href);
      const firstLeafId = leaves[0]?.id ?? '';
      if (activeSegment === '' || activeSegment === firstLeafId) {
        url.searchParams.delete(sectionKey);
      } else {
        url.searchParams.set(sectionKey, activeSegment);
      }
      if (activePanel === '' || activePanel === panels[0]?.id) {
        url.searchParams.delete(tabKey);
      } else {
        url.searchParams.set(tabKey, activePanel);
      }
      window.history.replaceState({}, '', url.toString());
    } catch (_) {
      // Ignore URL errors in non-browser environments
    }
  }, [
    activeLeaf,
    activePanel,
    activeSegment,
    leaves,
    panels,
    sectionKey,
    tabKey,
  ]);

  // TabBar renders into the PHP-created placeholder div
  const tabBarContainer = document.getElementById('wp-field-shell-tabs-bar');

  return (
    <>
      <SidebarNav
        nav={nav}
        activeSegment={activeSegment}
        onSegmentChange={handleSegmentChange}
        title={title}
      />
      {tabBarContainer &&
        panels.length > 1 &&
        createPortal(
          <TabBar
            panels={panels}
            activePanel={activePanel || panels[0]?.id}
            onPanelChange={handlePanelChange}
          />,
          tabBarContainer
        )}
    </>
  );
}

function mountAdminShell() {
  const root = document.getElementById('wp-field-shell-root');
  if (!root) return;

  let nav = [];
  try {
    nav = JSON.parse(root.getAttribute('data-nav') || '[]');
  } catch (e) {
    console.error('[wp-field-shell] Failed to parse nav data:', e);
  }

  const initialSegment = root.getAttribute('data-active') || '';
  const initialPanel = root.getAttribute('data-active-panel') || '';
  const title = root.getAttribute('data-title') || '';
  const sectionKey = root.getAttribute('data-section-key') || 'wpfs_section';
  const tabKey = root.getAttribute('data-tab-key') || 'wpfs_tab';

  createRoot(root).render(
    <AdminShellApp
      nav={nav}
      initialSegment={initialSegment}
      initialPanel={initialPanel}
      title={title}
      sectionKey={sectionKey}
      tabKey={tabKey}
    />
  );
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', mountAdminShell);
} else {
  mountAdminShell();
}
