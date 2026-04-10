import React from 'react';

/**
 * Horizontal tab bar for AdminShell.
 * Rendered only when the active leaf has panels.length > 1.
 *
 * Mounts into #wp-field-shell-tabs-bar via a React portal (handled by admin-shell.jsx).
 *
 * @param {Array<{id: string, label: string}>} panels        Tab list for current leaf.
 * @param {string}                             activePanel   Active panel id.
 * @param {Function}                           onPanelChange Called with new panel id on click.
 */
export default function TabBar({ panels, activePanel, onPanelChange }) {
  if (!panels || panels.length <= 1) {
    return null;
  }

  return (
    <div className="wp-field-shell__tabs" role="tablist">
      {panels.map((panel) => (
        <button
          key={panel.id}
          type="button"
          role="tab"
          className={[
            'wp-field-shell__tab',
            activePanel === panel.id ? 'wp-field-shell__tab--active' : '',
          ]
            .filter(Boolean)
            .join(' ')}
          aria-selected={activePanel === panel.id}
          onClick={() => onPanelChange(panel.id)}
        >
          {panel.label}
        </button>
      ))}
    </div>
  );
}
